<?

class Fruit
  var apples = 0
  var numbers = [
    1: 'one',
    2: 'two',
    3: 'three'
  ]

  fn addApples(n = 1)
    if (n >= 0)
      this.apples += n
    return this

  fn countApples()
    apples = this.apples
    out = 'You have '
    out .= isset(this.numbers[apples])
           ? this.numbers[apples] : apples
    switch (apples)
      case 1
        return out . ' apple.'
      default
        return "$out apples."
