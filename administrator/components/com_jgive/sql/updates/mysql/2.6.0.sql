ALTER TABLE `#__jg_campaigns` DROP COLUMN first_name;
ALTER TABLE `#__jg_campaigns` DROP COLUMN last_name;
ALTER TABLE `#__jg_campaigns` DROP COLUMN address;
ALTER TABLE `#__jg_campaigns` DROP COLUMN address2;
ALTER TABLE `#__jg_campaigns` DROP COLUMN zip;
ALTER TABLE `#__jg_campaigns` DROP COLUMN website_address;
ALTER TABLE `#__jg_campaigns` DROP COLUMN country;
ALTER TABLE `#__jg_campaigns` DROP COLUMN state;
ALTER TABLE `#__jg_campaigns` DROP COLUMN city;
ALTER TABLE `#__jg_campaigns` DROP COLUMN other_city;
ALTER TABLE `#__jg_campaigns` DROP COLUMN phone;
ALTER TABLE `#__jg_campaigns` DROP COLUMN paypal_email;

DROP TABLE #__jg_campaigns_images;
