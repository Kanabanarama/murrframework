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

		$user = OrmModel::get(TBL_USER)
			->where('uid', $userId)
			->find_one();

		if($loginstatus === true && $user->privileges >= 1) {
			$this->view = new TemplateView('backend.htm');


			if(isset($this->POST['formaction']) && $this->POST['formaction'] === 'filesdelete') {
				$filesToDelete = $this->POST['imageselect'];
				foreach($filesToDelete as $fileToDelete) {
					if(file_exists($fileToDelete) && is_writable($fileToDelete)) {
						unlink($fileToDelete);
					}
				}
			}

			/*$directory = "application/uploads/images/";
			if ($handle = opendir($directory)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..' && !is_dir($directory.$file)) {
						echo "$file\n";
						$result = unlink($directory.$file);
						echo($result);
					}
				}
				closedir($handle);
			}*/

			$directory = "application/uploads/images/*/";
			$templatePath = '../uploads/images/avatars/';
			$images = glob("" . $directory . "*.png");
			if($images) {
				$images = array_slice($images, 0, 20);
				$imagePaths = array();
				foreach($images as $image) {
					//$result = unlink($image);
					//var_dump($result);
					$imagePaths[] = array(
						'realpath' => $image,
						'relpath' => $templatePath.basename($image)
					);
				}
				$this->view->set('images', $imagePaths);
			}

			if(isset($this->POST['formaction']) && $this->POST['formaction'] === 'logsclear') {
				$logsToClear = 'application/logs/errors/error.log';
				if(file_exists($logsToClear) && is_writable($logsToClear)) {
					unlink($logsToClear);
				}
			}

			$directory = "application/logs/*/";
			$logs = glob("" . $directory . "*.log");
			if(count($logs)) {
				if(file_exists($logs[0])) {
					$logLines = file($logs[0]);
					$this->view->set('loglines', $logLines);
				}
			}

			$this->view->publish($this);



			if(isset($this->POST['formaction']) && $this->POST['formaction'] === 'newsdelete') {
				$entryUid = intval($this->POST['newsuid']);
				$newsToDelete = OrmModel::get(TBL_NEWS)
					->where('uid', $entryUid)
					->find_one();
				$newsToDelete->delete();
			}

			$allnews = OrmModel::get(TBL_NEWS)->find_many();
			$this->view->set('news', $allnews);

			if(isset($this->POST['formaction']) && $this->POST['formaction'] === 'newsload') {
				$entryUid = intval($this->POST['data']);
				if($entryUid !== 0) {
					$existingNews = OrmModel::get(TBL_NEWS)
						->where('uid', $entryUid)
						->find_one();

					$this->view->setAll(array(
						'newsuid'			=> $existingNews->uid,
						'newstitle'			=> $existingNews->title,
						'newstext'			=> $existingNews->content,
						'publicationdate'	=> $existingNews->publication_date,
						'newspublished'			=> ($existingNews->published) ? 'checked="checked"' : ''
					));
				}
			}

			if(isset($this->POST['formaction']) && $this->POST['formaction'] === 'newscreate') {
				$title = $this->POST['newstitle'];
				$content= $this->POST['newstext'];
				$publicationDate = $this->POST['publicationdate'];
				$publish = isset($this->POST['newspublished']) ? true : false;

				if($this->POST['newsuid']) {
					$entryUid = intval($this->POST['newsuid']);
					if($entryUid !== 0) {
						$newsToEdit = OrmModel::get(TBL_NEWS)
							->where('uid', $entryUid)
							->find_one();
						$newsToEdit->title = $title;
						$newsToEdit->content = $content;
						$newsToEdit->publication_date = $publicationDate;
						$newsToEdit->published = $publish;
						$resultNews = $newsToEdit->save();
						$newsId = $newsToEdit->uid;
					}
				} else {
					$news = OrmModel::get(TBL_NEWS)->create();
					$news->title = $title;
					$news->content = $content;
					$news->publication_date = $publicationDate;
					$news->published = $publish;
					$news->parent_user  = $userId;
					$resultNews = $news->save();
					if($resultNews) {
						$this->messages[] = 'News wurden erstellt.';
					} else {
						$this->errors[] = 'Fehler beim Erstellen der News.';
					}
					$newsId = $news->id();
				}

				if(isset($this->FILES['newsimage']['tmp_name']) && strlen($this->FILES['newsimage']['tmp_name'])) {
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