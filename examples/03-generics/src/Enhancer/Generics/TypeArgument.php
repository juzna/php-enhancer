<?php

namespace Enhancer\Generics;

use Nette;

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



	public static function create($code, array $uses = null, $namespace = null)
	{
		if (preg_match('~^(\S+)\s+(extends|super)\s+(\S+)$~', $code, $match)) {

			$className = $match[3];
			$segment = strtolower(substr($className, 0, strpos("$className\\", '\\')));
			$full = isset($uses[$segment])
					? $uses[$segment] . substr($className, strlen($segment))
					: $namespace . '\\' . $className;
			$fullClassName = ltrim($full, '\\');

			return new self($match[1], $fullClassName, $match[2] === 'extends');

		} elseif (preg_match('~^\S+$~', $code)) {
			return new self($code);

		} else {
			throw new \InvalidArgumentException("Invalid type argument");
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

	function __toString()
	{
		return $this->name;
	}


}
