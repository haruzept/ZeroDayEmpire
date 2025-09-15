ALTER TABLE servers CHANGE `credits` `cc` int(11) DEFAULT NULL;
ALTER TABLE transfers CHANGE `credits` `cc` bigint(11) DEFAULT '0';
