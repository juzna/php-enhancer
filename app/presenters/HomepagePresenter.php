<?php
# This should not be a .php file, but it is for the sake of autoloader

class HomepagePresenter extends BasePresenter
  fn renderDefault()
    movie = Movie("The Dark Night Returns", 10)
    this.template.movie = movie
