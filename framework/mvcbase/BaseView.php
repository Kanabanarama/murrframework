<?php

/**
 * BaseView
 * Murrmurr framework
 *
 * Defines the abstract base class for every View
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 30.01.2010
 * @version 1.1.0
 */

abstract class BaseView
{
	abstract public function fetch();
	abstract public function render();
}

?>