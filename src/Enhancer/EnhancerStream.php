<?php

namespace Enhancer;

/**
 * Stream wrapper which enhances PHP code before execution
 * NOTE: You must set static $enhancer variable when registered!
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class EnhancerStream
{

	/**
	 * @var bool
	 */
	public static $debug = FALSE;

	/**
	 * @var IEnhancer
	 */
	public static $enhancer;

	/**
	 * @var string
	 */
	private $filename;

	/**
	 * @var string
	 */
	private $buffer;

	/**
	 * @var integer
	 */
	private $pos;



	/**
	 * @param $path
	 * @param $mode
	 * @param $options
	 * @param $opened_path
	 *
	 * @return bool
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->filename = substr($path, strlen("ehnance://"));
		$this->buffer = self::$enhancer->enhance(file_get_contents($this->filename));
		$this->pos = 0;

		if (self::$debug === TRUE) {
			$tempFile = tempnam(sys_get_temp_dir(), 'PHPEnhancer_');
			file_put_contents($tempFile, $this->buffer);
			if ($e = \Enhancer\Utils\PhpSyntax::check($tempFile)) {
				throw $e;

			} else {
				@unlink($tempFile);
			}
		}

		return true;
	}



	/**
	 * @return array
	 */
	public function stream_stat()
	{
		return array(
			'size' => strlen($this->buffer),
		);
	}



	/**
	 * @param $count
	 *
	 * @return string
	 */
	public function stream_read($count)
	{
		$ret = substr($this->buffer, $this->pos, $count);
		$this->pos += $count;
		return $ret;
	}



	/**
	 * @return bool
	 */
	public function stream_eof()
	{
		return $this->pos >= strlen($this->buffer);
	}

}
