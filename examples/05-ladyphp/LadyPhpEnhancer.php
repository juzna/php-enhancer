<?php

/**
 * Wrapper for ladyphp
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class LadyPhpEnhancer implements \Enhancer\IEnhancer
{

	public function enhance($code)
	{
		return Lady::parse($code);
	}

}
