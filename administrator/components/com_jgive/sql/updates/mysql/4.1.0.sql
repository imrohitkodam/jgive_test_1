--
-- Table structure for table `#__jg_donors`
--
ALTER TABLE `#__jg_donors` ADD `pannumber` TEXT DEFAULT NULL;
ALTER TABLE `#__jg_campaigns` ADD `lifetimecamp` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jg_campaigns` ADD `email_sent` TINYINT(1) NOT NULL DEFAULT 0;
