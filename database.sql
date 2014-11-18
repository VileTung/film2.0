-- Adminer 4.1.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `data`;
CREATE TABLE `data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `imdb` int(10) unsigned NOT NULL,
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


-- 2014-11-18 14:35:04