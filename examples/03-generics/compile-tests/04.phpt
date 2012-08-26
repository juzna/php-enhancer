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
dump($foo instanceof Foo && \Enhancer\Generics\Registry::checkInstance($foo, 'Foo', array('User')));
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
\Enhancer\Generics\Registry::registerClass('Repository', array('E'));class Repository implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function foo($foo)
	{
		return $foo instanceof Foo && \Enhancer\Generics\Registry::checkInstance($foo, 'Foo', array(\Enhancer\Generics\Registry::resolveTypeArgument($this, 'E')));
	}
}
?>
------------------------------------------------j-d--
