simple generic class, oneline
<?php#e
class Collection<E> { }
?>
<?php#c
\Enhancer\Generics\Registry::registerClass('Collection', array('E'));class Collection implements \Enhancer\Generics\GenericType {public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); } }
?>
------------------------------------------------j-d--

generic class with a method
<?php#e
class Collection<E>
{
	function foo() { }
}
?>
<?php#c
\Enhancer\Generics\Registry::registerClass('Collection', array('E'));class Collection implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function foo() { }
}
?>
------------------------------------------------j-d--

generic class implementing interface
<?php#e
class Collection<E> implements IteratorAggregate
{
	function foo() { }
}
?>
<?php#c
\Enhancer\Generics\Registry::registerClass('Collection', array('E'));class Collection implements IteratorAggregate, \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function foo() { }
}
?>
------------------------------------------------j-d--

generic class extending super class
<?php#e
class Collection<E> extends \Nette\Object
{
	function foo() { }
}
?>
<?php#c
\Enhancer\Generics\Registry::registerClass('Collection', array('E'));class Collection extends \Nette\Object implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function foo() { }
}
?>
------------------------------------------------j-d--

generic class extending super class and implementing interface
<?php#e
class Collection<E> extends \Nette\Object implements IteratorAggregate
{
	function foo() { }
}
?>
<?php#c
\Enhancer\Generics\Registry::registerClass('Collection', array('E'));class Collection extends \Nette\Object implements IteratorAggregate, \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function foo() { }
}
?>
------------------------------------------------j-d--
