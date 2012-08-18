more type arguments
<?php#e
namespace A\B;
class ArrayHash<Key, Val>
{

}
?>
<?php#c
namespace A\B;
\GenericsRegistry::registerClass('A\B\ArrayHash', array('Key', 'Val'));class ArrayHash implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }

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
\GenericsRegistry::registerClass('A\B\ArrayHash', array('Key', 'Val'));class ArrayHash implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }

}
?>
