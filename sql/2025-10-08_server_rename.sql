-- Rename pcs table and related references to servers

-- Rename main table
RENAME TABLE `pcs` TO `servers`;

-- Update columns referencing pcs
ALTER TABLE `attacks`
  CHANGE `from_pc` `from_server` SMALLINT(6) DEFAULT '0',
  CHANGE `to_pc` `to_server` SMALLINT(6) DEFAULT '0';

ALTER TABLE `da_participants`
  CHANGE `pc` `server` SMALLINT(6) NOT NULL DEFAULT '0';

ALTER TABLE `rank_syndikate`
  CHANGE `pcs` `servers` MEDIUMINT(9) DEFAULT '0',
  CHANGE `av_pcs` `av_servers` FLOAT DEFAULT '0';

ALTER TABLE `upgrades`
  CHANGE `pc` `server` SMALLINT(6) NOT NULL DEFAULT '0';

ALTER TABLE `users`
  CHANGE `pcs` `servers` TEXT,
  CHANGE `pcview_ext` `serverview_ext` ENUM('yes','no') NOT NULL DEFAULT 'yes';

-- Update log type enum and existing values
ALTER TABLE `logs`
  MODIFY `type` ENUM('other','worm_clmoney','worm_blockpc','worm_blockserver','worm_pcsendmoney','worm_serversendmoney','delsyndikat','deluser','lockuser','badlogin','chclinfo') NOT NULL DEFAULT 'other';

UPDATE `logs` SET `type`='worm_blockserver' WHERE `type`='worm_blockpc';
UPDATE `logs` SET `type`='worm_serversendmoney' WHERE `type`='worm_pcsendmoney';

ALTER TABLE `logs`
  MODIFY `type` ENUM('other','worm_clmoney','worm_blockserver','worm_serversendmoney','delsyndikat','deluser','lockuser','badlogin','chclinfo') NOT NULL DEFAULT 'other';
