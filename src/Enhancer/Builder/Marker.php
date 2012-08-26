<?php

namespace Enhancer\Builder;

use Nette;
use Enhancer\Utils\PhpParser;

/**
 * Marks a place in output
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class Marker
{
	/** @var string Optional name, for debugging */
	public $name;

	/** @var int Position of the parser where this marker is placed */
	public $parserPosition;

	/** @var Marker Another marker which denotes end of a section */
	public $finish;

	/** @var string|array Optional code which should be placed to output */
	public $code;



	public function __construct($name, $pos)
	{
		$this->name = $name;
		$this->parserPosition = $pos;
	}



	/** stores a finishing marker */
	public function finish(Marker $mark)
	{
		$this->finish = $mark;
	}



	public function __toString()
	{
		return is_array($this->code) ? implode($this->code) : "$this->code";
	}

}
