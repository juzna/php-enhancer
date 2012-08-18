<?php

namespace Enhancer;

/**
 * Enhances PHP code
 * Tastes best with EnhancerStream
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
interface IEnhancer
{

	/**
	 * Get enhanced code
	 * @param  string $code
	 * @return string
	 */
	public function enhance($code);

}
