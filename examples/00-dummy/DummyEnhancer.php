<?php

/**
 * Calls you dummy
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class DummyEnhancer implements \Enhancer\IEnhancer
{

	public function enhance($code)
	{
		return str_replace('world', 'dummy', $code);
	}

}
