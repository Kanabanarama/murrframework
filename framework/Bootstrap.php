<?php

$root  = str_replace(DIRECTORY_SEPARATOR, '/', substr(__DIR__, 0, strpos(__DIR__, 'framework')));
define('ROOT_DIR', $root);

define('MVCBASE_DIR',		ROOT_DIR . 'framework/mvcbase/');

define('CONTROLLER_DIR',	ROOT_DIR . 'application/controllers/');
define('MODEL_DIR',			ROOT_DIR . 'application/models/');
define('VIEW_DIR',			ROOT_DIR . 'application/views/');
define('VIEWHELPER_DIR',	ROOT_DIR . 'application/viewhelpers/');

define('PREDEF_MODEL_DIR',		ROOT_DIR . 'framework/predef/application/models/');
define('PREDEF_CONTROLLER_DIR',	ROOT_DIR . 'framework/predef/application/controllers/');
define('PREDEF_VIEW_DIR',		ROOT_DIR . 'framework/predef/application/views/');
define('PREDEF_VIEWHELPER_DIR',	ROOT_DIR . 'framework/predef/application/viewhelpers/');

define('STATIC_DIR',		ROOT_DIR . 'framework/predef/templates/');
define('TEMPLATE_DIR',		ROOT_DIR . 'application/templates/');

require_once(__DIR__ . '/predef/config/config.php');
require_once(__DIR__ . '/Exception.php');
require_once(__DIR__ . '/Router.php');
require_once(__DIR__ . '/Registry.php');

/* Base classes */
include MVCBASE_DIR . 'BaseModel.php';
include MVCBASE_DIR . 'BaseController.php';
include MVCBASE_DIR . 'BaseView.php';
include MVCBASE_DIR . 'BaseViewhelper.php';

require_once(__DIR__ . '/Autoload.php');

/* DB Driver */
include(ROOT_DIR.'framework/drivers/DB_'._DBDRIVER.'.php');

$db = 'DB_'._DBDRIVER;
Registry::set('dbconnection', $db::getInstance());

?>