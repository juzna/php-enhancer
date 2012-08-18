<?php

namespace Enhancer\Utils;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PhpSyntax extends Nette\Object
{

	const OK_MSG = "No syntax errors";



	/**
	 * @param string $file
	 * @return \ErrorException|NULL
	 */
	public static function check($file)
	{
		exec('`which php` -l ' . escapeshellarg($file) . ' 2>&1', $output);

		if (strncmp(array_pop($output), self::OK_MSG, strlen(self::OK_MSG)) === 0) {
			return NULL;

		} else {
			$errorMgs = array_shift($output);
			$line = 0;
			if (preg_match('~on line (\d+)~', $errorMgs, $match)) {
				$line = $match[1];
			}

			return new \ErrorException($errorMgs, 0, 0, $file, $line);
		}
	}

}
