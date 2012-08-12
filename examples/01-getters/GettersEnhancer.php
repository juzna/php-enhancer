<?php

/**
 * Converts #attr(foo) into getter and setter method
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class GettersEnhancer implements \Enhancer\IEnhancer
{

	public function enhance($code)
	{
		$code = preg_replace_callback('~#attr\((\w+)\)$~m', function($match) {
			$name = $match[1];
			$ucName = ucfirst($name);

			$snippet = "
				private \$$name;

				public function get$ucName()
				{
					return \$this->$name;
				}

				public function set$ucName(\$$name)
				{
					\$this->$name = \$$name;
					return \$this;
				}
			";

			return str_replace("\n", ' ', $snippet); // remove newlines so that it doesn't break line numbers
		}, $code);
		return $code;
	}

}
