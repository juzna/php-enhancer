<?php

use Config\MyFactory;

/**
 * It would like to create as many Selection's as it wants
 * NOTE: not supported yet
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class UserProcessor
{
	/** @var MyFactory<Selection<User>> */
	private $factory;



	public function __construct(MyFactory<Selection<User>> $factory)
	{
		$this->factory = $factory;
	}



	public function User getNext($id)
	{
		$users = $this->factory->createInstance(); // always new Selection instance
		return $users->where('id > ?', $id)->order('id')->fetch(); // Selection<E>::fetch() returns E, i.e. User in our case
	}

}
