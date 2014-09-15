<?php

/**
 * BackendController
 * Murrmurr framework
 *
 * backend controller
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 1.0
 */

class BackendController extends BaseController
{
	public function process() {
		$loginstatus = Registry::get('authorisation')->is_logged_in();
		$userId = Registry::get('authorisation')->uid;
		if($loginstatus === true && $userId === 2) {
			$page = new TemplateView('backend.htm');

			if(isset($this->POST['formaction']) && $this->POST['formaction'] === 'newscreate') {
				var_dump($this->POST);
			}

			$page->publish($this);
		} else {
			Router::_404();
		}
	}
}

?>