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

		if($loginstatus === true && $userId === 3) {
			$this->view = new TemplateView('backend.htm');

			$allnews = OrmModel::get(TBL_NEWS)->find_many();
			$this->view->set('news', $allnews);

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

				$newsId = $news->id();

				if(isset($this->FILES['newsimage']['tmp_name']) && strlen($this->FILES['profile_avatar']['tmp_name'])) {
					$newsImageFile = new Image($this->FILES['newsimage']['tmp_name'], 150, 150, null, 'avatars');

					$newsimagealt = $this->POST['newsimagealt'];
					$newsimagedescription = $this->POST['newsimagedescription'];

					$newsimage = OrmModel::get(TBL_NEWSIMAGE)->create();
					$news->parent_news = $newsId;
					$news->title = $title;
					$news->alt = $newsimagealt;
					$news->description = $newsimagedescription;
					$news->image = $newsImageFile->url;
					$news->save();
				}

				//$this->log('new news', $resultNews);
			}

			$this->view->publish($this);
		} else {
			Router::_404();
		}
	}
}

?>