<?php

namespace Enhancer\Utils;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PhpFile extends Nette\Object
{

	/**
	 * @var array|\Enhancer\Utils\Code\Statement[]
	 */
	private $statements = array();

	/**
	 * @var PhpParser
	 */
	private $parser;



	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->parser = new PhpParser(file_get_contents($file));
		$this->parse();
	}



	private function parse()
	{
		$T_NAMESPACE = PHP_VERSION_ID < 50300 ? -1 : T_NAMESPACE;
		$T_NS_SEPARATOR = PHP_VERSION_ID < 50300 ? -1 : T_NS_SEPARATOR;
		$T_TRAIT = PHP_VERSION_ID < 50400 ? -1 : T_TRAIT;

		$expected = FALSE;
		$statement = array();
		$namespace = $name = '';
		$level = $minLevel = 0;

		while (($token = $this->parser->fetch()) !== FALSE) {
			$statement[] = $token;
			if (is_array($token)) {
				switch ($token[0]) {
					case T_COMMENT:
					case T_DOC_COMMENT:
					case T_WHITESPACE:
						continue 2;

					case $T_NS_SEPARATOR:
					case T_STRING:
						if ($expected) {
							$name .= $token[1];
						}
						continue 2;

					case $T_NAMESPACE:
					case T_CLASS:
					case T_INTERFACE:
					case T_FUNCTION:
					case $T_TRAIT:
						$expected = $token[0];
						$name = '';
						continue 2;

					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$level++;
				}
			}

			if ($expected) {
				switch ($expected) {
					case T_CLASS:
					case T_INTERFACE:
					case $T_TRAIT:
						if ($level === $minLevel) {
							$this->addClass($namespace . $name, $file);
						}
						break;

					case T_FUNCTION:
						break;

					case $T_NAMESPACE:
						$namespace = $name ? $name . '\\' : '';
						$minLevel = $token === '{' ? 1 : 0;
				}

				$expected = NULL;
			}

			if ($token === '{') {
				$level++;
			} elseif ($token === '}') {
				$level--;
			}
		}
	}



	/**
	 * @param callable $walker
	 */
	public function walk(\Closure $walker)
	{
		foreach ($this->statements as $stt) {
			if (!$stt instanceof Code\Statement) {
				continue;
			}

			$walker($stt);
		}
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return implode("\n", $this->statements);
	}

}
