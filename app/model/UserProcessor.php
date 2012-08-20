<?php

use Config\ServiceAccessor;

/**
 * Needs a user, but only sometimes
 * NOTE: not supported yet
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class UserProcessor
{
	/** @var ServiceAccessor<S> */
	private $lazy;



	public function __construct(ServiceAccessor<User> $accessor)
	{
		$this->lazy = $accessor;
	}



	public function doSomething()
	{
		$x = $this->lazy->get();
		return get_class($x) . ':' . spl_object_hash($x);
	}

}
