/*
	Add reply field to activity collection to allow for direct replues!
*/

ALTER TABLE `directus_activity` ADD `reply` INT(11)  NULL  DEFAULT NULL  AFTER `comment_deleted_on`;
INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`) VALUES (NULL, 'directus_activity', 'reply', 'm2o', 'many-to-one', NULL, '1', NULL, '0', '1', '0', '0', '11', 'full', NULL, NULL, NULL);
INSERT INTO `directus_relations` (`id`, `collection_many`, `field_many`, `collection_one`, `field_one`, `junction_field`) VALUES (NULL, 'directus_activity', 'reply', 'directus_activity', NULL, NULL);