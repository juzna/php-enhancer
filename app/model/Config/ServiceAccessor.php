<?php

namespace Config;

/**
 * Gets access to a service
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
interface ServiceAccessor<S>
{
	/**
	 * Get instance of the service
	 */
	function S get();
}
