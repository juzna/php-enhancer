<?php

/**
 * 'new Foo' are converted to a hook
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class ClassHookEnhancer implements \Enhancer\IEnhancer
{

	public function enhance($code)
	{
		$parser = new Enhancer\Utils\PhpParser($code);
		$namespace = $s = '';
		$uses = array('' => '');

		while (($token = $parser->fetch()) !== FALSE) {
			if ($parser->isCurrent(T_NAMESPACE)) {
				$namespace = (string) $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$s .= $token . ' ' . $namespace;
				if ($parser->fetch(';', '{') === '{') {
					$s .= '{';
				} else {
					$s .= ';';
				}

			} elseif ($parser->isCurrent(T_USE)) {
				if ($parser->isNext('(')) { // closure?
					$s .= $token;
					continue;
				}
				do {
					$class = $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
					$as = $parser->fetch(T_AS)
						? $parser->fetch(T_STRING)
						: substr($class, strrpos("\\$class", '\\'));
					$uses[strtolower($as)] = $class;
				} while ($parser->fetch(','));
				$parser->fetch(';');

			} elseif ($parser->isCurrent(T_NEW)) {
				$prefix = $token . $parser->fetchAll(T_WHITESPACE);
				$className = $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				if ($className === 'parent' || $className === 'self') {
					$s .= $prefix . $className;

				} else {
					$segment = strtolower(substr($className, 0, strpos("$className\\", '\\')));
					$full = isset($uses[$segment])
							? $uses[$segment] . substr($className, strlen($segment))
							: $namespace . '\\' . $className;
					$full = ltrim($full, '\\');

					$s .= "\\ClassHookHelper::newInstance('$full'";

					if ($parser->isNext(';')) { // without parentheses
						$s .= ")" . $parser->fetch();

					} elseif ($parser->isNext('(')) {
						$parser->fetch();
						if ( ! $parser->isNext(')')) $s .= ', '; // more arguments follow
					}
				}

			} else {
				$s .= $token;
			}
		}

		return $s;
	}

}



/**
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class ClassHookHelper
{

	public static function newInstance($className /*, $args */)
	{
		$args = func_get_args();
		array_shift($args);
		$cnt = count($args);

		echo "LOG: Creating instance of '$className' with $cnt arguments\n";

		$refl = new ReflectionClass($className);
		$ret = $refl->newInstanceArgs($args);

		echo "LOG: ... done\n";

		return $ret;
	}
}
