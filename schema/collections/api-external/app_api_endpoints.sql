-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: app_api_endpoints 
--

CREATE TABLE `app_api_endpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) NOT NULL DEFAULT 'draft',
  `sort` int(11) DEFAULT NULL,
  `api_provider` int(11) DEFAULT NULL COMMENT 'Name and information on the API Provider',
  `name` varchar(100) NOT NULL COMMENT 'Name of the provider or external API or CRM',
  `slug` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL COMMENT 'A short description of the purpose of the endpoint',
  `endpoint` varchar(250) DEFAULT NULL COMMENT 'cURL API Endpoint or URL. Do not include any queries in the URL, use App APIs Options instead!',
  `method` varchar(100) DEFAULT 'POST' COMMENT 'A request method supported by HTTP.',
  `data_type` varchar(20) DEFAULT 'JSON' COMMENT 'Format of data to send to API or CRM',
  `content` text COMMENT 'Detailed information of API or CRM provider. May be displayed for public or authenticated users.',
  `json_response` text COMMENT 'A sample JSON response received after submitting data to API or CRM. Omit non-essential properties!',
  `template` text COMMENT 'Handlebars template to use for displaying data if applicable: [Learn more
](https: //handlebarsjs.com/)',
  `response_type` varchar(10) DEFAULT 'json' COMMENT 'The format of the response from the API',
  `response_endpoint` int(11) DEFAULT NULL COMMENT 'Endpoint to call after a successful response from the current endpoint. Useful for oauth endpoints!',
  `properties` varchar(2000) DEFAULT NULL COMMENT 'Properties and credentials to use from the API Provider - for data properties use the API Data Options',
  `data` text COMMENT 'Map the required API properties to the internal fields',
  `url` varchar(200) DEFAULT NULL COMMENT 'API documentation URL. If absent, use the API Provider Documentation URL!',
  `fields` text COMMENT 'New fields to create from the existing data fields - using template strings',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_status_provider` (`status`,`api_provider`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Reset Collection: app_api_endpoints 
--

ALTER TABLE `app_api_endpoints` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('app_api_endpoints', '1', '1', '0', 'link', 'Settings, contents, and options for the endpoints APIs, CRMs, and Third Party Integrations.', '[
    {
        "locale": "en-US",
        "translation": "API Endpoints"
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
	(NULL, 'app_api_endpoints', 'api_options', 'o2m', 'many-to-many', '{
    "fields": "name,type,description",
    "template": "{{name}} - {{type}}",
    "allow_create": true,
    "allow_select": true
}', '0', NULL, '0', '0', '0', '0', '18', 'full', NULL, 'Options to use for Authentication and Data User Form Fields and Values.', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "API Form Options"
    }
]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'api_provider', 'm2o', 'many-to-one', '{
    "template": "{{name}}",
    "visible_fields": "name,type,description",
    "threshold": "0",
    "icon": "extension"
}', '0', NULL, '0', '0', '0', '0', '7', 'half', NULL, 'Name and information on the API Provider', '[]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'content', 'string', 'wysiwyg-extended', '{
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
}', '0', NULL, '0', '0', '0', '0', '19', 'full', NULL, 'Detailed information of API or CRM endpoint. May be displayed for public or authenticated users.', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'data', 'json', 'key-value', '{
    "keyInterface": "text-input",
    "keyType": "string",
    "keyOptions": {
        "placeholder": "API Provider Property",
        "trim": true,
        "iconLeft": "fingerprint"
    },
    "valueInterface": "text-input",
    "valueType": "string",
    "valueOptions": {
        "placeholder": "Internal Field",
        "trim": true,
        "iconLeft": "short_text"
    }
}', '0', NULL, '0', '0', '0', '0', '16', 'full', NULL, 'Map the required API properties to the internal fields', '[
    {
        "locale": "en-US",
        "translation": "Map API Properties"
    }
]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'data_type', 'string', 'dropdown', '{
    "choices": {
        "json": "JSON",
        "url": "URL Form Encoded",
        "query": "Query"
    },
    "icon": "code",
    "formatting": true
}', '0', NULL, '0', '0', '0', '0', '12', 'half', NULL, 'Format of data to send to API or CRM', '[]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'description', 'string', 'text-input', '{
    "iconLeft": "short_text",
    "trim": true,
    "showCharacterCount": true,
    "formatValue": false,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '8', 'half', NULL, 'A short description of the purpose of the endpoint', '[]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'divider_information', 'alias', 'divider', '{
    "style": "large",
    "title": "Getting Started",
    "description": "Settings, contents, and options for the endpoints APIs, CRMs, and Third Party Integrations.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '3', 'full', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'divider_options', 'alias', 'divider', '{
    "style": "large",
    "title": "Additional Options",
    "description": "Other related contents, responses, and options.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '0', '17', 'full', NULL, 'For use mainly with dynamic user submitted information.', '[]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'endpoint', 'string', 'text-input', '{
    "iconLeft": "link",
    "showCharacterCount": true,
    "trim": true,
    "placeholder": "https://api.example.com/api/{{dynamic}}/path"
}', '0', NULL, '0', '0', '0', '0', '9', 'half', NULL, 'cURL API Endpoint or URL. Do not include any queries in the URL, use App APIs Options instead!', '[]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'fields', 'json', 'key-value', '{
    "keyInterface": "text-input",
    "keyType": "string",
    "keyOptions": {
        "placeholder": "Field to create",
        "trim": true,
        "iconLeft": "fingerprint"
    },
    "valueInterface": "text-input",
    "valueType": "string",
    "valueOptions": {
        "placeholder": "Value Template String",
        "trim": true,
        "iconLeft": "short_text"
    }
}', '0', NULL, '0', '0', '0', '0', '15', 'full', NULL, 'New fields to create from the existing data fields - using template strings', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "Template Fields"
    }
]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'id', 'integer', 'primary-key', '{
    "monospace": true
}', '0', NULL, '0', '0', '1', '1', '1', 'full', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'json_response', 'string', 'json', '[]', '0', NULL, '0', '0', '0', '0', '21', 'full', NULL, 'A sample JSON response received after submitting data to API or CRM. Omit non-essential properties!', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "Sample JSON Response"
    }
]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'method', 'string', 'dropdown', '{
    "choices": {
        "POST": "POST",
        "PUT": "PUT",
        "PATCH": "PATCH",
        "DELETE": "DELETE",
        "GET": "GET"
    },
    "icon": "call_missed_outgoing",
    "formatting": true
}', '0', NULL, '1', '0', '0', '0', '11', 'half', NULL, 'A request method supported by HTTP.', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'name', 'string', 'text-input', '{
    "showCharacterCount": true,
    "trim": true,
    "formatValue": true,
    "placeholder": "",
    "iconLeft": "fingerprint"
}', '0', NULL, '1', '0', '0', '0', '5', 'half', NULL, 'Name of the provider or external API or CRM', '[]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'properties', 'array', 'tags', '{
    "iconLeft": "local_offer",
    "iconRight": "local_offer",
    "validationMessage": "Please enter a valid tag",
    "alphabetize": true,
    "lowercase": true,
    "wrap": false,
    "format": true,
    "sanitize": false
}', '0', NULL, '0', '0', '0', '0', '14', 'half', NULL, 'Properties and credentials to use from the API Provider - for data properties use the API Data Options', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "API Provider Properties"
    }
]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'response_endpoint', 'm2o', 'many-to-one', '{
    "threshold": "0",
    "icon": "link",
    "visible_fields": "name,endpoint",
    "template": "{{name}} - {{endpoint}}"
}', '0', NULL, '0', '0', '0', '0', '20', 'full', NULL, 'Endpoint to call after a successful response from the current endpoint. Useful for oauth endpoints!', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'response_type', 'string', 'dropdown', '{
    "choices": {
        "json": "JSON",
        "array": "Array",
        "object": "Object"
    },
    "icon": "settings_backup_restore",
    "formatting": true
}', '0', NULL, '0', '0', '0', '0', '13', 'full', NULL, 'The format of the response from the API', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "Response Type"
    }
]', NULL, NULL), 
	(NULL, 'app_api_endpoints', 'slug', 'slug', 'slug', '{
    "mirroredField": "name",
    "forceLowercase": true
}', '0', NULL, '0', '0', '0', '0', '6', 'half', NULL, '', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'sort', 'sort', 'sort', '', '0', NULL, '0', '0', '1', '0', '2', 'full', NULL, '', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'status', 'status', 'status', '{
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
}', '0', NULL, '0', '0', '0', '0', '4', 'full', NULL, 'Display Status - only active, online or published rows will be displayed!', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'template', 'string', 'code', '{
    "language": "application/x-httpd-php",
    "lineNumber": true
}', '0', NULL, '0', '0', '0', '0', '22', 'full', NULL, 'Handlebars template to use for displaying data if applicable: [Learn more
](https: //handlebarsjs.com/)', NULL, NULL, NULL), 
	(NULL, 'app_api_endpoints', 'url', 'string', 'text-input', '{
    "iconLeft": "link",
    "trim": true,
    "showCharacterCount": true,
    "formatValue": false,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '10', 'half', NULL, 'API documentation URL. If absent, use the API Provider Documentation URL!', '[]', NULL, NULL);

--
-- Insert Collection Options: directus_relations 
--

INSERT INTO `directus_relations` (`id`, `collection_many`, `field_many`, `collection_one`, `field_one`, `junction_field`) 
VALUES 
	(NULL, 'app_api_endpoints', 'response_endpoint', 'app_api_endpoints', NULL, NULL), 
	(NULL, 'joins_app_endpoints_options', 'api', 'app_api_endpoints', 'api_options', 'option'), 
	(NULL, 'joins_contents_campaigns_endpoints', 'endpoint', 'app_api_endpoints', NULL, NULL), 
	(NULL, 'joins_contents_campaigns_endpoints', 'endpoint', 'app_api_endpoints', NULL, 'campaign');