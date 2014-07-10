<?php

/**
 * OrmModel
 * Murrmurr framework
 *
 * Factory for Idiorm&Paris Models
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 29.06.2014
 * @version 1.0
 */

require_once 'framework/lib/IdiormAndParis/idiorm.php';
require_once 'framework/lib/IdiormAndParis/paris.php';

class OrmModel extends BaseModel
{
	private static $instance;

	public function __construct($strParams) {
		if(!self::$instance) {
			ORM::configure('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DATABASE);
			ORM::configure('username', MYSQL_USER);
			ORM::configure('password', MYSQL_PSSWD);
			ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			ORM::configure('return_result_sets', true);
			if(_DEBUG) {
				ORM::configure('logging', true);
			}
			self::$instance = $this;
		} else {
			return self::$instance;
		}
		//Model::$auto_prefix_models = '\\ParisModels\\';
	}

	public static function get($table) {
		if(!self::$instance) {
			self::$instance = new self('');
		}
		// TODO: whitelist aus defined tables
		$factoredModel = Model::factory($table);

		return $factoredModel;
	}

}

?>