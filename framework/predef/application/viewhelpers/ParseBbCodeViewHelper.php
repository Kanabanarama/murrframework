<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 23/06/14
 * Time: 20:47
 */

class ParseBbCodeViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes) {
		$strText = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $strText);
		$strText = preg_replace("/\[i\](.*)\[\/i\]/Usi", "<i>\\1</i>", $strText);
		$strText = preg_replace("/\[u\](.*)\[\/u\]/Usi", "<u>\\1</u>", $strText);
		$strText = preg_replace("/\[color=(.*)\](.*)\[\/color\]/Usi", "<font color=\"\\1\">\\2</font>", $strText);
		$strText = preg_replace("/\[size=(.*)\](.*)\[\/size\]/Usi", "<font size=\"\\1\">\\2</font>", $strText);
		$strText = preg_replace("/\[email=(.*)\](.*)\[\/email\]/Usi", "<a href=\"mailto:\\1\">\\2</a>", $strText);
		$strText = preg_replace("/\[img\](.*)\[\/img\]/Usi", '<img src="\\1">', $strText);

		return nl2br($strText);
	}

}

?>