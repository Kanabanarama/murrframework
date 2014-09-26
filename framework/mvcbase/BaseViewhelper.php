<?php

/**
 * BaseViewhelper
 * Murrmurr framework
 *
 * Defines the abstract base class for every Viewhelper
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 30.01.2010
 * @version 1.1.0
 */

abstract class BaseViewhelper
{
	abstract public function render($strTagContent, $aAttributes, $mData);

	public function escapeContent($strContent) {
		$content = htmlspecialchars($strContent);

		return $content;
	}

	public function stripContent($strContent) {
		$content = preg_replace('#[[\/\!]*?[^\[\]]*?]#si', '', $strContent);
		$content = strip_tags($content);

		return $content;
	}
}

?>