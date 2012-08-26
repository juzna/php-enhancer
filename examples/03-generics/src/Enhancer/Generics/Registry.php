<?php

namespace Enhancer\Generics;

use ReflectionClass;
use Nette\Utils\PhpGenerator\Helpers;

/**
 * @internal
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Registry
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

//		$obj = $refl->newInstanceArgs($args);
		$obj = Helpers::createObject($className, array());
		if ($typeValues !== NULL) self::$instances[spl_object_hash($obj)] = $typeValues;

		if (method_exists($obj, '__construct')) {
			callback($obj, '__construct')->invokeArgs($args);
		}

		return $obj;
	}



	/**
	 * @param object $object
	 * @return TypeValue[]  typeArgumentName => TypeValue
	 */
	public static function getParametrizedTypesForObject($object, $parameterName = NULL)
	{
		$oid = spl_object_hash($object);
		if (!isset(self::$instances[$oid])) {
			return array();
		}

		$ret = self::$instances[$oid];

		return ($parameterName !== NULL) ? $ret[$parameterName] : $ret;
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
		if ( ! self::checkInstance($object, $className, $typeValues)) throw new \Exception("Invalid type");
	}


	/**
	 * For a particular instance of generic class, find the actual class
	 * e.g E needs to be resolved in: return new Collection<E>(...)
	 * @param object $object
	 * @param string $typeArgumentName
	 */
	public static function resolveTypeArgument($object, $typeArgumentName)
	{
		if ( ! $typeValues = self::getParametrizedTypesForObject($object)) throw new \InvalidArgumentException("Object has no type-values");
		if ( ! isset($typeValues[$typeArgumentName])) throw new \InvalidArgumentException("Object does not have type-value named '$typeArgumentName'");

		// FIXME
		$x = $typeValues[$typeArgumentName];
		return is_scalar($x) ? $x : $x->actualClass;
	}



	/*****************  utils  *****************j*d*/



	/**
	 * From a generic class and raw type values, get resolved TypeValue's
	 * @param string $className
	 * @param string[]|TypeValue[] $rawTypeValues
	 * @return TypeValue[]  typeArgumentName => TypeValue
	 */
	protected static function resolveTypeValues($className, array $rawTypeValues = NULL)
	{
		$className = strtolower($className);

		if( ! isset(self::$classes[$className])) {
			if ($rawTypeValues) throw new \InvalidArgumentException("Class '$className' is not generic");
			else return NULL;
		}
		$typeArguments = self::$classes[$className];

		if(count($typeArguments) !== count($rawTypeValues)) throw new \InvalidArgumentException("Generic values do not mach generic arguments");

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


