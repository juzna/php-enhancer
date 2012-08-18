<?php

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{

		$this->template->users = $this->context->super->getUsers()->toCollection();
	}

}
