-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: app_api_options 
--

CREATE TABLE `app_api_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) NOT NULL DEFAULT 'published',
  `name` varchar(50) DEFAULT NULL COMMENT 'Name of Input or Property',
  `output` varchar(100) DEFAULT NULL COMMENT 'Name,  key, or property of output - outgoing data to send to API',
  `type` varchar(20) DEFAULT NULL COMMENT 'Type of Option. Required to build API Object.',
  `input` varchar(50) DEFAULT NULL COMMENT 'Input property to use to get dynamic value, if empty, you must provide a static value!',
  `format` varchar(20) DEFAULT NULL COMMENT 'Formats the value against a set of internal processes',
  `value` varchar(150) DEFAULT NULL COMMENT 'Value to use if empty, you must provide the input property!',
  `description` text COMMENT 'Description of the API field or option',
  `user_editable` tinyint(4) DEFAULT '0' COMMENT 'Allows user to update the value column',
  `form` varchar(20) DEFAULT NULL COMMENT 'The form, element, group, or section to which this option belongs. <br>For Form exports, enter the name of the form.',
  PRIMARY KEY (`id`),
  KEY `idx_status_form` (`status`,`form`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Reset Collection: app_api_options 
--

ALTER TABLE `app_api_options` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('app_api_options', '1', '1', '0', 'settings_ethernet', 'Settings, contents, and options for the endpoint form options APIs, CRMs, and Third Party Integrations.', '[
    {
        "locale": "en-US",
        "translation": "API Options"
    }
]', NULL);

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`, `display`, `display_options`) 
VALUES 
	(NULL, 'app_api_options', 'description', 'string', 'textarea', '{
    "rows": "10"
}', '0', NULL, '1', '0', '0', '0', '7', 'full', NULL, 'Description of the API field or option', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'divider_information', 'alias', 'divider', '{
    "style": "large",
    "title": "Getting Started",
    "description": "Settings, contents, and options for the endpoint form options for external APIs, CRMs, and Third Party Integrations.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '2', 'full', NULL, 'This allows you to make edits in realtime to the data exported to you Third Party Integration. <br>Be sure to consult the documentation of your provider on how to format the data sent before making edits.', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'form', 'string', 'text-input', '{
    "iconLeft": "code",
    "trim": true,
    "showCharacterCount": true,
    "formatValue": true,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '5', 'half', NULL, 'The form, element, group, or section to which this option belongs. <br>For Form exports, enter the name of the form.', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'format', 'string', 'dropdown', '{
    "choices": {
        "string": "Text",
        "email": "Email ID",
        "array": "Array or List",
        "boolean": "Boolean",
        "number": "Number or Float",
        "integer": "Integer",
        "uuid": "Universally Unique Identifier"
    },
    "formatting": true,
    "icon": "transform"
}', '0', NULL, '1', '0', '0', '0', '9', 'half', NULL, 'Formats the value against a set of internal processes', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'id', 'integer', 'primary-key', '{
    "monospace": true
}', '0', NULL, '0', '0', '1', '1', '1', 'full', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'input', 'string', 'text-input', '{
    "showCharacterCount": true,
    "trim": true,
    "iconLeft": "trending_down"
}', '0', NULL, '0', '0', '0', '0', '10', 'half', NULL, 'Input property to use to get dynamic value, if empty, you must provide a static value!', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'name', 'string', 'text-input', '{
    "showCharacterCount": true,
    "trim": true,
    "formatValue": true,
    "placeholder": "",
    "iconLeft": "fingerprint"
}', '0', NULL, '1', '0', '0', '0', '4', 'half', NULL, 'Name of Input or Property', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'output', 'string', 'text-input', '{
    "iconLeft": "trending_up",
    "trim": true,
    "showCharacterCount": true
}', '0', NULL, '0', '0', '0', '0', '11', 'half', NULL, 'Name,  key, or property of output - outgoing data to send to API', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'status', 'status', 'status', '{
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
}', '0', NULL, '0', '0', '0', '0', '3', 'full', NULL, 'Display Status - only active, online or published rows will be displayed!', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'type', 'string', 'dropdown', '{
    "choices": {
        "authentication": "Authentication",
        "header": "Header",
        "value": "Value",
        "url": "URL Component"
    },
    "icon": "more_vert",
    "formatting": true
}', '0', NULL, '1', '0', '0', '0', '8', 'half', NULL, 'Type of Option. Required to build API Object.', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'user_editable', 'boolean', 'switch', '{
    "labelOn": "Editable",
    "labelOff": "Read Only"
}', '0', NULL, '0', '0', '0', '0', '12', 'half', NULL, 'Allows user to update the value column', NULL, NULL, NULL), 
	(NULL, 'app_api_options', 'value', 'string', 'text-input', '{
    "iconLeft": "short_text",
    "showCharacterCount": true,
    "trim": true
}', '0', NULL, '0', '0', '0', '0', '6', 'full', NULL, 'Value to use if empty, you must provide the input property!', NULL, NULL, NULL);

--
-- Insert Collection Options: directus_relations 
--

INSERT INTO `directus_relations` (`id`, `collection_many`, `field_many`, `collection_one`, `field_one`, `junction_field`) 
VALUES 
	(NULL, 'joins_app_endpoints_options', 'option', 'app_api_options', NULL, 'api');