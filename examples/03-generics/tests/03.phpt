typehint validation
<?php#e
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
\GenericsRegistry::registerClass('Repository', array('E'));class Repository extends BaseRepository implements \GenericType
{public function getParametrizedType($parameterName) { return \GenericsRegistry::getParametrizedTypesForObject($this); }
	function saveOne($item)
	{\GenericsRegistry::ensureInstance($item, \GenericsRegistry::resolveTypeArgument($this, 'E'), array());
		return true;
	}

	function saveMultiple(Collection $list)
	{\GenericsRegistry::ensureInstance($list, 'Collection', array(\GenericsRegistry::resolveTypeArgument($this, 'E')));
		return true;
	}
}
?>
