<?php

// Grundeinstellungen

define('_UTF8', true);
define('_DEBUG', true);
define('_LOGFILE', false);

define('_DBDRIVER', 'DB_MYSQLI');
define('_MAILER', 'PHPMailer');
define('_MAILER_USE_SMTP', false);

// mySQL Zugang

define('MYSQL_HOST',     'localhost');
define('MYSQL_USER',     'root');
define('MYSQL_PSSWD',    '');
define('MYSQL_DATABASE', 'murrmurr');

// Tabellen

define('TABLE_PREFIX', '');

define('TBL_USER',					TABLE_PREFIX.'user');
define('TBL_CONTENT',				TABLE_PREFIX.'content');
define('TBL_TAG',					TABLE_PREFIX.'tag');
define('TBL_CONTENT_RELATION_TAG',	TABLE_PREFIX.'content_relation_tag');

?>