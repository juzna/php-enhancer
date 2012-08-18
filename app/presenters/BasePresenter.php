<?php
use Nette\Database\Connection;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var \Nette\Database\Connection */
	protected $db;

	public function __construct(Connection $db)
	{
		parent::__construct();
		$this->db = $db;
	}

}
