# Example 07 - new keywords

Should add possibility to define new keywords, example:
```php
class Foo
{
	memoize public function getFoo() { ... }
}


class MyFoo implements Foo
{
	private delegate $foo; // all methods from Foo interface will be delegated to here (with a wrapper?)

	function __construct(Foo $foo) {
		$this->foo = $foo;
	}
}
```
