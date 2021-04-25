-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: joins_contents_campaigns_endpoints 
--

CREATE TABLE `joins_contents_campaigns_endpoints` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `campaign` int(10) unsigned NOT NULL,
  `endpoint` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Reset Collection: joins_contents_campaigns_endpoints 
--

ALTER TABLE `joins_contents_campaigns_endpoints` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('joins_contents_campaigns_endpoints', '1', '1', '0', 'compare_arrows', 'Relational Collection - Links Contents Campaigns and API Endpoints', NULL, NULL);

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`, `display`, `display_options`) 
VALUES 
	(NULL, 'joins_contents_campaigns_endpoints', 'campaign', 'm2o', 'many-to-one', '{
    "template": "{{name}}",
    "visible_fields": "name",
    "placeholder": "Select one",
    "threshold": "0"
}', '0', NULL, '1', '0', '0', '0', NULL, 'full', NULL, NULL, '[]', NULL, NULL), 
	(NULL, 'joins_contents_campaigns_endpoints', 'endpoint', 'm2o', 'many-to-one', '{
    "template": "{{name}}",
    "visible_fields": "name",
    "placeholder": "Select one",
    "threshold": "0"
}', '0', NULL, '1', '0', '0', '0', NULL, 'full', NULL, NULL, '[]', NULL, NULL), 
	(NULL, 'joins_contents_campaigns_endpoints', 'id', 'integer', 'primary-key', NULL, '0', NULL, '0', '0', '1', '1', '0', 'full', NULL, NULL, NULL, NULL, NULL);