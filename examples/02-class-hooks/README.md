# Example 01 - helper on new instance

Hook on instance creation.


## Sample
This experimental code

```php
$foo = new Movie("Batman");
```

will get enhanced into something like this:
```php
$foo = \ClassHookHelder::newInstance('Movie', "Batman");
```

See `test.php` demo that it really works.
