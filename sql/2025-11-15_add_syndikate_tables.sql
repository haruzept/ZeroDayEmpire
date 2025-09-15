-- Migration: Add syndikate tables
-- Target: MariaDB 10.x

CREATE TABLE IF NOT EXISTS `syndikate` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `code` varchar(16) DEFAULT NULL,
  `events` text,
  `tax` int(11) NOT NULL DEFAULT '1',
  `money` bigint(20) NOT NULL DEFAULT '0',
  `infotext` text,
  `points` mediumint(9) DEFAULT NULL,
  `logofile` tinytext,
  `homepage` tinytext,
  `box1` varchar(50) DEFAULT 'Wichtig',
  `box2` varchar(50) DEFAULT 'Allgemein',
  `box3` varchar(50) DEFAULT 'Alte Beiträge',
  `acceptnew` char(3) DEFAULT 'yes',
  `rank` smallint(6) DEFAULT '0',
  `notice` text,
  `srate_total_cnt` int(11) NOT NULL DEFAULT '0',
  `srate_success_cnt` int(11) DEFAULT '0',
  `srate_noticed_cnt` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rank_syndikate` (
  `platz` smallint(6) NOT NULL AUTO_INCREMENT,
  `syndikat` smallint(6) DEFAULT '0',
  `members` tinyint(4) DEFAULT '0',
  `points` int(11) DEFAULT '0',
  `av_points` float DEFAULT '0',
  `pcs` mediumint(9) DEFAULT '0',
  `av_pcs` float DEFAULT '0',
  `success_rate` float DEFAULT '0',
  PRIMARY KEY (`platz`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
