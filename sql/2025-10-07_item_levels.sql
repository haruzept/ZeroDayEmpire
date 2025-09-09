-- Migration: move item upgrade data to database
-- Target: MariaDB 10.x

CREATE TABLE IF NOT EXISTS `item_levels` (
  `item` varchar(16) NOT NULL,
  `level` tinyint unsigned NOT NULL,
  `value` int unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `next_cost` int unsigned NOT NULL DEFAULT 0,
  `next_duration` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`item`,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `item_formulas` (
  `item` varchar(16) NOT NULL,
  `offset` decimal(4,2) NOT NULL DEFAULT 0.50,
  `cost_factor` decimal(10,2) NOT NULL,
  `duration_factor` decimal(10,2) NOT NULL,
  `cost_multiplier` decimal(10,2) NOT NULL DEFAULT 4.00,
  `max_level` decimal(5,2) NOT NULL DEFAULT 10.00,
  PRIMARY KEY (`item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

REPLACE INTO `item_levels` (`item`,`level`,`value`,`name`,`next_cost`,`next_duration`) VALUES
  ('cpu',0,1200,'Single-Core 1200 Ghz',60,20),
  ('cpu',1,1400,'Single-Core 1400 Ghz',80,25),
  ('cpu',2,1600,'Single-Core 1600 Ghz',90,30),
  ('cpu',3,1800,'Single-Core 1800 Ghz',110,35),
  ('cpu',4,2000,'Dual-Core 2x2000 Ghz',120,40),
  ('cpu',5,2200,'Dual-Core 2x2200 Ghz',140,45),
  ('cpu',6,2400,'Dual-Core 2x2400 Ghz',150,50),
  ('cpu',7,2600,'Dual-Core 2x2600 Ghz',255,55),
  ('cpu',8,2800,'Dual-Core 2x2800 Ghz',300,55),
  ('cpu',9,3000,'Dual-Core 2x3000 Ghz',512,60),
  ('cpu',10,3200,'Dual-Core 4x3200 Ghz',768,90),
  ('cpu',11,3400,'Quad-Core 4x3400 Ghz',1150,120),
  ('cpu',12,3600,'Quad-Core 4x3600 Ghz',1730,150),
  ('cpu',13,3800,'Quad-Core 4x3800 Ghz',2590,180),
  ('cpu',14,4000,'Quad-Core 4x4000 Ghz',3890,210),
  ('cpu',15,4200,'Quad-Core 4x4200 Ghz',5800,240),
  ('cpu',16,4400,'Quad-Core 4x4400 Ghz',8500,300),
  ('cpu',17,4600,'Quad-Core 4x4600 Ghz',12000,360),
  ('cpu',18,4800,'Quad-Core 4x4800 Ghz',18000,420),
  ('cpu',19,5000,'Quad-Core 4x5000 Ghz',25000,460),
  ('cpu',20,5200,'Quad-Core 4x5200 Ghz',50000,580),
  ('cpu',21,5400,'Quad-Core 4x5400 Ghz',0,0),
  ('ram',0,1024,NULL,200,30),
  ('ram',1,2048,NULL,300,45),
  ('ram',2,3072,NULL,500,60),
  ('ram',3,4096,NULL,800,70),
  ('ram',4,5120,NULL,1000,90),
  ('ram',5,6144,NULL,1200,120),
  ('ram',6,7168,NULL,3000,150),
  ('ram',7,8192,NULL,4000,180),
  ('ram',8,9216,NULL,10000,210),
  ('ram',9,10240,NULL,0,0);

REPLACE INTO `item_formulas` (`item`,`offset`,`cost_factor`,`duration_factor`,`cost_multiplier`,`max_level`) VALUES
  ('mm',0.50,51,10,4,10.0),
  ('bb',0.50,45,11,4,10.0),
  ('lan',0.50,150,25,4,10.0),
  ('sdk',0.50,100,15,4,5.0),
  ('fw',0.50,49,5,4,10.0),
  ('av',0.15,50,6,4,10.0),
  ('mk',0.50,100,16,4,10.0),
  ('ips',0.50,33,8,4,10.0),
  ('ids',0.50,44,7,4,10.0),
  ('rh',0.50,400,10,4,10.0),
  ('trojan',0.50,39,8,4,5.0);
