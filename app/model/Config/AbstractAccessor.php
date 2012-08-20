<?php

namespace Config;

/**
 * Makes it easier for other inheriters
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
abstract class AbstractAccessor<S> implements ServiceAccessor<S>
{
	/** @var S */
	private $instance;



	protected abstract S createInstance();



	public function S get()
	{
		if ($this->instance === NULL) {
			$this->instance = $this->createInstance();
		}

		return $this->instance;
	}

}
