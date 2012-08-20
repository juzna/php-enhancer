<?php

namespace Config;

/**
 * Gets service by a callback
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class CallbackAccessor<S> extends AbstractAccessor<S>
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
