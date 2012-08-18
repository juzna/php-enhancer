<?php

namespace GenericsExample\ORM\Entity;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class User extends \Nette\Object
{

	public $id;

	/**
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}

}
