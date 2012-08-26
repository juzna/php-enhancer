generic class with constrained argument
<?php#e
use ORM\Entity;
class Repository<E extends Entity>
{
}
?>
<?php#c
use ORM\Entity;
\GenericsRegistry::registerClass('Repository', array(new \TypeArgument('E', 'ORM\Entity', TRUE));class Repository implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
}
?>
------------------------------------------------j-d--

