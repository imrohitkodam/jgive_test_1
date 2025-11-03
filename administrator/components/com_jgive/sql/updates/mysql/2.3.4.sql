--
-- Add giveback title new column
--

ALTER TABLE  `#__jg_campaigns_givebacks` ADD title varchar(250) NOT NULL
COMMENT 'giveback title' AFTER campaign_id;
