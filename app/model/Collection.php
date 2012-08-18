<?php

/**
 *
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class Collection<E> implements \IteratorAggregate
{
	private $items;

	public function add(E $item)
	{
		$this->items[] = $item;
	}

	public function merge(Collection<E> $items)
	{
		foreach ($items as $item) $this->items[] = $item;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}
}

