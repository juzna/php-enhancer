<?php

namespace GenericsExample\ORM\Entity;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Article extends \Nette\Object
{

	public $id;

	/**
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}

}
