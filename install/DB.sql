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
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `username` char(50) NOT NULL,
  `password` char(50) NOT NULL,
  `active` tinyint(1) default '0',
  `privileges` tinyint(2) default '-1',
  `session` char(50) default NULL,
  `ip_addr` text NOT NULL,
  `firstname` char(50) default NULL,
  `lastname` char(50) default NULL,
  `email` char(150) NOT NULL default '',
  `lastlogin` datetime default NULL,
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
`active` ,
`privileges` ,
`session` ,
`ip_addr` ,
`firstname` ,
`lastname` ,
`email` ,
`lastlogin`
)
VALUES (
NULL , NOW( ) , NOW( ) , 'admin',  '5f4dcc3b5aa765d61d8327deb882cf99',  '1',  '2', NULL ,  '',  'Administrator',  '',  '', NOW( )
);

--
-- Table structure for table `profile`
--

CREATE TABLE IF NOT EXISTS `profile` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_user` int(11) unsigned NOT NULL,
  `description` text NOT NULL,
  `image_profile` text NOT NULL,
  `image_forum` text NOT NULL,
  `country` int(11) NOT NULL,
  `gender` int(11) NOT NULL,
  `birthday` date NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `title` char(50) NOT NULL,
  `content` char(50) NOT NULL,
  `userlevel` int(11) unsigned NULL,
  `parent_user` int(11) unsigned NOT NULL,
  `foreign_tag` VARCHAR( 255 ) DEFAULT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `tag` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tag` char(50) NOT NULL,
  `parent_content` VARCHAR( 255 ) NOT NULL DEFAULT  '',
  `count` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `content_relation_tag` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mm_foreign_content` int(11) unsigned NOT NULL,
  `mm_foreign_tag` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `news` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `title` char(50) NOT NULL,
  `content` char(50) NOT NULL,
  `userlevel` int(11) unsigned NULL,
  `parent_user` int(11) unsigned NOT NULL,
  `publication_date` datetime DEFAULT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `author` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `name` text NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booktitle` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `title` text NOT NULL,
  `author` int(11) NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booktitle_translation` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_book` int(11) unsigned NOT NULL,
  `title` text NOT NULL,
  `language` int(11) unsigned NULL,
  `image` text NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `bookedition` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_book` int(11) unsigned NOT NULL,
  `language` tinytext NOT NULL,
  `format` int(11) unsigned NULL,
  `pages` mediumint(9) NOT NULL,
  `asin` varchar(10) NOT NULL,
  `isbn` varchar(10) NOT NULL,
  `isbn13` varchar(13) NOT NULL,
  `image` text NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `noduplicates` (`parent_book`,`isbn13`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booklist` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_user` int(11) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `booklist_entry` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NULL default NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_booklist` int(4) NOT NULL,
  `booktitle` int(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `progress` smallint(6) NULL,
  `score` tinyint(4) NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `noduplicates` (`parent_booklist`,`booktitle`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;