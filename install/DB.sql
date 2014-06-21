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
  `username` char(50) NOT NULL,
  `password` char(50) NOT NULL,
  `active` tinyint(1) default '0',
  `privileges` tinyint(2) default '-1',
  `session` char(50) default NULL,
  `ip_addr` text NOT NULL,
  `time` datetime default NULL,
  `firstname` char(50) default NULL,
  `lastname` char(50) default NULL,
  `email` char(150) NOT NULL default '',
  `registration` timestamp NULL default NULL,
  `lastlogin` timestamp NULL default NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT IGNORE INTO `d00eea23`.`user` (
`uid` ,
`username` ,
`password` ,
`active` ,
`privileges` ,
`session` ,
`ip_addr` ,
`time` ,
`firstname` ,
`lastname` ,
`email` ,
`registration`,
`lastlogin`
)
VALUES (
NULL ,  'admin',  '5f4dcc3b5aa765d61d8327deb882cf99',  '1',  '2', NULL ,  '', NULL ,  'Administrator',  '',  '', NOW( ), NOW( )
);

--
-- Table structure for table `profile`
--

CREATE TABLE IF NOT EXISTS `profile` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `description` text NOT NULL,
  `image_profile` text NOT NULL,
  `image_forum` text NOT NULL,
  `country` int(11) NOT NULL,
  `gender` int(11) NOT NULL,
  `birthday` date NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `user_relation_profile` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `mm_foreign_user` int(11) unsigned NOT NULL,
  `mm_foreign_profile` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `title` char(50) NOT NULL,
  `content` char(50) NOT NULL,
  `userlevel` int(11) unsigned NULL,
  `parent_user` int(11) unsigned NOT NULL,
  `foreign_tag` VARCHAR( 255 ) DEFAULT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `tag` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `tag` char(50) NOT NULL,
  `parent_content` VARCHAR( 255 ) NOT NULL DEFAULT  '',
  `count` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `content_relation_tag` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `mm_foreign_content` int(11) unsigned NOT NULL,
  `mm_foreign_tag` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `news` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `title` char(50) NOT NULL,
  `content` char(50) NOT NULL,
  `userlevel` int(11) unsigned NULL,
  `parent_user` int(11) unsigned NOT NULL,
  `publication_date` VARCHAR( 255 ) DEFAULT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;