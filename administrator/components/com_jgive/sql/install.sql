-- jgive
-- Copyright © 2017 - All rights reserved.
-- License: GNU/GPL
--
-- jgive table(s) definition
--
--

--
-- Table structure for table `#__jg_campaigns`
--

CREATE TABLE IF NOT EXISTS `#__jg_campaigns` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `vendor_id` int(11) NOT NULL DEFAULT 0,
  `org_ind_type` varchar(250) NOT NULL DEFAULT '',
  `creator_id` int(11) NOT NULL DEFAULT 0 COMMENT 'userid of user who created this capmpaign',
  `title` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'donation',
  `max_donors` int(11) NOT NULL DEFAULT 0,
  `minimum_amount` DECIMAL(16,5) NOT NULL DEFAULT 0 COMMENT  'minimum amount for transaction',
  `short_description` text DEFAULT NULL,
  `long_description` text DEFAULT NULL,
  `goal_amount` DECIMAL(16,5) NOT NULL DEFAULT 0,
  `group_name` varchar(250) NOT NULL DEFAULT '',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `allow_exceed` tinyint(1) NOT NULL DEFAULT 0,
  `allow_view_donations` tinyint(1) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `internal_use` text DEFAULT NULL COMMENT 'use internally',
  `featured` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Set if campaign is featured.',
  `js_groupid` int(11) NOT NULL DEFAULT 0,
  `success_status` int(1) NOT NULL DEFAULT 0 COMMENT '0 - Ongoing, 1 - Successful, -1 - Failed',
  `processed_flag` varchar(50) DEFAULT 'NA' COMMENT 'NA - NA, SP - Success Processed, RF - Refunded',
  `video_on_details_page` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If 1 then show video on campaign details page insteaded of image',
  `meta_data` text DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `amount_suggestions` text DEFAULT NULL,
  `lifetimecamp` tinyint(1) NOT NULL DEFAULT 0,
  `email_sent` tinyint(1) NOT NULL DEFAULT 0,

  PRIMARY KEY  (`id`),
  KEY `vendor_id_idx` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jg_campaigns_givebacks`
--

CREATE TABLE IF NOT EXISTS `#__jg_campaigns_givebacks` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `campaign_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table#__jg_campaigns',
  `title` varchar(250) NOT NULL DEFAULT '' COMMENT 'giveback title',
  `amount` DECIMAL(16,5) NOT NULL DEFAULT 0 COMMENT 'giveback amount',
  `description` text DEFAULT NULL COMMENT 'giveback details',
  `order` int(5) NOT NULL DEFAULT 0 COMMENT 'ordering',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(400) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `campaign_id_idx` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jg_campaigns_beneficiary_stories`
--

CREATE TABLE IF NOT EXISTS `#__jg_campaigns_beneficiary_stories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary key',
  `campaign_id` INT NOT NULL COMMENT 'FK: ID of #__jg_campaigns',
  `beneficiary_name` VARCHAR(255) NOT NULL COMMENT 'Beneficiary name',
  `beneficiary_position` VARCHAR(255) DEFAULT NULL COMMENT 'Beneficiary position',
  `story_title` VARCHAR(255) DEFAULT NULL COMMENT 'Story title',
  `story_description` TEXT NOT NULL COMMENT 'Story description',
  `image_url` VARCHAR(255) DEFAULT NULL COMMENT 'Image URL',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Created timestamp',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last updated timestamp',
  `status` TINYINT(1) DEFAULT 0 COMMENT 'Status: 1 = Active, 0 = Inactive',
  CONSTRAINT `fk_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `#__jg_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__jg_donations`
--

