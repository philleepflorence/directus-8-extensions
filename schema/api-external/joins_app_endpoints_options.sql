-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: joins_app_endpoints_options 
--

CREATE TABLE `joins_app_endpoints_options` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) DEFAULT 'published',
  `api` int(10) DEFAULT NULL,
  `option` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_fields` (`status`,`api`,`option`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Reset Collection: joins_app_endpoints_options 
--

ALTER TABLE `joins_app_endpoints_options` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('joins_app_endpoints_options', '1', '1', '0', 'compare_arrows', 'Relational Collection - Links API Endpoints and API Options', NULL, NULL);

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`, `display`, `display_options`) 
VALUES 
	(NULL, 'joins_app_endpoints_options', 'api', 'm2o', 'many-to-one', '{
    "icon": "insert_link",
    "template": "{{provider}} - {{name}}:  {{endpoint}}",
    "visible_fields": "provider,name,endpoint,method",
    "threshold": "0"
}', '0', NULL, '0', '0', '0', '0', NULL, 'full', NULL, '', '[]', NULL, NULL), 
	(NULL, 'joins_app_endpoints_options', 'id', 'integer', 'primary-key', '{
    "monospace": true
}', '0', NULL, '1', '0', '0', '0', NULL, 'full', NULL, '', '[]', NULL, NULL), 
	(NULL, 'joins_app_endpoints_options', 'option', 'm2o', 'many-to-one', '{
    "template": "{{name}} - {{output}} ",
    "visible_fields": "name,output,type,input",
    "icon": "settings_ethernet",
    "threshold": "0"
}', '0', NULL, '0', '0', '0', '0', NULL, 'full', NULL, '', '[]', NULL, NULL), 
	(NULL, 'joins_app_endpoints_options', 'status', 'status', 'status', '{
    "simpleBadge": true,
    "status_mapping": {
        "published": {
            "name": "Published",
            "value": "published",
            "text_color": "white",
            "background_color": "green",
            "browse_subdued": false,
            "browse_badge": true,
            "soft_delete": false,
            "published": true,
            "required_fields": true
        },
        "draft": {
            "name": "Draft",
            "value": "draft",
            "text_color": "grey-800",
            "background_color": "grey-200",
            "browse_subdued": true,
            "browse_badge": true,
            "soft_delete": false,
            "published": false,
            "required_fields": false
        },
        "invited": {
            "name": "Invited",
            "value": "invited",
            "text_color": "grey-800",
            "background_color": "grey-400",
            "browse_badge": true,
            "published": false
        },
        "pending": {
            "name": "Pending",
            "value": "pending",
            "text_color": "grey-50",
            "background_color": "grey-600",
            "browse_subdued": true,
            "browse_badge": true
        },
        "online": {
            "name": "Online",
            "value": "online",
            "text_color": "light-green-50",
            "background_color": "light-green",
            "browse_badge": true,
            "published": true
        },
        "offline": {
            "name": "Offline",
            "value": "offline",
            "text_color": "grey-50",
            "background_color": "grey-700",
            "browse_subdued": true,
            "browse_badge": true
        },
        "inactive": {
            "name": "Inactive",
            "value": "inactive",
            "text_color": "amber-50",
            "background_color": "amber-700",
            "browse_subdued": true
        },
        "deleted": {
            "name": "Deleted",
            "value": "deleted",
            "text_color": "white",
            "background_color": "red",
            "browse_subdued": true,
            "browse_badge": true,
            "soft_delete": true,
            "published": false,
            "required_fields": false
        }
    }
}', '0', NULL, '0', '0', '0', '0', NULL, 'full', NULL, 'Display Status - only active, online or published rows will be displayed!', NULL, NULL, NULL);