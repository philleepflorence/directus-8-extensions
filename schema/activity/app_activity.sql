-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: app_activity 
--

CREATE TABLE `app_activity` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(15) NOT NULL DEFAULT 'curl',
  `method` varchar(20) DEFAULT NULL,
  `collection` varchar(50) NOT NULL,
  `item` int(11) DEFAULT NULL,
  `data` text,
  `owner` int(10) unsigned DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

--
-- Reset Collection: app_activity 
--

ALTER TABLE `app_activity` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('app_activity', '1', '1', '0', 'swap_vert', 'A collection of custom and automated API activities', NULL, '{
    {action
    }
} - {
    {collection
    }
} - {
    {created_on
    }
}');

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`, `display`, `display_options`) 
VALUES 
	(NULL, 'app_activity', 'action', 'string', 'text-input', '{
    "trim": true,
    "showCharacterCount": true,
    "formatValue": true,
    "monospace": false
}', '0', NULL, '1', '0', '0', '0', '2', 'half', NULL, '', '[]', NULL, NULL), 
	(NULL, 'app_activity', 'collection', 'string', 'text-input', '{
    "trim": true,
    "showCharacterCount": true,
    "formatValue": true,
    "monospace": false
}', '0', NULL, '1', '0', '0', '0', '6', 'half', NULL, NULL, '[]', NULL, NULL), 
	(NULL, 'app_activity', 'created_on', 'datetime_created', 'datetime-created', '{
    "showRelative": true
}', '0', NULL, '0', '1', '1', '1', '5', 'half', NULL, '', '[]', NULL, NULL), 
	(NULL, 'app_activity', 'data', 'json', 'json', '[]', '0', NULL, '0', '0', '0', '0', '8', 'full', NULL, NULL, '[]', NULL, NULL), 
	(NULL, 'app_activity', 'id', 'integer', 'primary-key', NULL, '0', NULL, '0', '0', '1', '1', '1', 'full', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'app_activity', 'item', 'integer', 'numeric', '{
    "localized": true,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '7', 'half', NULL, NULL, '[]', NULL, NULL), 
	(NULL, 'app_activity', 'method', 'string', 'text-input', '{
    "trim": true,
    "showCharacterCount": true,
    "formatValue": true,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '3', 'half', NULL, NULL, '[]', NULL, NULL), 
	(NULL, 'app_activity', 'owner', 'owner', 'owner', '{
    "template": "{{first_name}} {{last_name}}",
    "display": "both"
}', '0', NULL, '0', '0', '0', '0', '4', 'half', NULL, '', '[]', NULL, NULL);