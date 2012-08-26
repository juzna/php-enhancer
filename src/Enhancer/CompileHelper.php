<?php

namespace Enhancer;

use Enhancer\IEnhancer;
use Enhancer\EnhancerStream;

/**
 * Help with compiling
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class CompileHelper
{

	/**
	 * @param $dir
	 * @return array
	 */
	public static function compileDirs(IEnhancer $enhancer, array $dirs)
	{
		$errors = array();

		$usages = \Nette\Utils\Finder::findFiles("*.php")->from($dirs);
		foreach ($usages as $file) {
			/** @var \SplFileInfo $file */

			$outputPath = __DIR__ . '/output/' . str_replace(__DIR__ . '/', '', $file->getRealPath());
			if (self::isFresh($file, $outputPath)) {
					continue; // if newer, not compile again
			}

			try {
				@mkdir(dirname($outputPath), 0777, true);
				file_put_contents($outputPath, $enhancer->enhance(file_get_contents($file->getRealPath())));
			} catch(\Exception $e) {
				$errors[] = $e;
				continue;
			}

			if ($e = \Enhancer\Utils\PhpSyntax::check($outputPath)) {
				$errors[] = array($e);
			}
		}

		return $errors;
	}


	private static function isFresh(\SplFileInfo $file, $outputPath)
	{
//		$enhancerRefl = new \ReflectionClass('Enhancer\Generics\GenericsEnhancer');
		return file_exists($outputPath)
				&& filemtime($outputPath) > filemtime($file->getRealPath());
//				&& filemtime($outputPath) > filemtime($enhancerRefl->getFileName());
	}

}
