<?php

namespace ClassHookExample;



class MovieFactory
{
	public static function createMovie($name, $rating)
	{
		return new Movie($name, $rating);
	}
}
