-- phpMyAdmin SQL Dump
-- version 2.11.9.5
-- http://www.phpmyadmin.net
--
-- Masin: digituvastus.org
-- Tegemisaeg: 20.03.2011 kell 23:27:20
-- Serveri versioon: 5.1.45
-- PHP versioon: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Andmebaas: `vhost16899s1`
--

-- --------------------------------------------------------

--
-- Struktuur tabelile `blogs`
--

CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  `feed` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  `hub` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  `meta` text COLLATE utf8_estonian_ci NOT NULL,
  `checked` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `queued` set('Y','N') COLLATE utf8_estonian_ci NOT NULL DEFAULT 'N',
  `lease` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `title` (`title`),
  KEY `queued` (`queued`),
  KEY `lease` (`lease`),
  KEY `feed` (`feed`),
  KEY `hub` (`hub`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Blogide põhitabel';

-- --------------------------------------------------------

--
-- Struktuur tabelile `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `author` varchar(50) COLLATE utf8_estonian_ci NOT NULL,
  `tags` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  `contents` text COLLATE utf8_estonian_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `blog` (`blog`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Postitused';

-- --------------------------------------------------------

--
-- Struktuur tabelile `urls`
--

CREATE TABLE IF NOT EXISTS `urls` (
  `source` char(32) COLLATE utf8_estonian_ci NOT NULL,
  `dest` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  PRIMARY KEY (`source`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Aadressi suunamised';
