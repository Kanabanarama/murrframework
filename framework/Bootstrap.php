<?php

/**
 * Bootstrap
 * Murrmurr framework
 *
 * loading and initialization of all framework relevant classes
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 1.0
 */

// TODO: contants into config manager?
$root  = str_replace(DIRECTORY_SEPARATOR, '/', substr(__DIR__, 0, strpos(__DIR__, 'framework')));
$base = str_replace(DIRECTORY_SEPARATOR, '/', substr($root, strlen($_SERVER['DOCUMENT_ROOT'])));
define('ROOT_DIR', $root);
define('BASE_DIR', $base);
define('DOMAIN', 'http://'.$_SERVER['HTTP_HOST'] . '/');

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

define('LIB_DIR',		ROOT_DIR . 'framework/lib/');

/* Configuration handling */
require_once(__DIR__ . '/Configurator.php');

Configurator::loadConfiguration();

/* Framework specific classes */
require_once(__DIR__ . '/Exception.php');
require_once(__DIR__ . '/Router.php');
require_once(__DIR__ . '/Registry.php');
require_once(__DIR__ . '/Autoload.php');

/* Base classes */
include MVCBASE_DIR . 'BaseModel.php';
include MVCBASE_DIR . 'BaseController.php';
include MVCBASE_DIR . 'BaseView.php';
include MVCBASE_DIR . 'BaseViewhelper.php';

/* DB Driver */
include(ROOT_DIR.'framework/drivers/'._DBDRIVER.'.php');
$dbInstance = call_user_func(_DBDRIVER.'::getInstance');
Registry::set('dbconnection', $dbInstance);

require_once(__DIR__ . '/Authorisation.php');
Registry::set('authorisation', Authorisation::getInstance());

?>