<?php

/**
 * @author René Lantzsch <renelantsch@web.de>
 */

class Processor// extends BaseController
{
	protected $viewVars = array();

	/*public $doNotLink = true;
	public $executeOnly = true;
	public $isSingleton = true;*/

	//public function process() {}

	public function getviewVars() {
		return $this->viewVars;
	}
}