<?
# This should not be a .php file, but it is for the sake of autoloader

class Fruit
  var apples = 0
  var numbers = [
    1: 'one',
    2: 'two',
    3: 'three',
  ]

  fn addApples(n = 1)
    this.apples += n
    return this

  fn countApples()
    apples = this.apples
    out = 'You have '
    out .= isset(this.numbers[apples]) ? this.numbers[apples] : apples
    if (this.apples == 1)
      return out . ' apple.'
    else
      return "$out apples."
