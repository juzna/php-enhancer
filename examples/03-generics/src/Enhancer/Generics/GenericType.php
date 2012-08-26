<?php

namespace Enhancer\Generics;

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface GenericType
{

	/**
	 * @param string $parameterName
	 * @return string
	 */
	function getParametrizedType($parameterName);

}
