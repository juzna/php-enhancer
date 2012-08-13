<?php

/**
 * 'new Foo' are converted to a hook
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class GenericsEnhancer implements \Enhancer\IEnhancer
{

	/**
	 * @var Enhancer\Utils\PhpParser
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



	/**
	 * @param string $code
	 * @return string
	 */
	public function enhance($code)
	{
		$this->parser = new Enhancer\Utils\PhpParser($code);
		$this->namespace = $s = '';
		$uses = array('' => '');

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
				do {
					$class = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
					$as = $this->parser->fetch(T_AS)
						? $this->parser->fetch(T_STRING)
						: substr($class, strrpos("\\$class", '\\'));
					$uses[strtolower($as)] = $class;
				} while ($this->parser->fetch(','));
				$this->parser->fetch(';');

			} elseif ($this->parser->isCurrent(T_NEW)) {
				$prefix = $token . $this->parser->fetchAll(T_WHITESPACE);
				$className = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				if ($className === 'parent' || $className === 'self' || $className === 'static') {
					$s .= $prefix . $className;

				} elseif ($className) {
					$s .= "\\GenericsRegistry::newInstance('" . $this->fullClass($className) . "'";

					if ($generics = $this->fetchGenericParameter()) {
						$s .= ', array(\'' . implode('\', \'', $generics) . '\')';
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
				$classDef = array(
					$token . $this->parser->fetchAll(T_WHITESPACE), NULL, NULL, NULL,
				);
				$classDef[1] .= $className = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$generics = $this->fetchGenericParameter();
				if ($this->parser->isNext(T_EXTENDS)) {
					$classDef[2] .= $this->parser->fetch() . $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				}
				if ($this->parser->isNext(T_IMPLEMENTS)) {
					$classDef[3] .= $this->parser->fetch() . $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				}
				if ($generics) {
					$classDef[3] .= (!$classDef[3] ? ' implements ' : ', ') . '\GenericType';
				}

				$s .= '\\GenericsRegistry::registerClass(\'' .
					$this->fullClass($className) . '\', array(\'' .
					implode('\', \'', $generics) .
					'\')); ';
				$s .= implode($classDef);

			} elseif ($this->parser->isCurrent(T_FUNCTION)) {
				$s .= $token . $this->parser->fetchAll(T_WHITESPACE);
				$name = preg_split('~\s+~', $this->parser->fetchUntil('('), 2);
				if (count($name) === 1) {
					$s .= reset($name);
				} else {
					array_shift($name); // todo: validate return type
					$s .= reset($name);
				}

				$s .= $this->parser->fetch(); // fetch (
				while ($hint = $this->parser->fetchUntil(T_VARIABLE)) {

				}

			} else {
				$s .= $token;
			}
		}

		dump($s);

		return $s;
	}



	/**
	 * @return array
	 */
	private function fetchGenericParameter()
	{
		$params = array();
		if (!$this->parser->isNext('<')) {
			return $params;
		}

		$this->parser->fetch();
		while (!$this->parser->isNext('>')) {
			if ($this->parser->isNext(T_STRING) || $this->parser->isNext(T_NS_SEPARATOR)) {
				$params[] = str_replace('\\', '\\\\', $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR));
				continue;
			}

			$this->parser->fetch(); // ?
		}
		$this->parser->fetch();

		return $params;
	}



	/**
	 * @param string $className
	 * @return string
	 */
	private function fullClass($className)
	{
		$segment = strtolower(substr($className, 0, strpos("$className\\", '\\')));
		$full = isset($uses[$segment])
			? $uses[$segment] . substr($className, strlen($segment))
			: $this->namespace . '\\' . $className;
		return str_replace('\\', '\\\\', ltrim($full, '\\'));
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface GenericType
{

	/**
	 * @param string $parameterName
	 * @return string
	 */
	function getParametrizedType($parameterName);

}



/**
 * @internal
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class GenericsRegistry
{

	/**
	 * @var array
	 */
	private static $instances = array();

	/**
	 * @var array
	 */
	private static $classes = array();



	/**
	 * @param string $className
	 * @param array $genericTypes
	 *
	 * @return mixed
	 */
	public static function newInstance($className, $genericTypes /*, $args */)
	{
		$args = func_get_args();
		array_shift($args); // className
		array_shift($args); // genericTypes
		$cnt = count($args);

		echo "LOG: Creating instance of '$className' with $cnt arguments\n";

		$refl = new ReflectionClass($className);
		$obj = $refl->newInstanceArgs($args);

		echo "LOG: ... done\n";

		self::$instances[spl_object_hash($obj)] = $genericTypes;
		return $obj;
	}



	/**
	 * @param object $object
	 * @return array
	 */
	public static function getParametrizedTypesForObject($object)
	{
		$oid = spl_object_hash($object);
		if (!isset(self::$instances[$oid])) {
			return array();
		}

		return self::$instances[$oid];
	}



	/**
	 * @param string $className
	 * @param array $typeNames
	 */
	public static function registerClass($className, array $typeNames)
	{
		self::$classes[strtolower($className)] = $typeNames;
	}

}
