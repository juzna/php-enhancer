simple generic class, oneline
<?php#e
class Collection<E> { }
?>
<?php#c
\GenericsRegistry::registerClass('Collection', array('E'));class Collection implements \GenericType {public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); } }
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
\GenericsRegistry::registerClass('Collection', array('E'));class Collection implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
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
\GenericsRegistry::registerClass('Collection', array('E'));class Collection implements IteratorAggregate, \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
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
\GenericsRegistry::registerClass('Collection', array('E'));class Collection extends \Nette\Object implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
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
\GenericsRegistry::registerClass('Collection', array('E'));class Collection extends \Nette\Object implements IteratorAggregate, \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
	function foo() { }
}
?>
------------------------------------------------j-d--
