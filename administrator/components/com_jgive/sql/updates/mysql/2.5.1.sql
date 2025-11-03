ALTER TABLE `#__jg_campaigns` CHANGE `goal_amount` `goal_amount` DECIMAL(16,5) NOT NULL;
ALTER TABLE `#__jg_campaigns` CHANGE `minimum_amount` `minimum_amount` DECIMAL(16,5) NOT NULL;
ALTER TABLE `#__jg_campaigns_givebacks` CHANGE `amount` `amount` DECIMAL(16,5) NOT NULL;
ALTER TABLE `#__jg_orders` CHANGE `original_amount` `original_amount` DECIMAL(16,5) NOT NULL;
ALTER TABLE `#__jg_orders` CHANGE `amount` `amount` DECIMAL(16,5) NOT NULL;
ALTER TABLE `#__jg_orders` CHANGE `fee` `fee` DECIMAL(16,5) NOT NULL;

DROP TABLE #__jg_payouts;
