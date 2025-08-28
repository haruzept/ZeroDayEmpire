-- Migration: Add Research & Development (R&D) system (Schadsoftware-Linie)
-- Target: MariaDB 10.x

CREATE TABLE IF NOT EXISTS `research_tracks` (
  `track` varchar(10) NOT NULL,
  `name` varchar(80) NOT NULL,
  `max_level` tinyint unsigned NOT NULL DEFAULT 5,
  `base_cost` int unsigned NOT NULL,
  `base_time_min` int unsigned NOT NULL,
  `cost_mult` decimal(5,2) NOT NULL,
  `time_mult` decimal(5,2) NOT NULL,
  PRIMARY KEY (`track`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `research_state` (
  `pc` smallint(6) NOT NULL,
  `track` varchar(10) NOT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`pc`,`track`),
  KEY `k_track` (`track`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `research` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pc` smallint(6) NOT NULL,
  `start` int(11) NOT NULL DEFAULT 0,
  `end` int(11) NOT NULL DEFAULT 0,
  `track` varchar(10) NOT NULL,
  `target_level` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_pc_end` (`pc`,`end`),
  KEY `k_track` (`track`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `research_deps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `track` varchar(10) NOT NULL,
  `type` enum('unlock','level_gate') NOT NULL DEFAULT 'unlock',
  `gate_level` tinyint unsigned NOT NULL DEFAULT 0,
  `req_track` varchar(10) NOT NULL,
  `req_level` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_track_type` (`track`,`type`,`gate_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

REPLACE INTO `research_tracks` (`track`,`name`,`max_level`,`base_cost`,`base_time_min`,`cost_mult`,`time_mult`) VALUES
('r_ana','Öffentliche Skriptanalyse',5,100,5,1.60,1.45),
('r_poc','PoC-Verständnis & Doku',5,200,8,1.60,1.45),
('r_bauk','Modularer Code-Baukasten',5,350,12,1.60,1.45),
('r_lab','Simuliertes Schwachstellen-Labor',5,600,18,1.60,1.45),
('r_pers','Persistenz-Forschung (sim.)',5,900,25,1.60,1.45),
('r_veil','Verschleierungs-Methoden (sim.)',5,1400,35,1.60,1.45),
('r_c2','Steuerkanal-Emulation (sim.)',5,2200,50,1.60,1.45),
('r_data','Datenzugriffs-Strategien (sim.)',5,3400,70,1.60,1.45),
('r_se','Social-Engineering-Simulation (sim.)',5,5200,95,1.60,1.45),
('r_rans','Ransomware-Architektur (sim.)',5,8000,130,1.60,1.45);

-- Unlocks
INSERT INTO `research_deps` (`track`,`type`,`gate_level`,`req_track`,`req_level`) VALUES
('r_poc','unlock',0,'r_ana',2),
('r_bauk','unlock',0,'r_poc',2),
('r_lab','unlock',0,'r_bauk',2),
('r_pers','unlock',0,'r_bauk',3),
('r_pers','unlock',0,'r_lab',2),
('r_veil','unlock',0,'r_bauk',3),
('r_c2','unlock',0,'r_pers',2),
('r_c2','unlock',0,'r_veil',2),
('r_data','unlock',0,'r_lab',3),
('r_data','unlock',0,'r_pers',2),
('r_se','unlock',0,'r_poc',3),
('r_se','unlock',0,'r_data',2),
('r_rans','unlock',0,'r_pers',4),
('r_rans','unlock',0,'r_veil',3),
('r_rans','unlock',0,'r_c2',3),
('r_rans','unlock',0,'r_data',3),
('r_rans','unlock',0,'r_se',2);

-- Level gates
INSERT INTO `research_deps` (`track`,`type`,`gate_level`,`req_track`,`req_level`) VALUES
('r_pers','level_gate',4,'r_lab',3),
('r_pers','level_gate',5,'r_veil',2),
('r_veil','level_gate',4,'r_bauk',4),
('r_veil','level_gate',5,'r_c2',2),
('r_c2','level_gate',3,'r_pers',3),
('r_c2','level_gate',4,'r_veil',3),
('r_c2','level_gate',5,'r_data',3),
('r_data','level_gate',3,'r_pers',3),
('r_data','level_gate',4,'r_c2',3),
('r_data','level_gate',5,'r_se',3),
('r_se','level_gate',3,'r_data',3),
('r_se','level_gate',4,'r_c2',3),
('r_se','level_gate',5,'r_veil',4),
('r_rans','level_gate',2,'r_veil',3),
('r_rans','level_gate',3,'r_c2',3),
('r_rans','level_gate',3,'r_data',3),
('r_rans','level_gate',4,'r_pers',4),
('r_rans','level_gate',4,'r_se',3),
('r_rans','level_gate',5,'r_pers',5),
('r_rans','level_gate',5,'r_veil',4),
('r_rans','level_gate',5,'r_c2',4),
('r_rans','level_gate',5,'r_data',4),
('r_rans','level_gate',5,'r_se',4);
