<?php

/**
 * Image
 * Murrmurr framework
 *
 * class for manipulating images
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 30.01.2010
 * @version 1.2.1
 */

class Image
{
	private $iImageWidth;
	private $iImageHeight;

	private $aSourceImage;

	/*private $strSourceImagePath;

	private $iSourceImageWidth;
	private $iSourceImageHeight;
	private $iSourceImageMime;

	private $bSourceImageTooBig;*/

	private $aFileTypes;

	private $overlay;

	private $source_img;
	private $img;
	private $path;
	private $url;
	private $tag;

	public function __construct($aFile, $iWidth = 150, $iHeight = 150, $oImageBeyond = null, $strFolder = null) {
		$this->aFileTypes = array(
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif'
		);
		if($aFile) {
			$aImageInfo = GetImageSize($aFile);
			if($aImageInfo) {
				$this->aSourceImage['path'] = $aFile;
				$this->aSourceImage['mime'] = $aImageInfo['mime'];
				$this->aSourceImage['width'] = $aImageInfo[0];
				$this->aSourceImage['height'] = $aImageInfo[1];

				if(in_array($this->aSourceImage['mime'], $this->aFileTypes)) {
					$this->iImageWidth	= $iWidth;
					$this->iImageHeight	= $iHeight;
					$strNewFilename = md5(uniqid(rand(),true)) .'.png';
					$this->url = 'uploads/images/'. (($strFolder) ? $strFolder.'/' : '') . $strNewFilename;
					$this->path = 'application/'.$this->url;

					//$folderTest = ROOT_DIR.'application/uploads/images/'. (($strFolder) ? $strFolder.'/' : '');
					//file_put_contents($folderTest.'asdf.jpg', '');
					//echo substr(sprintf('%o', fileperms($folderTest)), -4);
					//echo(fileowner($folderTest));
					//echo(getenv('APACHE_RUN_USER'));
					//echo(posix_getuid());
					//die;

					$this->tag = '<img src="'.$this->url.'" width="'.$this->iImageWidth.'" height="'.$this->iImageHeight.'">';

					if(($this->aSourceImage['width'] <= $this->iImageWidth) && ($this->aSourceImage['height'] <= $this->iImageHeight)) {
						$this->aSourceImage['resize'] = false;
					} else {
						$this->aSourceImage['resize'] = true;
					}

					if($oImageBeyond instanceof self) { //$this->overlay = $oImageBeyond->img; }
						$this->overlay = $oImageBeyond->__get('img');
					}
				} else { trigger_error("No valid image type", E_USER_NOTICE); }
			}  else { trigger_error("No valid image", E_USER_NOTICE); }
		}  else { trigger_error("Not a file", E_USER_NOTICE); }

		return false;
	}

	private function uploadImage() {
		$strTempFile = $this->aSourceImage['path'];
		//var_dump($strTempFile);
		move_uploaded_file($strTempFile, $this->path);
	}

	private function loadImage() {
		$strTempFile = $this->aSourceImage['path'];

		switch($this->aSourceImage['mime']) {
			case $this->aFileTypes['jpg']:
				$this->img = imageCreateFromJPEG($strTempFile);
				break;
			case $this->aFileTypes['png']:
				$this->img = imagecreatefrompng($strTempFile);
				break;
			case $this->aFileTypes['gif']:
				$this->img = imageCreateFromGIF($strTempFile);
				break;
		}

		$this->source_img = $this->img;
	}


	/*private function setTransparency($new_image,$image_source)
    {
            $transparencyIndex = imagecolortransparent($image_source);
            $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);

            if ($transparencyIndex >= 0) {
                $transparencyColor    = imagecolorsforindex($image_source, $transparencyIndex);
            }

            $transparencyIndex    = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
            imagefill($new_image, 0, 0, $transparencyIndex);
            imagecolortransparent($new_image, $transparencyIndex);
    } */


