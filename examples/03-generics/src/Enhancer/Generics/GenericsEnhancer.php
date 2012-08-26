<?php

namespace Enhancer\Generics;

use Enhancer\Utils\PhpParser;

/**
 * 'new Foo' are converted to a hook
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class GenericsEnhancer implements \Enhancer\IEnhancer
{

	/**
	 * @var PhpParser
	 */
	private $parser;

	/**
	 * @var string
	 */
	private $namespace = '';

	/**
	 * @var string
	 */
	private $class = '';

	/**
	 * @var array
	 */
	private $functionReturnType = array();

	/**
	 * @var array
	 */
	private $functionParameterHint = array();

	/** @var string[]  names of type arguments of class being processed now */
	private $currentTypeArgs;

	/** @var array shortName => fullName */
	private $uses;



	/**
	 * @param string $code
	 * @return string
	 */
	public function enhance($code)
	{
		// clean
		$this->currentTypeArgs = NULL;
		$this->uses = array('' => '');
		$this->namespace = '';


		$this->parser = new PhpParser($code);
		$s = '';

		while (($token = $this->parser->fetch()) !== FALSE) {
			if ($this->parser->isCurrent(T_NAMESPACE)) {
				$this->namespace = (string) $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$s .= $token . ' ' . $this->namespace;
				if ($this->parser->fetch(';', '{') === '{') {
					$s .= '{';
				} else {
					$s .= ';';
				}

			} elseif ($this->parser->isCurrent(T_USE)) {
				if ($this->parser->isNext('(')) { // closure?
					$s .= $token;
					continue;
				}
				$s .= $token . $this->parser->fetchAll(T_WHITESPACE);
				do {
					$s .= $class = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
					$as = $this->parser->fetch(T_AS)
						? $this->parser->fetch(T_STRING)
						: substr($class, strrpos("\\$class", '\\'));
					$this->uses[strtolower($as)] = $class;
				} while ($this->parser->fetch(','));
				$s .= $this->parser->fetch(';');

			} elseif ($this->parser->isCurrent(T_NEW)) {
				$prefix = $token . $this->parser->fetchAll(T_WHITESPACE);
				$className = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				if ($className === 'parent' || $className === 'self' || $className === 'static') {
					$s .= $prefix . $className;

				} elseif ($className) {
					if ($this->currentTypeArgs && in_array($className, $this->currentTypeArgs)) {
						$classNameCode = "\\Enhancer\\Generics\\Registry::resolveTypeArgument(\$this, '$className')";
					} else {
						$classNameCode = "'" . $this->fullClass($className) . "'";
					}

					$s .= "\\Enhancer\\Generics\\Registry::newInstance(" . $classNameCode;

					if ($generics = $this->fetchGenericParameter()) {
						$s .= ', ' . $this->createCodeGenericParameter($generics);
					} else {
						$s .= ', NULL'; // no generics
					}

					if ($this->parser->isNext(';')) { // without parentheses
						$s .= ")" . $this->parser->fetch();

					} elseif ($this->parser->isNext('(')) {
						$this->parser->fetch();
						if ( ! $this->parser->isNext(')')) $s .= ', '; // more arguments follow
					}

				} else {
					$s .= $prefix;
				}

			} elseif ($this->parser->isCurrent(T_CLASS)) { // todo: abstract, final & interface
				$registration = NULL;
				// $classDef: 'class ', name, 'extends ', extends, 'implements ', implements, '{', generated-methods
				$classDef = array(
					$token . $this->parser->fetchAll(T_WHITESPACE), NULL, NULL, NULL, NULL, NULL, NULL
				);
				$classDef[1] = $className = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$generics = $this->currentTypeArgs = $this->fetchGenericParameter();

				if ( ! $generics) {
					$s .= implode($classDef);
					continue;
				}

				if ($this->parser->isNext(T_EXTENDS)) {
					$classDef[2] = $this->parser->fetchAll(T_WHITESPACE, T_EXTENDS);
					$classDef[3] = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				}
				if ($this->parser->isNext(T_IMPLEMENTS)) {
					$classDef[4] = $this->parser->fetchAll(T_WHITESPACE, T_IMPLEMENTS);
					$classDef[5] = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				}
				if ($generics) {
					if (!$classDef[4]) $classDef[4] = ' implements ';
					$classDef[5] .= ($classDef[5] ? ', ' : '') . '\Enhancer\Generics\GenericType';
				}

				$classDef[6] = $this->parser->fetch(T_WHITESPACE) . $this->parser->fetchAll('{'); // start class

				$classDef[7] = $generics ? 'public function getParametrizedType($parameterName) { return \\Enhancer\\Generics\\Registry::getParametrizedTypesForObject($this, $parameterName); }' : '';

				$s .= '\\Enhancer\\Generics\\Registry::registerClass(\'' .
					$this->fullClass($className) . '\', array(\'' .
					implode('\', \'', $generics) .
					'\'));';
				$s .= implode($classDef);

			} elseif ($this->parser->isCurrent(T_FUNCTION)) {
				$s .= $token . $this->parser->fetchAll(T_WHITESPACE);

				$name = $this->parser->fetchUntil(T_WHITESPACE, '('); // All(T_STRING, T_NS_SEPARATOR);
				if ($this->parser->isNext('(')) { // it was function name
					$s .= $name;

				} else { // it was return type function name follows
					// TODO: store return type somewhere

					$name = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
					$s .= $name;

					assert($this->parser->isNext('('));
				}

				$methodStartCode = NULL;

				$s .= $this->parser->fetchAll('(', T_WHITESPACE);
				while ( ! $this->parser->isNext(')')) {
					// hint, whitespace, variable, default value
					$hint = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
					$hintGenerics = $this->fetchGenericParameter();
					$ws1 = $this->parser->fetchAll(T_WHITESPACE);
					$variable = $this->parser->fetchAll(T_VARIABLE);
					$rest = $this->parser->fetchUntil(',', ')');

					$typeValuesCode = NULL; // actual type values for hint
					if ($hintGenerics) {
						foreach ($hintGenerics as $typeValue) $typeValuesCode[] = ($this->currentTypeArgs && in_array($typeValue, $this->currentTypeArgs)) ? "\\Enhancer\\Generics\\Registry::resolveTypeArgument(\$this, '$typeValue')" : "'{$this->fullClass($typeValue)}'";
						$typeValuesCode = implode(', ', $typeValuesCode);
					}

					$hintCode = NULL; // actual hint name
					if ($this->currentTypeArgs && in_array($hint, $this->currentTypeArgs)) {
						$hintCode = "\\Enhancer\\Generics\\Registry::resolveTypeArgument(\$this, '$hint')";
						$hint = $ws1 = NULL; // clear real typehint FIXME: if 'E extends Entity', leave 'Entity'
					}

					if ($typeValuesCode || $hintCode) {
						$hintCode = $hintCode ?: "'{$this->fullClass($hint)}'";
						$methodStartCode .= "\\Enhancer\\Generics\\Registry::ensureInstance($variable, $hintCode, array($typeValuesCode));";
					}


					$s .= $hint . $ws1 . $variable . $rest;

					if ($this->parser->isNext(',')) $s .= $this->parser->fetchAll(',', T_WHITESPACE); // comma
				}

				$s .= $this->parser->fetchAll(')', T_WHITESPACE);

				if ($this->parser->isNext(';')) { // abstract method

				} elseif ($this->parser->isNext('{')) {
					$s .= $this->parser->fetch() . $methodStartCode;
				}


			} elseif ($this->parser->isCurrent(T_VARIABLE) && $this->parser->isNext(T_INSTANCEOF)) {
				$variable = $token;
				$ws = $this->parser->fetchAll(T_WHITESPACE, T_INSTANCEOF);
				$className = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$fullClassName = $this->fullClass($className);
				$generics = $this->fetchGenericParameter();
				$additionalCode = NULL;

				$typeValuesCode = NULL; // actual (resolved) type values
				if ($generics) {
					foreach ($generics as $typeValue) $typeValuesCode[] = ($this->currentTypeArgs && in_array($typeValue, $this->currentTypeArgs)) ? "\\Enhancer\\Generics\\Registry::resolveTypeArgument(\$this, '$typeValue')" : "'{$this->fullClass($typeValue)}'";
					$typeValuesCode = implode(', ', $typeValuesCode);
				}

				$classNameCode = NULL; // actual hint name
				if ($this->currentTypeArgs && in_array($className, $this->currentTypeArgs)) {
					$classNameCode = "\\Enhancer\\Generics\\Registry::resolveTypeArgument(\$this, '$fullClassName')";
				}

				if ($classNameCode || $typeValuesCode) {
					if ( ! $classNameCode) $classNameCode = "'$fullClassName'";
					$additionalCode .= " && \\Enhancer\\Generics\\Registry::checkInstance($variable, $classNameCode, array($typeValuesCode))";
				}

				$s .= $variable . $ws . $className . $additionalCode;

			} else {
				$s .= $token;
			}
		}

		return $s;
	}



	/*****************  parsing  *****************j*d*/



	/**
	 * @return TypeArgument[]
	 */
	private function fetchGenericParameter()
	{
		$params = array();
		if (!$this->parser->isNext('<')) {
			return $params;
		}

		$this->parser->fetch();
		while ( ! $this->parser->isNext('>')) {
			$code = trim($this->parser->fetchUntil('>', ','));
			$params[] = TypeArgument::create($code, $this->uses, $this->namespace);
		}
		$this->parser->fetch();

		return $params;
	}



	/*****************  helper code generating  *****************j*d*/



	private function createCodeGenericParameter($generics)
	{
		$parts = array();
		foreach ($generics as $v) {
			$v = $v->name;

			if ($this->currentTypeArgs && in_array($v, $this->currentTypeArgs)) { // type argument
				$parts[] = "\\Enhancer\\Generics\\Registry::resolveTypeArgument(\$this, '$v')";

			} else { // just a simple class name
				$parts[] = "'{$this->fullClass($v)}'";
			}
		}

		return 'array(' . implode(', ', $parts) . ')';
	}



	/*****************  utils  *****************j*d*/



	/**
	 * @param string $className
	 * @return string
	 */
	private function fullClass($className)
	{
		$segment = strtolower(substr($className, 0, strpos("$className\\", '\\')));
		$full = isset($this->uses[$segment])
			? $this->uses[$segment] . substr($className, strlen($segment))
			: $this->namespace . '\\' . $className;
		return ltrim($full, '\\'); //str_replace('\\', '\\\\', ltrim($full, '\\'));
	}

}
