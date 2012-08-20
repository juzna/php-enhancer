<?php

/**
 * Class declaration can contain function calls
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class ClassEnhancer implements \Enhancer\IEnhancer
{

	public function enhance($code)
	{
		return $code; // TODO
	}

}
