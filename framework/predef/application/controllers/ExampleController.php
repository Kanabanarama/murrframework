<?php

/**
 * ExampleController
 * Murrmurr framework
 *
 * example controller that loads an example html
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 1.0
 */

class ExampleController extends BaseController
{
	public function process() {
		$page = new TemplateView('example.htm');
		$page->publish($this);
	}
}

?>