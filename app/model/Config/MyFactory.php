<?php

namespace Config;

/**
 * Creates an instance any time you ask for it
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class MyFactory<S>
{
	/** @var callable */
	private $cb;



	public function __construct(callable $cb)
	{
		$this->cb = $cb;
	}

	public function S createInstance()
	{
		return call_user_func($this->cb);
	}

}
