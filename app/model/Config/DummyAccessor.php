<?php

namespace Config;

/**
 * Give it something, and it'll give it back to you
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class DummyAccessor<S> implements ServiceAccessor<S>
{
	private $item;

	public function __construct(S $item)
	{
		$this->item = $item;
	}


	function S get()
	{
		return $this->item;
	}

}
