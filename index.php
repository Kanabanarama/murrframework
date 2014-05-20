<?php

/**
 * index.php
 *
 * <René Lantzsch 23.01.2009> Erstversion
 *
 * @author René Lantzsch <renelantzsch@web.de>
 * @copyright Copyright (c) René Lantzsch 23.01.2009
 * @since 23.01.2009
 * @version 0.4a
 *
 * Framework Name? Kanoa? Rush? Murrmurr?
 *
 */

/**
 * @todo part controllers to only-do and show processing (for later ajax use)
 * @todo strip everything down to widgets
 * @todo make folders for modules?
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
|
|--framework
|  |-- base
|  |   |-- model
|  |   |-- view
|  |   `-- controller
|  `-- drivers
|
|--private
|  |-- config
|  |   `-- config.php
|  |-- libs
|  |-- models
|  |-- controllers
|  |-- views
|  |   `--helpers
|  `-- install
|
`--public
   `-- templates
       |-- html
       |-- css
       |-- gfx
       `-- js
*/

?>