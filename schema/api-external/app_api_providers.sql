-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: app_api_providers 
--

CREATE TABLE `app_api_providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) NOT NULL DEFAULT 'draft' COMMENT 'Active Status - Only active or published rows will be displayed!',
  `name` varchar(100) DEFAULT NULL COMMENT 'Name of Third Party Provider',
  `slug` varchar(100) DEFAULT NULL COMMENT 'URL Representation of the name of the provider',
  `type` varchar(20) DEFAULT 'crm' COMMENT 'Type of provider - CRM, Social Network, et al.',
  `description` varchar(150) DEFAULT NULL COMMENT 'Short description explaining provider',
  `website` varchar(150) DEFAULT NULL COMMENT 'Main website of provider',
  `url` varchar(150) DEFAULT NULL COMMENT 'API documentation URL. If absent, use website URL!',
  `image` int(10) unsigned NOT NULL COMMENT 'An image that identifies the API Provider',
  `content` text COMMENT 'Information on how to use API and any other important information to display to end users.',
  `integration_url` varchar(150) DEFAULT NULL COMMENT 'URL with information on integration.',
  `credentials` text COMMENT 'Additional credentials, parameters, and properties required by the Provider - Be sure to enter the property as required by the provider!',
  PRIMARY KEY (`id`),
  KEY `idx_status_name_slug` (`status`,`slug`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Reset Collection: app_api_providers 
--

ALTER TABLE `app_api_providers` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('app_api_providers', '1', '0', '0', 'extension', 'Settings, contents, and options for external APIs, CRMs, and Third Party Integrations.', '[
    {
        "locale": "en-US",
        "translation": "External API Providers"
    }
]', '{
    {name
    }
} - {
    {description
    }
}');

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`, `display`, `display_options`) 
VALUES 
	(NULL, 'app_api_providers', 'content', 'string', 'wysiwyg-extended', '{
    "toolbar": [
        "undo",
        "redo",
        "bold",
        "italic",
        "underline",
        "bullist",
        "numlist",
        "blockquote",
        "h1",
        "h2",
        "h3",
        "h4",
        "h5",
        "h6",
        "link",
        "unlink",
        "image",
        "media",
        "hr",
        "strikethrough",
        "subscript",
        "superscript",
        "indent",
        "table",
        "selectall",
        "copy",
        "cut",
        "paste",
        "removeformat",
        "remove",
        "code",
        "fullscreen"
    ],
    "cdn": "http://cdn.directus.local",
    "cdn_template": "app/thumbnails/{{width/{{height}}/{{fit}}",
    "tinymce_options": {
        "image_caption": true,
        "image_class_list": [
            {
                "title": "None",
                "value": ""
            },
            {
                "title": "Image Full Width",
                "value": "d-block-100"
            },
            {
                "title": "Align Image Center",
                "value": "d-block-center"
            },
            {
                "title": "Align Image Right",
                "value": "d-block-right"
            },
            {
                "title": "Align Image Left",
                "value": "d-block-left"
            }
        ],
        "image_dimensions": true
    }
}', '0', NULL, '0', '0', '0', '1', '11', 'full', NULL, 'Information on how to use API and any other important information to display to end users.', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'credentials', 'json', 'key-value', '{
    "keyInterface": "text-input",
    "keyType": "string",
    "keyOptions": {
        "placeholder": "API Provider Property"
    },
    "valueInterface": "text-input",
    "valueType": "string",
    "valueOptions": {
        "placeholder": "Value provided by the API Provider"
    }
}', '0', NULL, '0', '0', '0', '0', '14', 'full', NULL, 'Additional credentials, parameters, and properties required by the Provider - Be sure to enter the property as required by the provider!', '[]', NULL, NULL), 
	(NULL, 'app_api_providers', 'description', 'string', 'text-input', '{
    "iconLeft": "info",
    "showCharacterCount": true,
    "trim": true
}', '0', NULL, '0', '0', '0', '0', '7', 'half', NULL, 'Short description explaining provider', '[]', NULL, NULL), 
	(NULL, 'app_api_providers', 'divider_information', 'alias', 'divider', '{
    "style": "large",
    "title": "Getting Started",
    "description": "Settings, contents, and options for external APIs, CRMs, and Third Party Integrations.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '2', 'full', NULL, 'The following information are usually shown to the end users.', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'divider_options', 'alias', 'divider', '{
    "style": "large",
    "title": "Additional options",
    "description": "These are usually required when accessing the endpoints of the API Provider.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '12', 'full', NULL, 'The following information are usually hidden from the end users.', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'endpoints', 'o2m', 'one-to-many', '{
    "fields": "name,description",
    "template": "{{name}}",
    "sort_field": "sort",
    "delete_mode": "relation",
    "allow_create": true,
    "allow_select": true
}', '0', NULL, '0', '0', '0', '1', '15', 'full', NULL, 'Currently available endpoints', '[]', NULL, NULL), 
	(NULL, 'app_api_providers', 'id', 'integer', 'primary-key', '{
    "monospace": true
}', '0', NULL, '1', '0', '1', '1', '1', 'full', NULL, '', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'image', 'file', 'file', '{
    "crop": true,
    "viewType": "cards",
    "viewOptions": {
        "title": "title",
        "subtitle": "type",
        "content": "description",
        "src": "data"
    },
    "viewQuery": [],
    "filters": [],
    "accept": "image/jpeg,image/png,image/gif",
    "allowDelete": false
}', '0', NULL, '1', '0', '0', '0', '10', 'full', NULL, 'An image that identifies the API Provider', '[]', NULL, NULL), 
	(NULL, 'app_api_providers', 'integration_url', 'string', 'text-input', '{
    "trim": true,
    "showCharacterCount": true,
    "iconLeft": "link",
    "placeholder": "https://www.example.com..."
}', '0', NULL, '0', '0', '0', '1', '13', 'full', NULL, 'URL with information on integration.', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'name', 'string', 'text-input', '{
    "showCharacterCount": true,
    "trim": true,
    "formatValue": true,
    "placeholder": "",
    "iconLeft": "fingerprint"
}', '0', NULL, '1', '0', '0', '0', '4', 'half', NULL, 'Name of Third Party Provider', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'slug', 'slug', 'slug', '{
    "mirroredField": "name",
    "forceLowercase": true
}', '0', NULL, '0', '0', '0', '1', '5', 'half', NULL, 'URL Representation of the name of the provider', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'status', 'status', 'status', '{
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
}', '0', NULL, '0', '0', '0', '1', '3', 'full', NULL, 'Display Status - only active, online or published rows will be displayed!', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'type', 'string', 'dropdown', '{
    "placeholder": "Choose an option...",
    "forceLowercase": true,
    "allow_other": false,
    "formatting": true,
    "choices": {
        "crm": "CRM",
        "social": "Social Network"
    },
    "icon": "more_vert"
}', '0', NULL, '0', '0', '0', '0', '6', 'half', NULL, 'Type of provider - CRM, Social Network, et al.', '[]', NULL, NULL), 
	(NULL, 'app_api_providers', 'url', 'string', 'text-input', '{
    "trim": true,
    "showCharacterCount": true,
    "iconLeft": "link",
    "placeholder": "https://www.example.com..."
}', '0', NULL, '1', '0', '0', '0', '9', 'half', NULL, 'API documentation URL. If absent, use website URL!', NULL, NULL, NULL), 
	(NULL, 'app_api_providers', 'website', 'string', 'text-input', '{
    "showCharacterCount": true,
    "trim": true,
    "iconLeft": "link",
    "placeholder": "https://www.example.com..."
}', '0', NULL, '1', '0', '0', '0', '8', 'half', NULL, 'Main website of provider', NULL, NULL, NULL);

--
-- Insert Collection Options: directus_collection_presets 
--

INSERT INTO `directus_collection_presets` (`id`, `title`, `user`, `role`, `collection`, `search_query`, `filters`, `view_type`, `view_query`, `view_options`, `translation`) 
VALUES 
	(NULL, NULL, NULL, NULL, 'app_api_providers', NULL, NULL, 'tabular', '{
    "tabular": {
        "fields": "name,type,website,url,description"
    }
}', '{
    "tabular": {
        "spacing": "comfortable"
    }
}', NULL);

--
-- Insert Collection Options: directus_relations 
--

INSERT INTO `directus_relations` (`id`, `collection_many`, `field_many`, `collection_one`, `field_one`, `junction_field`) 
VALUES 
	(NULL, 'app_api_endpoints', 'provider', 'app_api_providers', 'endpoints', NULL), 
	(NULL, 'app_api_endpoints', 'api_provider', 'app_api_providers', 'endpoints', NULL);