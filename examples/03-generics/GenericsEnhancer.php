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


		$this->parser = new Enhancer\Utils\PhpParser($code);
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
				do {
					$class = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
					$as = $this->parser->fetch(T_AS)
						? $this->parser->fetch(T_STRING)
						: substr($class, strrpos("\\$class", '\\'));
					$this->uses[strtolower($as)] = $class;
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
				$classDef = array(
					$token . $this->parser->fetchAll(T_WHITESPACE), NULL, NULL, NULL,
				);
				$classDef[1] .= $className = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$generics = $this->currentTypeArgs = $this->fetchGenericParameter();
				if ($this->parser->isNext(T_EXTENDS)) {
					$classDef[2] .= $this->parser->fetch() . $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				}
				if ($this->parser->isNext(T_IMPLEMENTS)) {
					$classDef[3] .= $this->parser->fetch() . $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				}
				if ($generics) {
					$classDef[3] .= (!$classDef[3] ? ' implements ' : ', ') . '\GenericType';
				}

				$classDef[4] = $this->parser->fetchAll('{'); // start class

				$classDef[5] = $generics ? 'public function getParametrizedType($parameterName) { return "TODO"; }' : '';

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

//		dump($s);

		return $s;
	}



	/*****************  parsing  *****************j*d*/



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



	/*****************  helper code generating  *****************j*d*/



	private function createCodeGenericParameter($generics)
	{
		$parts = array();
		foreach ($generics as $v) {
			if ($this->currentTypeArgs && in_array($v, $this->currentTypeArgs)) { // type argument
				$parts[] = "\\GenericsRegistry::resolveTypeArgument(\$this, '$v')";

			} else { // just a simple class name
				$parts[] = "'$v'";
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
	 * @var array  oid => typeArgumentName => TypeValue
	 */
	private static $instances = array();

	/**
	 * @var array  className => index => TypeArgument
	 */
	private static $classes = array();



	/**
	 * @param string $className
	 * @param string[]|TypeValue[] $rawTypeValues
	 *
	 * @return mixed
	 */
	public static function newInstance($className, array $rawTypeValues = null /*, $args */)
	{
		$refl = new ReflectionClass($className); // autoloads the class
		$typeValues = self::resolveTypeValues($className, $rawTypeValues); // check types

		$args = func_get_args();
		array_shift($args); // className
		array_shift($args); // genericTypes
		$cnt = count($args);

		echo "LOG: Creating instance of '$className' with $cnt arguments\n";

		$obj = $refl->newInstanceArgs($args);

		echo "LOG: ... done\n";

		self::$instances[spl_object_hash($obj)] = $typeValues;

		return $obj;
	}



	/**
	 * @param object $object
	 * @return TypeValue[]  typeArgumentName => TypeValue
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
	 * @param string[] $typeArguments
	 */
	public static function registerClass($className, array $typeArguments)
	{
		$x = array();
		foreach ($typeArguments as $arg) $x[] = TypeArgument::create($arg);

		echo "LOG: registering class $className\n";

		self::$classes[strtolower($className)] = $x;
	}



	/**
	 * Check if object matches generic type (class + type values)
	 * @param object $object
	 * @param string $className
	 * @param TypeValue[] $typeValues
	 * @return bool
	 */
	public static function checkInstance($object, $className, array $typeValues)
	{
		if ( ! $object instanceof $className) return FALSE;

		// TODO

		return TRUE;
	}



	/**
	 * Same as #checkInstance, but throws if invalid
	 * @param object $object
	 * @param string $className
	 * @param TypeValue[] $typeValues
	 */
	public static function ensureInstance($object, $className, array $typeValues)
	{
		if ( ! self::checkInstance($object, $className, $typeValues)) throw new Exception("Invalid type");
	}


	/**
	 * For a particular instance of generic class, find the actual class
	 * e.g E needs to be resolved in: return new Collection<E>(...)
	 * @param object $object
	 * @param string $typeArgumentName
	 */
	public function resolveTypeArgument($object, $typeArgumentName)
	{
		if ( ! $typeValues = self::getParametrizedTypesForObject($object)) throw new InvalidArgumentException("Object has no type-values");
		if ( ! isset($typeValues[$typeArgumentName])) throw new InvalidArgumentException("Object does not have type-value named '$typeArgumentName'");

		return $typeValues[$typeArgumentName]->actualClass;
	}



	/*****************  utils  *****************j*d*/



	/**
	 * From a generic class and raw type values, get resolved TypeValue's
	 * @param string $className
	 * @param string[]|TypeValue[] $rawTypeValues
	 * @return TypeValue[]  typeArgumentName => TypeValue
	 */
	protected static function resolveTypeValues($className, $rawTypeValues)
	{
		$className = strtolower($className);

		if( ! isset(self::$classes[$className])) throw new InvalidArgumentException("Class '$className' is not generic");
		$typeArguments = self::$classes[$className];

		if(count($typeArguments) !== count($rawTypeValues)) throw new InvalidArgumentException("Generic values do not mach generic arguments");

		$typeValues = array();
		foreach($typeArguments as $k => $typeArgument) {
			$val = $rawTypeValues[$k];

			if ( ! $val instanceof TypeValue) { // string -> TypeValue
				TypeValue::create($typeArgument, $val);

			} else { // validate TypeValue to match
				// TODO
			}

			$typeValues[$typeArgument->name] = $val;
		}

		return $typeValues;
	}

}



/**
 * Type argument for generics
 *
 * Examples:
 *  - E
 *  - E extends Entity
 *  - E super RegisteredUserEntity
 */
class TypeArgument
{
	public $name;
	public $className;
	public $extends;



	public static function create($code)
	{
		if (preg_match('~^(\S+)\s+(extends|super)\s+(\S+)$~', $code, $match)) {
			return new self($match[1], $match[3], $match[2] === 'extends');

		} elseif (preg_match('~^\S+$~', $code)) {
			return new self($code);

		} else {
			throw new InvalidArgumentException("Invalid type argument");
		}
	}

	protected function __construct($name, $className = NULL, $extends = TRUE)
	{
		$this->name = $name;
		$this->className = $className;
		$this->extends = $extends;
	}

	public function matches($actualClassName)
	{
		if ( ! $this->className) return TRUE;

		if ($this->extends) { // actualClassName should be instanceof className
			return Nette\Reflection\ClassType::from($actualClassName)->isSubclassOf($this->className);

		} else { // superclass
			return Nette\Reflection\ClassType::from($this->className)->isSubclassOf($actualClassName);
		}
	}

}


/**
 * Particular instance of TypeArgument
 */
class TypeValue extends TypeArgument
{
	public $actualClass;



	public static function create(TypeArgument $arg, $actualClass)
	{
		if ( ! $arg->matches($actualClass)) throw new InvalidArgumentException("Incompatible type");

		$ret = new self($arg->name, $arg->className, $arg->extends);
		$ret->actualClass = $actualClass;

		return $ret;
	}

}

