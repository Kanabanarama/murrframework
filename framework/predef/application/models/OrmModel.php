<?php

/**
 * @author René Lantzsch <renelantsch@web.de>
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
			self::$instance = $this;
		} else {
			return self::$instance;
		}

		//Model::$auto_prefix_models = '\\ParisModels\\';

		/*$result = Model::factory('user')
			->where('username', 'kanabanarama')
			->left_outer_join('booklist', 'user.uid = booklist.parent_user')
			->left_outer_join('booklist_entry', 'booklist.uid = booklist_entry.parent_booklist')
			->left_outer_join('booktitle', 'booklist_entry.booktitle = booktitle.uid')
			->left_outer_join('author', '')
			->find_many();*/

		//var_dump($result[0]->title);
	}

	public static function get($table) {
		if(!self::$instance) {
			self::$instance = new self('');
		}
		$factoredModel = Model::factory($table);

		return $factoredModel;
	}
}

?>