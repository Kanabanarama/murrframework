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
	protected $langXml;
	protected $language;
	public function __construct() {
		$this->langDirectories = array(
			'application/lang',
			'framework/lang'
		);
		$this->language = 'en';
		$this->loadLangFileForModule('global');
	}

	public function loadLangFileForModule($moduleName) {
		foreach($this->langDirectories as $langDir) {
			$langFile = $langDir.'/'.$moduleName.'.xml';
			if(is_file($langFile)) {
				try {
					$this->langXml = simplexml_load_file($langFile);
					//var_dump($this->langXml);
				} catch(Exception $e) {
					throw new Exception('Lang file missing', 86);
				}
			}
		}
	}

	public function setLanguage($langCode) {
		// TODO: allowed languages..?
		if(in_array($langCode, array('en', 'de')) === true) {
			$this->language = $langCode;
		}
	}

	public function l($key) {
		$path = 'lang[@key="'.$this->language.'"]/translation[@key="header.search.text"]';
		$node = current($this->langXml->xpath($path));
		if($node) {
			$translation = (string)$node;
		} else {
			$translation = '';
		}

		return $translation;
	}

	public function lall() {
		$path = 'lang[@key="'.$this->language.'"]/translation';
		$nodes = $this->langXml->xpath($path);
		$langArray = array();
		foreach($nodes as $i => $val){
			$key = (string)$nodes[$i]->attributes()->key;
			$langArray[$key] = (string)$val;
		}

		return $langArray;
	}
}

?>