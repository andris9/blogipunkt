-- phpMyAdmin SQL Dump
-- version 2.11.9.5
-- http://www.phpmyadmin.net
--
-- Masin: digituvastus.org
-- Tegemisaeg: 22.03.2011 kell 17:01:40
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
  `lang` varchar(10) COLLATE utf8_estonian_ci NOT NULL DEFAULT 'et_ee',
  `checked` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `queued` set('Y','N') COLLATE utf8_estonian_ci NOT NULL DEFAULT 'N',
  `lease` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `title` (`title`),
  KEY `queued` (`queued`),
  KEY `lease` (`lease`),
  KEY `feed` (`feed`),
  KEY `hub` (`hub`),
  KEY `language` (`lang`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Blogide põhitabel';

-- --------------------------------------------------------

--
-- Struktuur tabelile `cat2blog`
--

CREATE TABLE IF NOT EXISTS `cat2blog` (
  `category` int(11) NOT NULL,
  `blog` int(11) NOT NULL,
  PRIMARY KEY (`category`,`blog`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Blogi sidumine kategooriaga';

-- --------------------------------------------------------

--
-- Struktuur tabelile `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_estonian_ci NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Blogide kategooriad';

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
  `votes` int(11) NOT NULL,
  `points` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `blog` (`blog`),
  KEY `date` (`date`),
  KEY `points` (`points`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Postitused';

-- --------------------------------------------------------

--
-- Struktuur tabelile `queue`
--

CREATE TABLE IF NOT EXISTS `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) COLLATE utf8_estonian_ci NOT NULL,
  `data` text COLLATE utf8_estonian_ci NOT NULL,
  `hash` varchar(32) COLLATE utf8_estonian_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Järjekorras olevad muudatused';

-- --------------------------------------------------------

--
-- Struktuur tabelile `urls`
--

CREATE TABLE IF NOT EXISTS `urls` (
  `source` char(32) COLLATE utf8_estonian_ci NOT NULL,
  `dest` varchar(255) COLLATE utf8_estonian_ci NOT NULL,
  PRIMARY KEY (`source`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci COMMENT='Aadressi suunamised';
