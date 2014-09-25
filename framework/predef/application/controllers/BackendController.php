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
				$title = $this->POST['newstitle'];
				$content= $this->POST['newstext'];
				$publicationDate = $this->POST['publicationdate'];
				$publish = isset($this->POST['newscreate']) ? true : false;

				$news = OrmModel::get(TBL_NEWS)->create();
				$news->title = $title;
				$news->content = $content;
				$news->publication_date = $publicationDate;
				$news->published = $publish;
				$news->parent_user  = $userId;
				$resultNews = $news->save();

				//$this->log('new news', $resultNews);
			}

			$page->publish($this);
		} else {
			Router::_404();
		}
	}
}

?>