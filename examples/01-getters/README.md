# Example 01 - getters and setters

Very simple *enhancer* generates a) property, b) getter and c) setter.


## Sample
This experimental code

```php
class Movie
{
	#attr(name)
}
```

will get enhanced into something like this:
```php
class Movie
{
	private $name;

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
	    $this->name = $name;
	    return $this;
	}
}
```

See `test.php` demo that it really works.
