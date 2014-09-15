<?php

/**
 * Autoload
 * Murrmurr framework
 *
 * Auto loads the models, controllers and views
 * and other classes of the application
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 1.0
 */

class Autoloader
{
	public static function __murrmurr_autoload($strClassName) {
		$aAutoloadDirectories = array(CONTROLLER_DIR, VIEW_DIR, MODEL_DIR, PREDEF_CONTROLLER_DIR, PREDEF_VIEW_DIR, PREDEF_VIEWHELPER_DIR, PREDEF_MODEL_DIR, LIB_DIR);
		$strFile = ucfirst($strClassName) . '.php';

		/* Die MVC Dirs durchlaufen  */
		foreach($aAutoloadDirectories as $strDir) {
			$strPath = $strDir . $strFile;
			if(file_exists($strPath)) {
				include($strPath);
				/* 404 wenn nach Einbindung die Klasse immer noch nicht existiert */
				if(!class_exists($strClassName, false)) {
					$strErr = 'Class not found: [' . $strClassName . ']';
					break;
				}
				return;
			}
		}

        // messy PHP 5.2 fix: Search for default template, before 5.3 you can't recover from an class_not_found exception.
        if ((version_compare(PHP_VERSION, '5.3.0') < 0) && !class_exists($strClassName, false)) {
            $templateName = str_replace('Controller', '', $strClassName);
            $controller = new DefaultTemplateDeliveryController(null, null, null, $templateName);
            $controller->getView()->render();
            exit;
        } else if(defined('_DEBUG') && _DEBUG) {
			//if(version_compare(phpversion(), '5.3', '>=')) {
				$strErr = isset($strErr) ? $strErr : 'Class File not found: [' . $strFile . ']';
				throw new Exception($strErr, 3);
			//}
		} else {
			Router::_404();
		}
	}
}

// autoloader < PHP 5.1.2
if (version_compare(PHP_VERSION, '5.1.2') >= 0) {
	function __autoload($strClassName) {
		Autoloader::__murrmurr_autoload($strClassName);
	}
} else {
	spl_autoload_register('Autoloader::__murrmurr_autoload');
}

?>