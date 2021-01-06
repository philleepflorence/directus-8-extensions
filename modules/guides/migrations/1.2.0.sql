# Contents FAQs - Update Items View Options

UPDATE `directus_collection_presets` SET `view_query` = '{\"tabular\":{\"fields\":\"application,category,icon,question\",\"sort\":\"sort\"}}' WHERE `collection` = 'contents_faqs';

# Contents FAQs - Update Directus Fields

UPDATE `directus_fields` 
SET `options` = '{\"icon\":\"computer\",\"choices\":{\"collection\":\"Working with Collections\",\"concepts\":\"Concepts\",\"faqs\":\"Frequently Asked Questions\",\"guide\":\"How To Guides\",\"reference\":\"Reference\",\"tutorial\":\"Tutorials\"},\"formatting\":true}', `note` = 'The section to which the item belongs', `sort` = 8, `width` = 'half' 
WHERE `collection` = 'contents_faqs' AND `field` = 'category';

UPDATE `directus_fields` 
SET `sort` = 9, `width` = 'half' 
WHERE `collection` = 'contents_faqs' AND `field` = 'answered_by';

ALTER TABLE `contents_faqs` ADD COLUMN `icon` VARCHAR(20)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT 'book'  COMMENT 'Icon to use for Directus or Internal Guides' AFTER `answer`;

ALTER TABLE `contents_faqs` ADD COLUMN `owner` INT(10)  UNSIGNED  NULL  DEFAULT '1' AFTER `updated`;

ALTER TABLE `contents_faqs` ADD COLUMN `modified_by` INT(10)  UNSIGNED  NULL  DEFAULT '1' AFTER `owner`;

INSERT INTO `directus_fields` (`collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`)
VALUES
	('contents_faqs', 'icon', 'string', 'icon', '[]', 0, NULL, 0, 0, 0, 0, 11, 'full', NULL, 'Icon to use for Directus or Internal Guides', '[]'),
	('contents_faqs', 'divider_security', 'alias', 'divider', '{\"style\":\"large\",\"title\":\"Security and Authentication Information\",\"description\":\"These items are primarily read only!\",\"hr\":true,\"margin\":false}', 0, NULL, 0, 0, 0, 0, 12, 'full', NULL, 'Only edit in extreme cases (such as an application server error)!', '[]'),
	('contents_faqs', 'owner', 'owner', 'owner', '{\"template\":\"{{first_name}} {{last_name}}\",\"display\":\"both\"}', 0, NULL, 0, 0, 0, 0, 15, 'half', NULL, NULL, '[]'),
	('contents_faqs', 'modified_by', 'user_updated', 'user-updated', '{\"template\":\"{{first_name}} {{last_name}}\",\"display\":\"both\"}', 0, NULL, 0, 0, 0, 0, 16, 'half', NULL, NULL, '[]');

	
# Contents FAQs - Directus Collection Configuration

UPDATE `directus_collections`
SET `icon` = 'book', `note` = 'Collection of documents (as on a website) that provides answers to a list of typical questions that users might ask regarding a particular subject', `translation` = '[{\"locale\":\"en-US\",\"translation\":\"Guides & FAQs\"}]'
WHERE `collection` = 'contents_faqs';

# Directus Roles - Modules

UPDATE `directus_roles`
SET `module_listing` = '[{\"name\":\"Dashboard - Getting Started\",\"link\":\"\\/app\\/ext\\/dashboard\",\"icon\":\"view_quilt\"},{\"name\":\"All Collections and Contents\",\"link\":\"\\/app\\/collections\",\"icon\":\"storage\"},{\"newItem\":true,\"name\":\"Guides and Documentation\",\"link\":\"\\/app\\/ext\\/guides\",\"icon\":\"book\"},{\"newItem\":true,\"name\":\"Comments\",\"link\":\"\\/app\\/ext\\/comments\",\"icon\":\"notifications_active\"},{\"name\":\"Uploaded Files Directory\",\"link\":\"\\/app\\/files\",\"icon\":\"cloud_done\"},{\"name\":\"Directus Users Directory\",\"link\":\"\\/app\\/users\",\"icon\":\"supervised_user_circle\"}]';
