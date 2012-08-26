<?php

namespace GettersExample;



/**
 * Movie model class
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class Movie
{
	#attr(name)
	#attr(rating)

	public function __construct($name, $rating)
	{
		$this->name = $name;
		$this->rating = $rating;
	}

}
