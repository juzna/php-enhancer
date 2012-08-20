<?php

namespace Enhancer\Builder;

use Nette;
use Enhancer\Utils\PhpParser;

/**
 * Helps building output code
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 *
 * @method fetch()
 * @method fetchToken($arg)
 * @method fetchAll($arg)
 * @method fetchUntil($arg)
 * @method pass()
 * @method passToken($arg)
 * @method passAll($arg)
 * @method passUntil($arg)
 * @method skip()
 * @method skipToken($arg)
 * @method skipAll($arg)
 * @method skipUntil($arg)
 * @method isNext($arg)
 */
class Builder extends Nette\Object
{
	/** @var PhpParser */
	protected $parser;

	/** @var array Output tokens; can be string, array-like token, marker, ... */
	protected $output = array();

	/** @var Marker[] Stack for marks being added; added at bottom, i.e. index 0 is the newest */
	protected $markStack = array();



	public function __construct(PhpParser $parser)
	{
		$this->parser = $parser;
	}



	/**
	 * Append token to output
	 * @param $token
	 */
	public function append($token)
	{
		foreach (func_get_args() as $arg) $this->output[] = $arg;
	}



	/**
	 * Create a new mark
	 * @param string $name
	 * @return Marker
	 */
	public function mark($name = NULL)
	{
		$mark = $this->createMarker($name);
		$this->append($mark);
		$this->markStack[] = $mark;

		return $mark;
	}



	/**
	 * Finishes a marker
	 * @param Marker $mark
	 * @param string $name
	 */
	public function done(Marker $mark, $name = NULL)
	{
		if ( ! $this->markStack || $this->markStack[0] !== $mark) throw new \Nette\InvalidStateException("Unclosed marks");
		array_shift($this->markStack);

		if ($name) { // add finishing marker
			$finishingMark = $this->createMarker($name);
			$mark->finish($finishingMark);
			$this->append($finishingMark);

			return $finishingMark;

		} else {
			// nothing?
		}
	}



	/**
	 * Drops existing marker and anything beyond it
	 * @param Marker $mark
	 */
	public function drop(Marker $mark)
	{
		if ( ! $this->markStack || $this->markStack[0] !== $mark) throw new \Nette\InvalidStateException("Unclosed marks");

	}



	/**
	 * Revert processing to the marker (and try it again)
	 * @param Marker $mark
	 */
	public function revert(Marker $mark)
	{
		$pos = array_search($mark, $this->markStack);
		if ($pos === FALSE) throw new \InvalidArgumentException("Mark not on stack");
		array_splice($this->markStack, 0, $pos + 1, array()); // remove from stack


		$this->parser->revert($mark->parserPosition);
	}



	/**
	 * Find position of a marker
	 * @param Marker $mark
	 * @return int|bool
	 */
	public function findMarker(Marker $mark)
	{
		return array_search($mark, $this->output);
	}



	/**
	 * @return string
	 */
	public function getOutputCode()
	{
//		$ret = array();
//		foreach ($this->output as $token) {
//			$ret
//		}
		return implode($this->output);
	}
	


	/*****************  utils  *****************j*d*/

	protected function createMarker($name)
	{
		return new Marker($name, $this->parser->position);
	}



	/*****************  magic methods  *****************j*d*/

	public function __call($name, $args)
	{
		// delegate
		if (method_exists($this->parser, $name)) {
			return call_user_func_array(array($this->parser, $name), $args);
		}

		// passX - parse tokens into output
		if (preg_match('~pass(\w*)$~A', $name, $match) && method_exists($this->parser, $method = "fetch$match[1]")) {
			$tmp = call_user_func_array(array($this->parser, $method), $args);
			$this->append($tmp);
			return $tmp;
		}

		// skipX - skip tokens
		if (preg_match('~skip(\w*)$~A', $name, $match) && method_exists($this->parser, $method = "fetch$match[1]")) {
			$tmp = call_user_func_array(array($this->parser, $method), $args); // discard output
			return $tmp;
		}

		// try Nette\Object
		return parent::__call($name, $args);
	}

}
