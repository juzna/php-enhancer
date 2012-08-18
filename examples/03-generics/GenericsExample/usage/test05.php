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
		$c = new Collection<E>();
		foreach ($ids as $id) {
			$c->add(new E($id));
		}
		return $c;
	}

}

$em = new \stdClass();
$f = new Factory<ORM\Entity\User>($em);

$user = $f->create(99);
dump($user);

$users = $f->createCollection(array(1, 2, 3));
dump($users);

$users->add($f->create(4));

try {
	$users->add(new ORM\Entity\Article(100));
} catch (\Exception $e) {
	dump($e->getMessage());
}
