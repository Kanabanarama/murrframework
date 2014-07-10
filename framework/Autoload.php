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

function __murrmurr_autoload($strClassName) {
	$aAutoloadDirectories = array(CONTROLLER_DIR, VIEW_DIR, MODEL_DIR, PREDEF_CONTROLLER_DIR, PREDEF_VIEW_DIR, PREDEF_VIEWHELPER_DIR, PREDEF_MODEL_DIR, LIB_DIR);
	$strFile = ucfirst($strClassName) . '.php';

	/* Die MVC Dirs durchlaufen  */
	foreach($aAutoloadDirectories as $strDir) {
		$strPath = $strDir . $strFile;
		if(file_exists($strPath)) {
			require($strPath);
			/* 404 wenn nach Einbindung die Klasse immer noch nicht existiert */
			if(!class_exists($strClassName, false)) {
				$strErr = 'Class not found: [' . $strClassName . ']';
				break;
			}
			return;
		}
	}

	if(defined('_DEBUG') && _DEBUG) {
		$strErr = isset($strErr) ? $strErr : 'Class File not found: [' . $strFile . ']';
		throw new Exception($strErr, 3);
	} else {
		Router::_404();
	}
}

spl_autoload_register('__murrmurr_autoload', true, true);

?>