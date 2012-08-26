<?php

namespace GenericsExample\ORM;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Repository<E>
{

	private $em;

	public function __construct($em)
	{
		$this->em = $em;
	}

	public function E find($id)
	{
		dump("Finding {$this->getParametrizedType('E')}#$id");
		return new E($id);
	}

	public function add(E $entity)
	{
		dump('Persisting entity', $entity);
	}

}
