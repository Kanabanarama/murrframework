<?php

class Configurator
{
	public function __construct() {}

	public static function loadConfiguration() {
		// Load config from application if provided or fallback to standard setup
		if(is_file(ROOT_DIR.'application/config/config.php')) {
			require_once(ROOT_DIR.'application/config/config.php');
		} else {
			require_once(__DIR__ . '/predef/config/config.php');
		}
	}
}

?>