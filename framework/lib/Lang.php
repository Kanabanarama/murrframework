<?php

/**
 * Lang
 * Murrmurr framework
 *
 * fetching translations from keys
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 11.07.2014
 * @version 0
 */

class Lang
{
	protected $langDirectories;
	public function __construct() {
		$this->langDirectories = array(
			'application/lang',
			'framework/lang'
		);
		$this->loadLangFileForModule('global');
	}

	public function loadLangFileForModule($moduleName) {
		foreach($this->langDirectories as $langDir) {
			$langFile = $langDir.'/'.$moduleName.'.xml';
			if(is_file($langFile)) {
				$langXmlContent = file_get_contents($langFile);
				var_dump($langXmlContent);
			}
		}
	}

	public function l($key) {

	}
}

?>