<?php

/**
 * Stream wrapper which enhances PHP code before execution
 * NOTE: You must set static $enhancer variable when registered!
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class EnhancerStream
{
	/** @var Enhancer */
	public static $enhancer;

	private $filename;
	private $buffer;
	private $pos;

	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->filename = substr($path, strlen("ehnance://"));
		$this->buffer = self::$enhancer->enhance(file_get_contents($this->filename));
		$this->pos = 0;
		return true;
	}

	public function stream_stat()
	{
		return array(
			'size' => strlen($this->buffer),
		);
	}

	public function stream_read($count)
	{
		$ret = substr($this->buffer, $this->pos, $count);
		$this->pos += $count;
		return $ret;
	}

	public function stream_eof()
	{
		return $this->pos >= strlen($this->buffer);
	}


}
