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
						$classNameCode = "\\GenericsRegistry::resolveTypeArgument(\$this, '$className')";
					} else {
						$classNameCode = "'" . $this->fullClass($className) . "'";
					}

					$s .= "\\GenericsRegistry::newInstance(" . $classNameCode;

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
					$classDef[5] .= ($classDef[5] ? ', ' : '') . '\GenericType';
				}

				$classDef[6] = $this->parser->fetch(T_WHITESPACE) . $this->parser->fetchAll('{'); // start class

				$classDef[7] = $generics ? 'public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }' : '';

				$s .= '\\GenericsRegistry::registerClass(\'' .
					$this->fullClass($className) . '\', array(\'' .
					implode('\', \'', $generics) .
					'\'));';
				$s .= implode($classDef);

			} elseif ($this->parser->isCurrent(T_FUNCTION)) {
				$s .= $token . $this->parser->fetchAll(T_WHITESPACE);

				$name = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR, T_WHITESPACE);
				if ($this->parser->isNext('(')) { // it was function name
					$s .= $name;

				} else { // it was return type function name follows
					// TODO: store return type somewhere

					$name = $this->parser->fetchAll(T_STRING, T_NS_SEPARATOR, T_WHITESPACE);
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
						foreach ($hintGenerics as $typeValue) $typeValuesCode[] = ($this->currentTypeArgs && in_array($typeValue, $this->currentTypeArgs)) ? "\\GenericsRegistry::resolveTypeArgument(\$this, '$typeValue')" : "'{$this->fullClass($typeValue)}'";
						$typeValuesCode = implode(', ', $typeValuesCode);
					}

					$hintCode = NULL; // actual hint name
					if ($this->currentTypeArgs && in_array($hint, $this->currentTypeArgs)) {
						$hintCode = "\\GenericsRegistry::resolveTypeArgument(\$this, '$hint')";
						$hint = $ws1 = NULL; // clear real typehint FIXME: if 'E extends Entity', leave 'Entity'
					}

					if ($typeValuesCode || $hintCode) {
						$hintCode = $hintCode ?: "'{$this->fullClass($hint)}'";
						$methodStartCode .= "\\GenericsRegistry::ensureInstance($variable, $hintCode, array($typeValuesCode));";
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
					foreach ($generics as $typeValue) $typeValuesCode[] = ($this->currentTypeArgs && in_array($typeValue, $this->currentTypeArgs)) ? "\\GenericsRegistry::resolveTypeArgument(\$this, '$typeValue')" : "'{$this->fullClass($typeValue)}'";
					$typeValuesCode = implode(', ', $typeValuesCode);
				}

				$classNameCode = NULL; // actual hint name
				if ($this->currentTypeArgs && in_array($className, $this->currentTypeArgs)) {
					$classNameCode = "\\GenericsRegistry::resolveTypeArgument(\$this, '$fullClassName')";
				}

				if ($classNameCode || $typeValuesCode) {
					if ( ! $classNameCode) $classNameCode = "'$fullClassName'";
					$additionalCode .= " && \\GenericsRegistry::checkInstance($variable, $classNameCode, array($typeValuesCode))";
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

		$obj = $refl->newInstanceArgs($args);
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
	public static function resolveTypeArgument($object, $typeArgumentName)
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
				TypeValue::createValue($typeArgument, $val);

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



	public static function createValue(TypeArgument $arg, $actualClass)
	{
		if ( ! $arg->matches($actualClass)) throw new InvalidArgumentException("Incompatible type");

		$ret = new self($arg->name, $arg->className, $arg->extends);
		$ret->actualClass = $actualClass;

		return $ret;
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ClassType extends Nette\Reflection\ClassType
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Method extends Nette\Reflection\Method
{

}
