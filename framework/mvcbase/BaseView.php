<?php

/**
 * BaseView
 *
 * Defines the abstract base class for every View
 *
 * @author René Lantzsch <renelantsch@web.de>
 */

abstract class BaseView
{
	abstract public function fetch();
	abstract public function render();
}

?>