	private function resizeImage() {
		//imagedestroy($this->img);

		$this->img = imagecreatetruecolor($this->iImageWidth, $this->iImageHeight);

		//http://www.klamm.de/forum/showthread.php?t=175931
		// Füllt das Bild mit der Farbe "transparent" (standard schwarz)
		$transparent = imagecolorallocatealpha($this->img, 0, 0, 0, 127);
		imagefill($this->img, 0, 0, $transparent);

		//$this->setTransparency($this->img, $this->source_img);

		imagecopyresampled($this->img, $this->source_img, 0, 0, 0, 0, $this->iImageWidth, $this->iImageHeight, $this->aSourceImage['width'], $this->aSourceImage['height']);

		//imagecopyresized($this->img, $this->source_img, 0, 0, 0, 0, $this->iImageWidth, $this->iImageHeight, $this->iSourceImageWidth, $this->iSourceImageHeight);

		//if($oImageBeyond) {

		/*var_dump($oImageBeyond->getWidth());
		var_dump($oImageBeyond->getHeight());
		var_dump($oImageBeyond->getSourceWidth());
		var_dump($oImageBeyond->getSourceHeight());*/

		//var_dump($oImageBeyond);

		/*$oImage3 = imagecreatetruecolor($this->iMaxImageWidth, $this->iMaxImageHeight);
		imagealphablending($oImage3, false);
		imagesavealpha($oImage3, true);

			imagecopy($oImage3, $oImage, 0, 0, 0, 0, $this->iMaxImageWidth, $this->iMaxImageHeight);

			imagecopy($oImage3, $oImageBeyond->image, 0, 0, 0, 0, $oImageBeyond->getWidth(), $oImageBeyond->getHeight());
			*/
		//header("Content-type: image/png");
		//imagepng($oImage3);
		//exit;

		//var_dump($oImageBeyond);
		//var_dump($oImageSource);
		//var_dump($oImage);

		//	$asdf = imagecolorallocate($oImage, 46, 34, 84);
		//	imagefill  ( $oImage  ,  5  ,  5  ,  $asdf  );
		//$res = imagecopyresampled($oImage, $oImageBeyond->image, 0, 0, 0, 0, $oImageBeyond->getWidth(), $oImageBeyond->getHeight(), $oImageBeyond->getWidth(), $oImageBeyond->getHeight());
		//	}
	}

	public function renderOverlay() {
		//imagealphablending($this->overlay, false);
		//imageSaveAlpha($this->overlay, true);
		/*header("Content-type: image/png");
		imagepng($this->overlay);
		exit;*/
		//die('overlay');
		imageSaveAlpha($this->img, true);

		//$overlay = $this->overlay->__get('img');

		//var_dump($this->img);
		//var_dump($overlay);

		//$this->setTransparency($this->img, $this->overlay);

		//var_dump($this->overlay->getWidth());
		//var_dump($this->overlay->getHeight());

		//var_dump($this->overlay->__get('img'));
		//header("Content-type: image/png");
		//imagepng($this->overlay->__get('img'));
		//exit;

		imagecopy($this->img, $this->overlay, 0, 0, 0, 0, 150, 150); // $this->overlay->getWidth(), $this->overlay->getHeight);
	}

	public function saveImage() {
		imagepng($this->img, $this->path);
	}





	/*public function getGD()
	{
		$this->loadImage();

		if($this->bSourceImageTooBig) {
			$this->resizeImage();
		}

		if($this->overlay) {
			$this->renderOverlay();
		}

		return $this->img;
	}*/

	public function getWidth() {
		return $this->iImageWidth;
	}

	public function getHeight() {
		return $this->iImageHeight;
	}

	public function getSourceWidth() {
		return $this->aSourceImage['width'];
	}

	public function getSourceHeight() {
		return $this->aSourceImage['height'];
	}

	public function __get($strKey) {
		//var_dump($strKey);

		switch($strKey) {
			case 'url':
			case 'tag':
				// datei abspeichern / url/link returnen

				if($this->aSourceImage['resize'] || $this->overlay) {
					$this->loadImage();
					$this->resizeImage();

					if($this->overlay) {
						$this->renderOverlay();
					}

					$this->saveImage();
				} else {
					$this->uploadImage();
				}

				/*if($this->bSourceImageTooBig) {
					$this->loadImage();
					$this->resizeImage();
					$this->saveImage();
				} else {
					$this->uploadImage();
				}*/

				return $this->$strKey;
				break;
			case 'img':
				// datei einlesen / img obj returnen
				//echo('img WANT');
				$this->loadImage();
				//var_dump($this->img);
				if($this->aSourceImage['resize']) {
					$this->resizeImage();
				}

				if($this->overlay) {
					$this->renderOverlay();
				}

				return $this->img;
				break;
		}

		/*switch($strKey) {
			case 'url':
				if(!$this->image)
					imagepng($this->image, $this->url);
				return $this->url;
				break;
			case 'tag':
				if(!$this->image)
					imagepng($this->image, $this->url);
				return $this->tag;
				break;
				// datei abspeichern / link returnen
				break;
			case 'image':
				// datei einlesen / img obj returnen

				var_dump($this->image);

				return $this->image;
				break;
		}*/
	}

}

?>