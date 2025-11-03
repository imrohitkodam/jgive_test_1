
--
-- Table structure for table `#__jg_individuals`
--

CREATE TABLE IF NOT EXISTS `#__jg_individuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `taxnumber` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `addr_line_1` text NOT NULL,
  `addr_line_2` text NOT NULL,
  `country` int(3) NOT NULL,
  `region` int(5) NOT NULL,
  `city` int(5) NOT NULL,
  `other_city_check` tinyint(1) NOT NULL,
  `other_city_value` varchar(100) NOT NULL,
  `zip` varchar(30) NOT NULL,
  `published` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;


--
-- Table structure for table `jgive__jg_organizations`
--

CREATE TABLE IF NOT EXISTS `#__jg_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `taxnumber` varchar(50) NOT NULL,
  `website` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `addr_line_1` text NOT NULL,
  `addr_line_2` text NOT NULL,
  `country` int(3) NOT NULL,
  `region` int(5) NOT NULL,
  `city` int(5) NOT NULL,
  `other_city_check` tinyint(1) NOT NULL,
  `other_city_value` varchar(100) NOT NULL,
  `zip` varchar(30) NOT NULL,
  `published` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

--
-- Add  columns in the donor table
--
ALTER TABLE  `#__jg_donors` ADD contributor_id INT(11) NOT NULL  COMMENT 'fk - primary key of table #__individuals or #__organizations' AFTER user_id;
ALTER TABLE  `#__jg_donors` ADD donor_type varchar(20) NOT NULL  COMMENT 'Type of donor' AFTER contributor_id;
ALTER TABLE  `#__jg_donors` ADD org_name varchar(250) NOT NULL  COMMENT 'Organization name' AFTER last_name;
ALTER TABLE  `#__jg_donors` ADD taxnumber varchar(50) NOT NULL COMMENT 'Donor Tax ID' AFTER phone;
ALTER TABLE  `#__jg_orders` ADD payment_received_date datetime NOT NULL COMMENT 'Donation received date' AFTER mdate;

--
-- Table structure for table `jgive__jg_organization_contact`
--

CREATE TABLE IF NOT EXISTS `#__jg_organization_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_organizations',
  `individual_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_individuals',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;