CREATE TABLE IF NOT EXISTS `#__jg_donations` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `campaign_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table #__jg_campaigns',
  `donor_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table #__jg_donors',
  `order_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table #__jg_orders',
  `giveback_id` int(11) NOT NULL DEFAULT 0 COMMENT 'id of jg_campaigns_givebacks',
  `annonymous_donation` tinyint(1) NOT NULL DEFAULT 0,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `recurring_frequency` varchar(100) DEFAULT NULL,
  `recurring_count` int(11) DEFAULT NULL,
  `subscr_id` varchar(100) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `campaign_id_idx` (`campaign_id`),
  KEY `donor_id_idx` (`donor_id`),
  KEY `giveback_id_idx` (`giveback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jg_donors`
--

CREATE TABLE IF NOT EXISTS `#__jg_donors` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table #__users',
  `contributor_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table #__individuals or #__organizations',
  `donor_type` varchar(20) NOT NULL DEFAULT '' COMMENT 'Type of donor',
  `campaign_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table #__jg_donors',
  `email` varchar(255) NOT NULL DEFAULT '',
  `first_name` varchar(250) NOT NULL DEFAULT '',
  `last_name` varchar(250) NOT NULL DEFAULT '',
  `org_name` varchar(250) NOT NULL DEFAULT '' COMMENT 'Organization name',
  `address` text DEFAULT NULL,
  `address2` text DEFAULT NULL,
  `city` varchar(250) NOT NULL DEFAULT '',
  `state` varchar(250) NOT NULL DEFAULT '',
  `country` varchar(250) NOT NULL DEFAULT '',
  `zip` varchar(250) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `taxnumber` varchar(50) NOT NULL DEFAULT '',
  `pannumber` TEXT DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `campaign_id_idx` (`campaign_id`),
  KEY `contributor_id_idx` (`contributor_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jg_orders`
--

CREATE TABLE IF NOT EXISTS `#__jg_orders` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `order_id` VARCHAR( 23 ) NOT NULL DEFAULT '',
  `campaign_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table#__jg_campaigns',
  `donor_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table#__jg_donors',
  `donation_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table#__jg_donations',
  `fund_holder` tinyint( 1 ) NOT NULL DEFAULT 0 COMMENT 'To whose account money was originally transferred to: 0-admin, 1-campaign promoter',
  `cdate` datetime DEFAULT NULL COMMENT 'creation date',
  `mdate` datetime DEFAULT NULL COMMENT 'modification date',
  `payment_received_date` datetime DEFAULT NULL COMMENT 'Donation received date',
  `transaction_id` varchar(100) NOT NULL DEFAULT '' COMMENT 'transaction id given by payment processor',
  `original_amount` DECIMAL(16,5) NOT NULL DEFAULT 0 COMMENT 'original amount with no fee applied',
  `amount` DECIMAL(16,5) NOT NULL DEFAULT 0 COMMENT 'amount after applying fee',
  `fee` DECIMAL(16,5) NOT NULL DEFAULT 0 COMMENT 'processing fee',
  `vat_number` varchar(100) NOT NULL DEFAULT '' COMMENT 'VAT number',
  `status` varchar(100) NOT NULL DEFAULT '' COMMENT 'payment status',
  `processor` varchar(100) NOT NULL DEFAULT '' COMMENT 'payment gateway used',
  `ip_address` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP address of payer',
  `extra` text DEFAULT NULL,
  `params` text DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `campaign_id_idx` (`campaign_id`),
  KEY `donor_id_idx` (`donor_id`),
  KEY `donation_id_idx` (`donation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__tj_media_files`
--

CREATE TABLE IF NOT EXISTS `#__tj_media_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT '',
  `type` varchar(250) NOT NULL DEFAULT '',
  `path` varchar(250) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `source` varchar(250) NOT NULL DEFAULT '',
  `original_filename` varchar(250) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT 0,
  `storage` varchar(250) NOT NULL DEFAULT '',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__tj_media_files_xref`
--

CREATE TABLE IF NOT EXISTS `#__tj_media_files_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL DEFAULT 0,
  `client_id` int(11) NOT NULL DEFAULT 0,
  `client` varchar(250) NOT NULL DEFAULT '',
  `is_gallery` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jg_reports`
--

CREATE TABLE IF NOT EXISTS `#__jg_reports` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(5) NOT NULL DEFAULT 0,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'published =1, unpublished = 0',
  `created_by` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'created by user id',
  `created_on` datetime DEFAULT NULL,
  `modified_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id_idx` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jg_individuals`
--

CREATE TABLE IF NOT EXISTS `#__jg_individuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `taxnumber` varchar(50) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `addr_line_1` text DEFAULT NULL,
  `addr_line_2` text DEFAULT NULL,
  `country` int(3) NOT NULL DEFAULT 0,
  `region` int(5) NOT NULL DEFAULT 0,
  `city` int(5) NOT NULL DEFAULT 0,
  `other_city_check` tinyint(1) NOT NULL DEFAULT 0,
  `other_city_value` varchar(100) NOT NULL DEFAULT '',
  `zip` varchar(30) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT 0,
  `created_date` datetime DEFAULT NULL,
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `vendor_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `vendor_id_idx` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `jgive__jg_organizations`
--

CREATE TABLE IF NOT EXISTS `#__jg_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `taxnumber` varchar(50) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `addr_line_1` text DEFAULT NULL,
  `addr_line_2` text DEFAULT NULL,
  `country` int(3) NOT NULL DEFAULT 0,
  `region` int(5) NOT NULL DEFAULT 0,
  `city` int(5) NOT NULL DEFAULT 0,
  `other_city_check` tinyint(1) NOT NULL DEFAULT 0,
  `other_city_value` varchar(100) NOT NULL DEFAULT '',
  `zip` varchar(30) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT 0,
  `created_date` datetime DEFAULT NULL,
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `vendor_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `vendor_id_idx` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;


--
-- Table structure for table `jgive__jg_organization_contact`
--

CREATE TABLE IF NOT EXISTS `#__jg_organization_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table#__jg_organizations',
  `individual_id` int(11) NOT NULL DEFAULT 0 COMMENT 'fk - primary key of table#__jg_individuals',
  PRIMARY KEY (`id`),
  KEY `organization_id_idx` (`organization_id`),
  KEY `individual_id_idx` (`individual_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
