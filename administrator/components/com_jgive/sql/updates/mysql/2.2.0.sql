--
-- Add ordering column in the campaigns table
--
ALTER TABLE  `#__jg_campaigns` ADD ordering INT(11) NOT NULL DEFAULT '0' AFTER meta_desc;

--
-- Table structure for table `#__jg_reports`
--
CREATE TABLE IF NOT EXISTS `#__jg_reports` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(5) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT 'published =1, unpublished = 0',
  `created_by` int(10) unsigned NOT NULL COMMENT 'created by user id',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

RENAME TABLE #__media_files_xref TO #__tj_media_files_xref;
RENAME TABLE #__jg_media_files TO #__tj_media_files;

DROP TABLE #__jg_updates;
--
-- Update param name for campaign donors count
--
UPDATE `#__menu` SET params = REPLACE(params, 'donor_count', 'campaignDonorsCount') where link = 'index.php?option=com_jgive&view=campaigns&layout=all';

--
-- Update my donations menu link
--
UPDATE `#__menu` SET link = 'index.php?option=com_jgive&view=donations&layout=default' where link = 'index.php?option=com_jgive&view=donations&layout=my';
