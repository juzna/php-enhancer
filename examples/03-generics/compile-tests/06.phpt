more type arguments
<?php#e
namespace A\B;
class ArrayHash<Key, Val>
{

}
?>
<?php#c
namespace A\B;
\Enhancer\Generics\Registry::registerClass('A\B\ArrayHash', array('Key', 'Val'));class ArrayHash implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }

}
?>
------------------------------------------------j-d--


more type arguments and whitespaces there and over
<?php#e
namespace A\B;
class ArrayHash< Key, Val >
{

}
?>
<?php#c
namespace A\B;
\Enhancer\Generics\Registry::registerClass('A\B\ArrayHash', array('Key', 'Val'));class ArrayHash implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }

}
?>
