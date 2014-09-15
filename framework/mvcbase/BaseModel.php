<?php

/**
 * BaseModel
 * Murrmurr framework
 *
 * Defines the abstract base class for every model
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 30.01.2010
 * @version 1.1.0
 */

abstract class BaseModel
{
    private $aVars = array();

	abstract public function __construct($strParams);

	public function __get($strIndex) {
		if(isset($this->aVars[$strIndex])) {
			return $this->aVars[$strIndex];
		} else {
			return null;
		}
	}

	public function __set($strIndex, $value) {
		$this->aVars[$strIndex] = $value;
	}
}

?>