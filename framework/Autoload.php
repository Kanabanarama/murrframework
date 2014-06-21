<?php

/**
 * Autoload
 *
 * Auto loads the models, controllers and views of your application
 * depending of the url-path.
 *
 * @author René Lantzsch
 * @param string $strClassName Name of class
 * @version 1.0
 */

function __kanoa_autoload($strClassName) {
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

spl_autoload_register('__kanoa_autoload', true, true);

?>