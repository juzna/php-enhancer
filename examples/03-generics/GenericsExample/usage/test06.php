<?php
/**
 * Experiment with caching
 */


namespace GenericsExample;

use GenericsExample\ORM\Entity\User;



/*****************  declaration  *****************j*d*/

/**
 * Hash-map like cache
 * Type validation for free
 */
interface Cache<K, V>
{
	public function V load(K $key);
	public function V save(K $key, V $value);

}



/**
 * Cache storing data into files
 */
class FileCache<K, V> implements Cache<K, V>
{
	/** @var string */
	private $dir;

	/**
	 * @param  string $dir Directory where to store cached items
	 */
	public function __construct($dir)
	{
		$this->dir = $dir;
	}

	public function V load(K $key)
	{
		return file_exists($file = "$this->dir/$key") ? unserialize(file_get_contents($file)) : NULL;
	}

	public function V save(K $key, V $value)
	{
		file_put_contents("$this->dir/$key", serialize($value));
		return $value;
	}

}



/**
 * Cache in memory
 */
class MemoryCache<K, V> implements Cache<K, V>
{
	/** @var array K => V */
	private $items;

	public function V load(K $key)
	{
		$k = "$key"; // __toString
		return isset($this->items[$k]) ? $this->items[$k] : NULL;
	}

	public function V save(K $key, V $value)
	{
		return $this->items["$key"] = $value;
	}

}



/**
 * Extending the class
 * Maps a key to User instance
 */
class UserCache<K> extends MemoryCache<K, User> // note only K is type argument, User is an actual type value
{
	// no impl needed, all types are inferred
}



/**
 * Extending further, only store admins
 */
class AdminCache<K> extends UserCache<K>
{
	// public function V save(K $key, V $value) -- should this be possible, when type argument V is not present anymore? Don't think so!
	public function User save(K $key, User $value)
	{
		if ( ! $value->isAdmin()) throw new \InvalidArgumentException("Not an admin dude");
		return parent::save($key, $value);
	}

}



/*****************  usages  *****************j*d*/

// Unconstrained usage
$cache1 = new FileCache(); // no type values given
$cache1->save('john', new User(1));
$cache1->save(1, new \GenericsExample\ORM\Entity\Article(2)); // anything works


// Cache with type values
$cache2 = new FileCache<string,User>();
$cache2->save('john', new User(1));
$cache2->save(1, new \GenericsExample\ORM\Entity\Article(2)); // will throw
