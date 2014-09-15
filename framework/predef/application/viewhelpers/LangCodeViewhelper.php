<?php

/**
 * LangCodeViewhelper
 * Murrmurr framework
 *
 * viewhelper for rendering lang codes from Language Strings
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 14.09.2014
 * @version 1.0
 */

class LangCodeViewhelper extends BaseViewhelper {

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
		$langCode = (isset($this->langCodeMap[$strLangCodeOrName])) ? $this->langCodeMap[$strLangCodeOrName] : 'none';

		return $langCode;
	}
}

?>