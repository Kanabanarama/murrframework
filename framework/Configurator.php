<?php

class Configurator
{
	public function __construct() {}

	public static function loadConfiguration() {
		// Load config from application if provided or fallback to standard setup
		if(is_file('application/config/config.php')) {
			include('application/config/config.php');
		} else {
			include(__DIR__ . '/predef/config/config.php');
		}
	}
}

?>