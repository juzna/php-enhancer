<?php

namespace GenericsExample;


class Collection<E> implements \IteratorAggregate
{
	private $items;

	public function add(E $item)
	{
		$this->items[] = $item;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}
}


class Factory<E>
{
	public function create($id) {
		return new E($id);
	}

	public function createCollection(array $ids) {
		$c = new \Collection<E>();
		foreach ($ids as $id) {
			$c->add(new E($id));
		}
		return $c;
	}

}

$f = new Factory< GenericsExample\ORM\Entity\User>($em);

