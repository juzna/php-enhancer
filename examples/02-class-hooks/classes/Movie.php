<?php

class Movie
{
	public $name;
	public $rating;


	public function __construct($name, $rating)
	{
		echo "in Movie constructor\n";
		$this->name = $name;
		$this->rating = $rating;
	}

}
