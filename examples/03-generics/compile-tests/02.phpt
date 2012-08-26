new instance - simple
<?php#e
use MyNS\Foo;
$f = new Foo;
?>
<?php#c
use MyNS\Foo;
$f = \Enhancer\Generics\Registry::newInstance('MyNS\Foo', NULL);
?>
------------------------------------------------j-d--


new instance - generics
<?php#e
use MyNS\Foo;
$f = new Factory<Foo>();
?>
<?php#c
use MyNS\Foo;
$f = \Enhancer\Generics\Registry::newInstance('Factory', array('MyNS\Foo'));
?>
------------------------------------------------j-d--


new instance - generics + arguments
<?php#e
$f = new Factory<Foo>($em, 2, foo(3));
?>
<?php#c
$f = \Enhancer\Generics\Registry::newInstance('Factory', array('Foo'), $em, 2, foo(3));
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
\Enhancer\Generics\Registry::registerClass('Factory', array('E'));class Factory implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function create($id)
	{
		return \Enhancer\Generics\Registry::newInstance(\Enhancer\Generics\Registry::resolveTypeArgument($this, 'E'), NULL, $id);
	}
}
?>
------------------------------------------------j-d--


new instance of generic with type-argument
<?php#e
use ORM\Common\Collection;
class Factory<E>
{
	public function createCollection(array $ids) {
		return new Collection<E>($ids[0], $ids[1]);
	}
}
?>
<?php#c
use ORM\Common\Collection;
\Enhancer\Generics\Registry::registerClass('Factory', array('E'));class Factory implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	public function createCollection(array $ids) {
		return \Enhancer\Generics\Registry::newInstance('ORM\Common\Collection', array(\Enhancer\Generics\Registry::resolveTypeArgument($this, 'E')), $ids[0], $ids[1]);
	}
}
?>
