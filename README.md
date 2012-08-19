# PhpEnhancer

*Enhance PHP syntax now.*

See examples.


## Motivation
PHP's syntax isn't great, there are things which may be done better. What if you could easily extend it in user-space
(i.e. by a function written in PHP)? Start experimenting today.



## How it works
PHP uses *autoloading* to locate a class on disk when it it being used for the first time. *PhpEnhancer* registers a special
autoloader, which not only locates the class on disk, but also enhances it on the fly.

That being said, *PhpEhnancer* can enhance only classes loaded by *autoloading* mechanism and cannot do anything with those files
loaded via *require*/*include* in PHP. That's actually not a problem, because most projects use autoloading for 99% of the classes.
(Usually, the only exception is the *autoloader* itself being loaded manually and then it takes all the work).

Should this be a problem, you can try adding a [php extension](https://github.com/juzna/php-enhancer/tree/php-extension)
which gets deeper into PHPs core and can hook onto anything ;)


## Examples
Simple examples are in `examples` directory.

Example of a complete project based on *Nette sandbox* is in branch [sandbox](https://github.com/juzna/php-enhancer/tree/sandbox).
Just clone it, install dependencies using composer and you're ready to start your own project with enhanced php.
