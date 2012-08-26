<?php

/**
 * Class declaration can contain function calls
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class ClassEnhancer implements \Enhancer\IEnhancer
{
	private $level;
	private $className, $classExtends, $classImplements;


	public function enhance($code)
	{
		$parser = new Enhancer\Utils\PhpParser($code);
		$builder = new \Enhancer\Builder\Php\PhpBuilder($parser);
		$namespace = '';
		$uses = array('' => '');

		while (($token = $builder->fetch()) !== FALSE) {
			if ($parser->isCurrent(T_NAMESPACE)) {
				$builder->append($token);
				$builder->pass(T_WHITESPACE);
				$namespace = (string) $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$builder->append($namespace);
				continue;

			} elseif ($parser->isCurrent(T_USE)) {
				if ($parser->isNext('(')) { // closure?
					$builder->append($token);
					continue;
				}
				$builder->append($token);
				$builder->pass(T_WHITESPACE);
				do {
					$class = $builder->passAll(T_STRING, T_NS_SEPARATOR);
					$as = $builder->pass(T_AS)
							? $builder->pass(T_STRING)
							: substr($class, strrpos("\\$class", '\\'));
					$uses[strtolower($as)] = $class;
				} while ($builder->pass(','));
				$builder->passAll(';', T_WHITESPACE);
				continue;

			} elseif ($parser->isCurrent(T_CLASS)) {
				$this->className = $this->classExtends = $this->classImplements = NULL; // clear

				$builder->append($token);
				$builder->pass(T_WHITESPACE);

				$classMark = $builder->mark('class-name');
				$this->className = $builder->pass(T_STRING);
				$builder->done($classMark);

				$this->level = -1;
				continue;

			} elseif ($parser->isCurrent(T_EXTENDS)) {
				$builder->append($token);
				$builder->pass(T_WHITESPACE);
				$this->classExtends = $builder->passAll(T_STRING, T_NS_SEPARATOR);
				continue;

			} elseif ($parser->isCurrent(T_IMPLEMENTS)) {
				$builder->append($token);
				$builder->pass(T_WHITESPACE);
				$this->classImplements = array();
				do { // while ($parser->isNext(T_STRING) || $parser->isNext(T_NS_SEPARATOR)) {
					$this->classImplements[] = $builder->passAll(T_STRING, T_NS_SEPARATOR);
				} while(trim($builder->passAll(',', T_WHITESPACE)));
				continue;

			} elseif ($parser->isCurrent(T_STRING) && !$parser->isPrev(T_FUNCTION) && $parser->isNext('(') && $this->level === 0) { // function call in class declaration

				$functionName = $this->getHelperFunctionName($token);
				$parser->fetch('(');
				$args = $parser->fetchUntil(')');
				$parser->fetch(); $parser->fetch(); // omit ')' + ';'

				if (is_callable($functionName)) {
					$snippet = eval("return $functionName (\$builder, $args);");
					if (is_array($snippet)) $snippet = implode($snippet);
					$snippet = str_replace("\n", ' ', $snippet); // remove newlines so that it doesn't break line numbers
					$builder->append($snippet);
				} elseif ($functionName) {
					$builder->append("/* Invalid method $token, got $functionName */");
				} else {
					$builder->append("/* Unknown method $token */");
				}
				continue;

			} elseif ($parser->isCurrent('{')) {
				$this->level++;

			} elseif ($parser->isCurrent('}')) {
				$this->level--;

			}

			$builder->append($token);
			$builder->pass(T_WHITESPACE);
		}

		return $builder->getOutputCode();
	}


	private function getHelperFunctionName($functionName)
	{
		if (class_exists($this->classExtends) && method_exists($this->classExtends, $functionName)) {
			return "$this->classExtends::$functionName";
		}

	}

}
