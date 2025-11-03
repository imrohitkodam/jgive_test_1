--
-- Change default table engine to InnoDB;
--

ALTER TABLE `#__jg_campaigns` ENGINE = InnoDB;
ALTER TABLE `#__jg_campaigns_givebacks` ENGINE = InnoDB;
ALTER TABLE `#__jg_campaigns_images` ENGINE = InnoDB;
ALTER TABLE `#__jg_donations` ENGINE = InnoDB;
ALTER TABLE `#__jg_donors` ENGINE = InnoDB;
ALTER TABLE `#__jg_orders` ENGINE = InnoDB;
ALTER TABLE `#__jg_payouts` ENGINE = InnoDB;
ALTER TABLE `#__tj_media_files` ENGINE = InnoDB;
ALTER TABLE `#__tj_media_files_xref` ENGINE = InnoDB;
ALTER TABLE `#__jg_reports` ENGINE = InnoDB;
ALTER TABLE `#__jg_individuals` ENGINE = InnoDB;
ALTER TABLE `#__jg_organizations` ENGINE = InnoDB;
ALTER TABLE `#__jg_organization_contacts` ENGINE = InnoDB;
