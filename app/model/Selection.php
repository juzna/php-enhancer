<?php
use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;

class Selection<E extends ActiveRow> extends \Nette\Database\Table\Selection
{

	public function __construct(Connection $db) {
		parent::__construct(strtolower($this->getParametrizedType('E') . 's'), $db);
	}

	protected function createRow(array $row)
	{
		return new E($row, $this);
	}

	public function toCollection()
	{
		$ret = new Collection<E>();
		foreach ($this as $item) $ret->add($item);

		return $ret;
	}

}
