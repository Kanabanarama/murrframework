<?php

/**
 * FlagViewhelper
 * Murrmurr framework
 *
 * viewhelper for rendering flag symbols from text
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 05.09.2014
 * @version 1.0
 */

class FlagViewhelper extends BaseViewhelper {

	private $langCodeMap = array(
		'Deutsch'	=> 'de',
		'German'	=> 'de',
		'English'	=> 'us',
		'Englisch'	=> 'us',
		'Japanisch'	=> 'jp',
		'Japanese'	=> 'jp',
		'Russisch'	=> 'ru',
		'Russian'	=> 'ru',
		'Französisch'	=> 'fr',
	);

	public function render($strLangCodeOrName, $strFlagIconLocation) {
		if(strlen($strLangCodeOrName) === 2) {
			$flagImage = $strLangCodeOrName.'.png';
		} else {
			$langCode = (isset($this->langCodeMap[$strLangCodeOrName])) ? $this->langCodeMap[$strLangCodeOrName] : 'none';
			$flagImage = $langCode.'.png';
		}

		if(!$strFlagIconLocation) {
			$strFlagIconLocation = '/'.BASE_DIR.str_replace(ROOT_DIR, '', STATIC_DIR).'gfx/flags/';
		}
		$flagImageUrl = $strFlagIconLocation.$flagImage;

		return $flagImageUrl;
	}
}

?>