new instance - simple
<?php#e
$f = new Foo;
?>
<?php#c
$f = \GenericsRegistry::newInstance('Foo', NULL);
?>
------------------------------------------------j-d--


new instance - generics
<?php#e
$f = new Factory<Foo>();
?>
<?php#c
$f = \GenericsRegistry::newInstance('Factory', array('Foo'));
?>
------------------------------------------------j-d--


new instance - generics + arguments
<?php#e
$f = new Factory<Foo>($em, 2, foo(3));
?>
<?php#c
$f = \GenericsRegistry::newInstance('Factory', array('Foo'), $em, 2, foo(3));
?>
------------------------------------------------j-d--


new instance of type-argument
<?php#e
class Factory<E>
{
	function create($id)
	{
		return new E($id);
	}
}
?>
<?php#c
\GenericsRegistry::registerClass('Collection', array('E'));class Factory implements \GenericType
{public function getParametrizedType($parameterName) { return "TODO"; }
	function create($id)
	{
		return \GenericsRegistry::newInstance(\GenericsRegistry::resolveTypeArgument($this, 'E'), NULL, $id);
	}
}
?>
------------------------------------------------j-d--


new instance of generic with type-argument
<?php#e

class Factory<E>
{
	public function createCollection(array $ids) {
		return new Collection<E>($ids[0], $ids[1]);
	}
}
?>
<?php#c
\GenericsRegistry::registerClass('Factory', array('E')); class Factory implements \GenericType
{public function getParametrizedType($parameterName) { return "TODO"; }
	public function createCollection($ids) {
		return \GenericsRegistry::newInstance('Collection', array(\GenericsRegistry::resolveTypeArgument($this, 'E')), $ids[0], $ids[1]);
	}
}
?>
