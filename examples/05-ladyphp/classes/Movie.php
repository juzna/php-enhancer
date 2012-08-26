<?
# This should not be a .php file, but it is for the sake of autoloader

class Movie
  public name
  public rating = 5

  fn __construct(name, rating = null)
    this.name = name;
    if (rating !== null) this.rating = rating
