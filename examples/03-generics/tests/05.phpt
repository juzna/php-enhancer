return type hint
<?php#e
class Foo
{
	public function Entity getEntity()
	{
		// dummy
	}
}
?>
<?php#c
class Foo
{
	public function getEntity()
	{
		// dummy
	}
}
?>
------------------------------------------------j-d--

return type with type argument
<?php#e
class Foo<E>
{
	public function E getEntity()
	{
		// dummy
	}
}
?>
<?php#c
\GenericsRegistry::registerClass('Foo', array('E'));class Foo implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
	public function getEntity()
	{
		// dummy
	}
}
?>
------------------------------------------------j-d--


