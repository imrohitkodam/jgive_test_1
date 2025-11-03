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

ALTER TABLE `#__jg_campaigns` ADD `amount_suggestions` text COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
