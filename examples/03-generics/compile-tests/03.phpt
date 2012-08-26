typehint validation
<?php#e
use ORM\Common\Collection;

class Repository<E> extends BaseRepository
{
	function saveOne(E $item)
	{
		return true;
	}

	function saveMultiple(Collection<E> $list)
	{
		return true;
	}
}
?>
<?php#c
use ORM\Common\Collection;

\Enhancer\Generics\Registry::registerClass('Repository', array('E'));class Repository extends BaseRepository implements \Enhancer\Generics\GenericType
{public function getParametrizedType($parameterName) { return \Enhancer\Generics\Registry::getParametrizedTypesForObject($this, $parameterName); }
	function saveOne($item)
	{\Enhancer\Generics\Registry::ensureInstance($item, \Enhancer\Generics\Registry::resolveTypeArgument($this, 'E'), array());
		return true;
	}

	function saveMultiple(Collection $list)
	{\Enhancer\Generics\Registry::ensureInstance($list, 'ORM\Common\Collection', array(\Enhancer\Generics\Registry::resolveTypeArgument($this, 'E')));
		return true;
	}
}
?>
