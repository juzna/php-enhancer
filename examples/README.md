# PhpEnhancer examples

Each example is in one directory. It contains:
- **enhancer source code** in `src/` directory; sometimes just one class is enough here.
- **compile tests** in `compile-tests/` directory; examples of enhanced php with manually crafted result of compilation.
- **demo model** classes in `model/` dir with experimental syntax, which will be converted by *enhancer*. These are loaded automatically by class loader.
- **semantic tests** in `tests/` directory - examples of enhanced code with assertions.
- **compiled files** in `output/` directory - compiled version of `src` and `tests`; for debugging. It will contain *model* + *tests* subdirectories.
- *bootstrap.php* - bootstraps class loader with particular enhancer.


You can:
- run compile tests - phpunit
- run semantic tests - phpunit
- compile files - `php compile.php`
- debug one test - `php run.php` after you've edited that file

TODO: refactor these commands



## Naming conventions
All enhancer source classes should be in namespace `Enhancer\Foo` where *Foo* is the name of enhancer. The enhancer class itself should be named `Enhancer\Foo\FooEnhancer`.



## Compiler tests
Files with extension `.phpt` which consist of:

- description
- enhanced code
- compiled code
- horizontal line separator


A *test executor* should load those files and validate each *test case*.
(to be implemented)
