<?php

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$s = new Selection<User>($this->db);
		$s->where('id >= ?', 2);
		$this->template->users = $s->toCollection();
	}

}
