<?php

/**
 * UriViewhelper
 * Murrmurr framework
 *
 * viewhelper for manupilating urls
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 23.06.2014
 * @version 1.1.0
 */

class ParseBbCodeViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes, $mData) {
		$strText = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $strText);
		$strText = preg_replace("/\[i\](.*)\[\/i\]/Usi", "<i>\\1</i>", $strText);
		$strText = preg_replace("/\[u\](.*)\[\/u\]/Usi", "<u>\\1</u>", $strText);
		$strText = preg_replace("/\[color=(.*)\](.*)\[\/color\]/Usi", "<font color=\"\\1\">\\2</font>", $strText);
		$strText = preg_replace("/\[size=(.*)\](.*)\[\/size\]/Usi", "<font size=\"\\1\">\\2</font>", $strText);
		$strText = preg_replace("/\[email=(.*)\](.*)\[\/email\]/Usi", "<a href=\"mailto:\\1\">\\2</a>", $strText);
		$strText = preg_replace("/\[img\](.*)\[\/img\]/Usi", '<img src="\\1">', $strText);

		return stripslashes(nl2br($strText));
	}

}

?>