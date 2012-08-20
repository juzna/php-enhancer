<?php

use ORM\Entity;

class Movie extends ORM\Entity
{
	attr('name', 'string');
	attr('rating', 'float');


	public function __construct($name, $rating)
	{
		echo "in Movie constructor\n";
		$this->name = $name;
		$this->rating = $rating;
	}

}
