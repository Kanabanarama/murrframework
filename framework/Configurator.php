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

		if(constant('_UTF8') === true) {
			mb_internal_encoding('UTF-8');
			/*mb_http_output( "UTF-8" );
			iconv_set_encoding("input_encoding", "UTF-8");
			iconv_set_encoding("internal_encoding", "UTF-8");
			iconv_set_encoding("output_encoding", "UTF-8");*/
			header('Content-Type: text/html; charset=utf-8');
		}
	}
}

?>