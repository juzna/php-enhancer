<?php

namespace GenericsExample\ORM\Entity;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class User extends \Nette\Object
{

	public $id;
	public $admin;

	/**
	 */
	public function __construct($id, $admin = FALSE)
	{
		$this->id = $id;
		$this->admin = $admin;
	}



	public function isAdmin()
	{
		return $this->admin;
	}

}
