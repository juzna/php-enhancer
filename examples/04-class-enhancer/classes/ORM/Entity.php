<?php

namespace ORM;

/**
 * Base entity class
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class Entity
{


	/*****************  these will generate some code  *****************j*d*/

	public static function attr($name, $type)
	{
		$ucName = ucfirst($name);
		if (self::isScalarType($type)) $type = NULL;

		return "
			private \$$name;

			public function get$ucName()
			{
				return \$this->$name;
			}

			public function set$ucName($type \$$name)
			{
				\$this->$name = \$$name;
				return \$this;
			}
		";
	}

	private static function isScalarType($type)
	{
		return in_array($type, array('int', 'float', 'string', 'bool'));
	}

}
