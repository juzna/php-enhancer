<?php

use ORM\Entity;

class Movie extends ORM\Entity
{
	// This will call static method attr(...), which can provide new properties, methods, etc.
	//  You can call any static method here
	attr('name', 'string');
	attr('rating', 'float');


	public function __construct($name, $rating)
	{
		echo "in Movie constructor\n";
		$this->name = $name;
		$this->rating = $rating;
	}

}
