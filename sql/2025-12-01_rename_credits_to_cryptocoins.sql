ALTER TABLE `servers` CHANGE `credits` `cryptocoins` int(11) DEFAULT NULL;
ALTER TABLE `transfers` CHANGE `credits` `cryptocoins` bigint(11) DEFAULT '0';
