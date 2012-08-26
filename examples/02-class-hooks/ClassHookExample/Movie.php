<?php

namespace ClassHookExample;



class Movie
{
	public $name;
	public $rating;


	public function __construct($name, $rating)
	{
		$this->name = $name;
		$this->rating = $rating;
	}

}
