<?php

namespace Enhancer\Loaders;

use Nette;

/**
 * Nette's RobotLoader with enhancing enabled
 * FIXME: doesn't work because of private properties :(
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class RobotLoader extends Nette\Loaders\RobotLoader
{
	// copied whole method just for one change
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\'); // PHP namespace bug #49143
		$info = & $this->list[$type];

		if ($this->autoRebuild && empty($this->checked[$type]) && (is_array($info) ? !is_file($info[0]) : $info < self::RETRY_LIMIT)) {
			$info = is_int($info) ? $info + 1 : 0;
			$this->checked[$type] = TRUE;
			if ($this->rebuilt) {
				$this->getCache()->save($this->getKey(), $this->list, array(
					Cache::CONSTS => 'Nette\Framework::REVISION',
				));
			} else {
				$this->rebuild();
			}
		}

		if (isset($info[0])) {
			Nette\Utils\LimitedScope::load("enhance://" . $info[0], TRUE); // NOTE: here is the change!

			if ($this->autoRebuild && !class_exists($type, FALSE) && !interface_exists($type, FALSE) && (PHP_VERSION_ID < 50400 || !trait_exists($type, FALSE))) {
				$info = 0;
				$this->checked[$type] = TRUE;
				if ($this->rebuilt) {
					$this->getCache()->save($this->getKey(), $this->list, array(
						Cache::CONSTS => 'Nette\Framework::REVISION',
					));
				} else {
					$this->rebuild();
				}
			}
			self::$count++;
		}
	}

}
