--
-- Table structure for table `#__jg_media_files`
--

CREATE TABLE IF NOT EXISTS `#__jg_media_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `type` varchar(250) NOT NULL,
  `path` varchar(250) COLLATE utf8mb4_bin NOT NULL,
  `state` tinyint(1) NOT NULL,
  `source` varchar(250) NOT NULL,
  `original_filename` varchar(250) COLLATE utf8mb4_bin NOT NULL,
  `size` int(11) NOT NULL,
  `storage` varchar(250) NOT NULL,
  `created_by` int(11) NOT NULL,
  `access` tinyint(1) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

--
-- Table structure for table `#__media_files_xref`
--

CREATE TABLE IF NOT EXISTS `#__media_files_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `client` varchar(250) NOT NULL,
  `is_gallery` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

ALTER TABLE  `#__jg_campaigns` ADD vendor_id INT AFTER id;

--
-- Update Menu Item from campaign creation
--

UPDATE `#__menu` SET link = 'index.php?option=com_jgive&view=campaignform&layout=default' where link = 'index.php?option=com_jgive&view=campaign&layout=create';

--
-- Update Menu Item from single campaign
--

UPDATE `#__menu` SET link = REPLACE(link, 'index.php?option=com_jgive&view=campaign&layout=single', 'index.php?option=com_jgive&view=campaign&layout=default') where link like 'index.php?option=com_jgive&view=campaign&layout=single%'
