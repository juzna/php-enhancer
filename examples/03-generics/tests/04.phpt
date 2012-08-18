instanceof - simple
<?php#e
dump($foo instanceof Foo);
?>
<?php#c
dump($foo instanceof Foo);
?>
------------------------------------------------j-d--


instanceof - generic
<?php#e
dump($foo instanceof Foo<User>);
?>
<?php#c
dump($foo instanceof Foo && \GenericsRegistry::checkInstance($foo, 'Foo', array('User')));
?>
------------------------------------------------j-d--


instanceof - within class
<?php#e
class Repository<E>
{
	function foo($foo)
	{
		return $foo instanceof Foo<E>;
	}
}
?>
<?php#c
\GenericsRegistry::registerClass('Repository', array('E'));class Repository implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
	function foo($foo)
	{
		return $foo instanceof Foo && \GenericsRegistry::checkInstance($foo, 'Foo', array(\GenericsRegistry::newInstance(\GenericsRegistry::resolveTypeArgument($this, 'E'))));
	}
}
?>
------------------------------------------------j-d--
