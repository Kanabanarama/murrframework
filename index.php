<?php

/**
 * index
 * Murrmurr framework
 *
 * loads bootstrap and starts the router
 *
 * @author René Lantzsch <kana@bookpile.net>
 *
 * @author René Lantzsch <renelantzsch@web.de>
 * @copyright Copyright (c) René Lantzsch 23.01.2009
 * @since 23.01.2009
 * @version 1.0
 */

require_once 'framework/Bootstrap.php';

Router::start();

/*
::POSSIBLE URL FORMATS::

bookpile.net/
bookpile.net/index/
bookpile.net/profile/
bookpile.net/profile/Kana/
bookpile.net/book/45-456762023/
bookpile.net/profile/Kana/edit/

::FILE STRUCTURE::
/
|-- install
|   `-- install scripts
|
|--framework
|  |
|  |-- framework specific classes
|  |
|  |-- mvcbase
|  |   |-- base model class
|  |   |-- base controller class
|  |   |-- base view class
|  |   `-- base viewhelper class
|  |
|  |-- predef
|  |   |-- config
|  |   |--application
|  |   |  |-- models
|  |   |  |-- controllers
|  |   |  |-- views
|  |   |  `-- viewhelper
|  |   `-- templates
|  |
|  |-- lib
|  |
|  `-- drivers
|
`--application
   |
   |-- config
   |   |-- config file
   |   `-- table definition file
   |
   |-- libs
   |
   |-- models
   |-- controllers
   |-- views
   |-- viewhelpers
   |
   |-- templates
   `-- uploads
*/

?>