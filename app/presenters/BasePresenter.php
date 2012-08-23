<?
# This should not be a .php file, but it is for the sake of autoloader

abstract class BasePresenter extends Nette\Application\UI\Presenter
  # ladyphp bug - there must be a method with body, otherwise syntax error
  fn __construct()
    x = 1


