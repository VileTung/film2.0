-- Adminer 4.1.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `data`;
CREATE TABLE `data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `imdb` varchar(10) NOT NULL,
  `hash` varchar(40) NOT NULL,
  `size` int(20) unsigned NOT NULL,
  `quality` varchar(50) NOT NULL,
  `retriever` varchar(50) NOT NULL,
  `reliability` varchar(250) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `genres`;
CREATE TABLE `genres` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `imdb` varchar(10) NOT NULL,
  `genre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `imdb`;
CREATE TABLE `imdb` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `imdb` varchar(10) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `runtime` int(11) unsigned NOT NULL,
  `rating` float unsigned NOT NULL,
  `release` date NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imdb` (`imdb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `process`;
CREATE TABLE `process` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `wait` varchar(250) NOT NULL,
  `process` varchar(250) NOT NULL,
  `repeat` enum('true','false') NOT NULL DEFAULT 'false',
  `flow` enum('hour','day','week','month') NOT NULL DEFAULT 'week',
  `start` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `process` varchar(250) NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `sessionId` int(10) unsigned NOT NULL,
  `progress` float unsigned NOT NULL,
  `state` varchar(10) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sessionId` (`sessionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`key`, `value`) VALUES
('osCount',	'9'),
('osDateTime',	'1420801072'),
('osEnabled',	'true'),
('pLastUpdate',	'1420814063'),
('pPID',	''),
('pSessionId',	'58942');

DROP TABLE IF EXISTS `subtitle`;
CREATE TABLE `subtitle` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `imdb` varchar(10) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `language` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `trackers`;
CREATE TABLE `trackers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(40) NOT NULL,
  `tracker` varchar(250) NOT NULL,
  `leechers` int(5) unsigned NOT NULL,
  `seeders` int(5) unsigned NOT NULL,
  `update` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2015-01-13 15:28:10
