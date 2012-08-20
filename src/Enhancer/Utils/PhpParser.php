<?php

namespace Enhancer\Utils;

use Nette;

/**
 * Simple tokenizer for PHP.
 *
 * @author     David Grudl
 */
class PhpParser extends Nette\Utils\Tokenizer
{

	/**
	 * @param array $code
	 */
	public function __construct($code)
	{
		$this->ignored = array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE);
		foreach (@token_get_all($code) as $token) {
			$this->tokens[] = is_array($token) ? self::createToken($token[1], $token[0]) : $token;
		}
	}


	public function revert($position)
	{
		$this->position = $position;
	}

}
