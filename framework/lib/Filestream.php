<?php

class StreamDownload
{
	private $_strFileContent;
	private $_strFileName;
	private $__iContentLenght;

	public function __construct($strContent, $strFileName)
	{
		$this->_strFileContent	= $strContent;
		$this->_strFileName		= $strFileName;
		$this->_iContentLenght	= strlen($strContent);
	}

	public function __toString()
	{
		$this->send();
		return;
	}

	private function send()
	{
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: text");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=".$this->_strFileName);
		header("Content-Transfer-Encoding: binary");
		header('Content-Length: '.$this->_iContentLenght);
		echo $this->_strFileContent;
		exit;
	}
}

?>