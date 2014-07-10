<?php

/**
 * Processor
 * Murrmurr framework
 *
 * post processor for doing stuff after the controller has been processed
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 10.
 */

abstract class Processor
{
	protected $controller;
	protected $viewVars = array();

	public function getviewVars() {
		return $this->viewVars;
	}

	public function setController($controller) {
		$this->controller = $controller;
	}

	public function getController() {
		return $this->controller;
	}
}