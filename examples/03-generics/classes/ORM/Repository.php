<?php

namespace ORM;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Repository<E>
{

	private $em;
	private $class;

	public function __construct($em)
	{
		$this->em = $em;
		$this->class = $this->getParametrizedType('E');
	}

	public function E find($id)
	{
		$class = $this->class;
		dump("Finding $class#$id");
		return new $class($id);
	}

	public function add(E $entity)
	{
		dump('Persisting entity', $entity);
	}

}
