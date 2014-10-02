-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 03. Oktober 2010 um 16:11
-- Server Version: 5.0.51
-- PHP-Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `username` char(50) NOT NULL,
  `password` text NOT NULL,
  `origin` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `privileges` tinyint(2) DEFAULT '-1',
  `session` char(50) DEFAULT NULL,
  `ip_addr` text NOT NULL,
  `firstname` text DEFAULT NULL,
  `lastname` text DEFAULT NULL,
  `email` char(100) NOT NULL DEFAULT '',
  `language` char(2) NOT NULL DEFAULT '',
  `facebook_account` text NOT NULL DEFAULT '',
  `facebook_settings` text NOT NULL,
  `lastlogin` datetime DEFAULT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT IGNORE INTO `d00eea23`.`user` (
`uid` ,
`created` ,
`updated` ,
`username` ,
`password` ,
`origin` ,
`active` ,
`privileges` ,
`session` ,
`ip_addr` ,
`firstname` ,
`lastname` ,
`email` ,
`language` ,
`facebook_account` ,
`facebook_settings` ,
`lastlogin`
)
VALUES
(NULL , NOW( ) , NOW( ) , 'admin',  '5f4dcc3b5aa765d61d8327deb882cf99',  'install',  '1',  '2', NULL ,  '',  'Administrator',  '',  'kana@bookpile.net', 'en', NULL, NULL, NOW( )),
(NULL, '2014-06-26 02:11:26', '2014-06-26 02:11:26', 'Kana', '75f03325b6915e6b8cf928874e354496', 'install', '1', '1', '70gkqlm0pud8a2shde3bb9n1r2', '', '', '', 'kanabanarama@googlemail.com', 'de', 'kana-noir@hotmail.de', NULL, '2014-07-13 20:35:22');

--
-- Table structure for table `profile`
--

CREATE TABLE IF NOT EXISTS `profile` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_user` int(11) unsigned NOT NULL,
  `description` text NOT NULL,
  `image_profile` text NOT NULL,
  `image_forum` text NOT NULL,
  `country` int(11) NOT NULL,
  `gender` int(11) NOT NULL,
  `birthday` date DEFAULT NULL,
  PRIMARY KEY (`uid`),
  FOREIGN KEY(parent_user) REFERENCES user(uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `d00eea23`.`profile`
(`uid`, `updated`, `created`, `parent_user`, `description`, `image_profile`, `image_forum`, `country`, `gender`, `birthday`)
VALUES
(NULL, NOW(), NOW(), 1, '[b]42[/b]', '', '', '', 1, NOW()),
(NULL, '2014-06-26 02:11:26', '2014-06-26 02:11:26', 2, '[b]We\'re all mad here![/b]', 'uploads/images/avatars/1c9ffa9f193a3c2f27eb7cc56a9f5af3.png', '', '', 0, '1986-08-29');

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `userlevel` int(11) unsigned NULL,
  `parent_user` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `tag` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tag` text NOT NULL,
  `count` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `content_relation_tag` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mm_foreign_content` int(11) unsigned NOT NULL,
  `mm_foreign_tag` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `news` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NULL DEFAULT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `userlevel` int(11) unsigned DEFAULT NULL,
  `parent_user` int(11) unsigned NOT NULL,
  `publication_date` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `news_image` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_news` int(11) NOT NULL,
  `title` text NOT NULL,
  `alt` text NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `image_position` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `author` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `name` text NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booktitle` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `title` text NOT NULL,
  `author` int(11) NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booktitle_translation` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_booktitle` int(11) unsigned NOT NULL,
  `title` text NOT NULL,
  `language` int(11) unsigned NULL,
  `image` text NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY (`uid`),
  FOREIGN KEY(parent_booktitle) REFERENCES booktitle(uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `bookedition` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_booktitle` int(11) unsigned NOT NULL,
  `language` tinytext NOT NULL,
  `format` int(11) unsigned NULL,
  `pages` mediumint(9) NOT NULL,
  `asin` varchar(10) NOT NULL,
  `isbn` varchar(10) NOT NULL,
  `isbn13` varchar(13) NOT NULL,
  `image` text NOT NULL,
  `link` text NOT NULL,
  `fbobject` text(20) DEFAULT '',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `noduplicates` (`parent_booktitle`,`isbn13`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booklist` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_user` int(11) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booklistentry` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_booklist` int(11) NOT NULL,
  `booktitle` int(11) NOT NULL,
  `bookedition` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `progress` smallint(6) NULL,
  `score` tinyint(4) NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `noduplicates` (`parent_booklist`,`booktitle`),
  FOREIGN KEY(parent_booklist) REFERENCES booklist(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `rank` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user` int(11) unsigned NOT NULL,
  `rank` int(11) unsigned NOT NULL,
  `pages` int(11) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;