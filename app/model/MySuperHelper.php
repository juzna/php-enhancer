<?php
use Nette\Database\Connection;

/**
 * {MySuperHelper}
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class MySuperHelper
{
	/** @var \Nette\Database\Connection */
	protected $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	public function getUsers()
	{
		$s = new Selection<User>($this->db);
		$s->where('id >= ?', 2);
		return $s;
	}
}
