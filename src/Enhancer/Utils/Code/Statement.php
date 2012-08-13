<?php

namespace Enhancer\Utils\Code;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Statement extends Nette\Object
{

	/**
	 * @var array
	 */
	public $tokens;



	/**
	 * @return string
	 */
	public function __toString()
	{
		$s = NULL;
		foreach ($this->tokens as $token) {
			$s .= is_array($token) ? $token['value'] : $token;
		}
		return $s;
	}

}
