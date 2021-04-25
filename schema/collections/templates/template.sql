-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: philleepedit
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 20.3.0 - x86_64

--
-- Create Collection: joins_$t:nature_$t:subject_$t:object 
-- Example Collection: joins_contents_collection_relation 
--

--
-- Replace $t:field_subject . $t:field_object 
-- Replace $t:nature . $t:subject . $t:object 
--

CREATE TABLE `joins_$t:nature_$t:subject_$t:object` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `sort` int(10) unsigned DEFAULT NULL,
  `$t:field_subject` int(10) unsigned DEFAULT NULL,
  `t:field_object` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Reset Collection: Collection 
--

ALTER TABLE `joins_$t:nature_$t:subject_$t:object` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`) 
VALUES 
	('joins_$t:nature_$t:subject_$t:object', '1', '1', '0', 'compare_arrows', 'Relational $t:nature collection - Links $t:subject and $t:object', NULL);

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields`(`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`)
VALUES (NULL, 'joins_$t:nature_$t:subject_$t:object', '$t:field_subject', 'm2o', 'many-to-one', '{ "template": "", "placeholder": "Select one", "threshold": "0" }', '0', NULL, '0', '0', '0', '0', NULL, 'full', NULL, NULL, '[]'),
       (NULL, 'joins_$t:nature_$t:subject_$t:object', 'id', 'integer', 'primary-key', NULL, '0', NULL, '0', '0', '1', '1', '0', 'full', NULL, NULL, NULL),
       (NULL, 'joins_$t:nature_$t:subject_$t:object', '$t:field_object', 'm2o', 'many-to-one', '{ "template": "", "placeholder": "Select one", "threshold": "0" }', '0', NULL, '0', '0', '0', '0', NULL, 'full', NULL, NULL, '[]'),
       (NULL, 'joins_$t:nature_$t:subject_$t:object', 'sort', 'sort', 'sort', NULL, '0', NULL, '0', '0', '1', '1', '0', 'full', NULL, NULL, NULL),
       (NULL, 'joins_$t:nature_$t:subject_$t:object', 'status', 'status', 'status', '{ "status_mapping": { "published": { "name": "Active Relationship", "value": "published", "text_color": "white", "background_color": "accent", "browse_subdued": false, "browse_badge": true, "soft_delete": false, "published": true, "required_fields": true }, "draft": { "name": "Draft Relationship", "value": "draft", "text_color": "white", "background_color": "blue-grey-100", "browse_subdued": true, "browse_badge": true, "soft_delete": false, "published": false, "required_fields": false }, "deleted": { "name": "Deleted Relationship", "value": "deleted", "text_color": "white", "background_color": "red", "browse_subdued": true, "browse_badge": true, "soft_delete": true, "published": false, "required_fields": false } } }', '0', NULL, '1', '0', '0', '0', '0', 'full', NULL, NULL, NULL);