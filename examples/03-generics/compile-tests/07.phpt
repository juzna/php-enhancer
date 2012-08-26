generic class with constrained argument
<?php#e
use ORM\Entity;
class Repository<E extends Entity>
{
}
?>
<?php#c
use ORM\Entity;
\Enhancer\Generics\Registry::registerClass('Repository', array(new \TypeArgument('E', 'ORM\Entity', TRUE));class Repository implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
}
?>
------------------------------------------------j-d--

