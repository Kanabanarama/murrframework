<?php

/**
 * BaseViewhelper
 *
 * Defines the abstract base class for every Viewhelper
 *
 * @author René Lantzsch <renelantsch@web.de>
 */

abstract class BaseViewhelper
{
	abstract public function render($strTagContent, $aAttributes);

	public function secureContent($strContent) {
		$content = htmlspecialchars($strContent);

		return $content;
	}
}

?>