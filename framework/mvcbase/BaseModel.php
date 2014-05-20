<?php

/**
 * BaseModel
 *
 * Defines the abstract base class for every Model
 *
 * @author René Lantzsch <renelantsch@web.de>
 */

abstract class BaseModel
{
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