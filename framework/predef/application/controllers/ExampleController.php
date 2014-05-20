<?php

/*
* Beispiel Controller
* @author René Lantzsch <kana@bookpile.de>
* @copyright Copyright (c) 2014 René Lantzsch
*/

class ExampleController extends BaseController
{
	public function process()
	{
		$page = new TemplateView('example.htm');
		$page->publish($this);
	}
}

?>