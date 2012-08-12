<?php

/**
 * Enhances PHP code
 * Tastes best with EnhancerStream
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class Enhancer
{

	public function enhance($code)
	{
		// here comes all the magic
		$code = preg_replace('/(new\s+\w+)(<\w+>)/', '\\1 /* \\2 */', $code);

		return $code;
	}

}
