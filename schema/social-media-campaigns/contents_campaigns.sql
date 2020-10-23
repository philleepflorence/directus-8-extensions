-- Directus MySQL Collection Schema and Options Dump
--
-- Host: localhost    Database: directus
-- ------------------------------------------------------
-- Server Domain: api.directus.local
-- Server Version: Darwin - 19.6.0 - x86_64

--
-- Create Collection: contents_campaigns 
--

CREATE TABLE `contents_campaigns` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `name` varchar(200) NOT NULL COMMENT 'The display name or tag of the campaign',
  `slug` varchar(200) DEFAULT NULL COMMENT 'URL Safe representation of the name',
  `start_date` datetime NOT NULL COMMENT 'This is the date and time the campaign should start',
  `end_date` datetime DEFAULT NULL COMMENT 'This is the date and time the campaign should end',
  `image` int(10) unsigned NOT NULL COMMENT 'The image to use with the campaign',
  `page` int(10) unsigned NOT NULL COMMENT 'The page to which this campaign leads',
  `article` int(10) unsigned DEFAULT NULL COMMENT 'An article or detailed post with additional information on the campaign',
  `content` text NOT NULL COMMENT 'This is the content or message sent to the social media platforms',
  `url` varchar(200) DEFAULT NULL COMMENT 'The external URL of the campaign - optional if no internal post or page content exists!',
  `call_to_action` varchar(200) DEFAULT 'Learn More' COMMENT 'Text to display as a call to action button',
  `owner` int(10) unsigned DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `modified_by` int(10) unsigned DEFAULT NULL,
  `modified_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique_name_page_start_date` (`name`,`page`,`start_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Reset Collection: contents_campaigns 
--

ALTER TABLE `contents_campaigns` AUTO_INCREMENT = 1;

--
-- Insert Collection Options: directus_collections 
--

INSERT INTO `directus_collections` (`collection`, `managed`, `hidden`, `single`, `icon`, `note`, `translation`, `display_template`) 
VALUES 
	('contents_campaigns', '1', '0', '0', 'timelapse', 'A collection of automated social media campaign posts', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "Social Media Posts"
    }
]', '{
    {name
    }
} - {
    {start_date
    }
}');

--
-- Insert Collection Options: directus_fields 
--

INSERT INTO `directus_fields` (`id`, `collection`, `field`, `type`, `interface`, `options`, `locked`, `validation`, `required`, `readonly`, `hidden_detail`, `hidden_browse`, `sort`, `width`, `group`, `note`, `translation`, `display`, `display_options`) 
VALUES 
	(NULL, 'contents_campaigns', 'activity', 'o2m', 'one-to-many-extended', '{
    "filters": [
        {
            "field": "collection",
            "operator": "eq",
            "value": "contents_campaigns"
        }
    ],
    "item_count": "Items — {{count}}",
    "template": "{{action}} - {{method}}",
    "fields": "action,method,owner,created_on",
    "allow_create": false,
    "allow_select": false,
    "sort_field": "",
    "delete_mode": "readonly"
}', '0', NULL, '0', '0', '0', '0', '16', 'full', NULL, 'Social Media API activity - click on each activity for details', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'apis', 'o2m', 'many-to-many', '{
    "fields": "name,description",
    "template": "{{name}} - {{description}}",
    "allow_create": true,
    "allow_select": true
}', '0', NULL, '0', '0', '0', '0', '12', 'full', NULL, 'The social media APIs to use when publishing this campaign', '[
    {
        "newItem": true,
        "locale": "en-US",
        "translation": "Social Media Endpoints"
    }
]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'article', 'm2o', 'many-to-one', '{
    "icon": "format_align_justify",
    "template": "{{title}}",
    "visible_fields": "title,post_type,category",
    "placeholder": "Select one post",
    "threshold": "0"
}', '0', NULL, '0', '0', '0', '0', '15', 'half', NULL, 'An article or detailed post with additional information on the campaign', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'call_to_action', 'string', 'text-input', '{
    "iconLeft": "touch_app",
    "trim": true,
    "showCharacterCount": true,
    "formatValue": true,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '10', 'half', NULL, 'Text to display as a call to action button', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'content', 'string', 'textarea', '{
    "rows": "5",
    "serif": false,
    "placeholder": "Use call to action words or phrases...the first 2 lines must describe the campaign perfectly!"
}', '0', NULL, '1', '0', '0', '0', '7', 'full', NULL, 'This is the content or message sent to the social media platforms', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'created_on', 'datetime_created', 'datetime-created', NULL, '0', NULL, '0', '1', '0', '1', '19', 'half', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'contents_campaigns', 'divider_information', 'alias', 'divider', '{
    "style": "large",
    "title": "Getting Started",
    "description": "Campaigns are automated posts made to social media platforms such as Facebook or Instagram.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '2', 'full', NULL, 'To automate the process, be sure the credentials for all the social media platforms have been added to the API! <br>Images for certain social media networks, such as Instagram, will be sized and cropped (Squares) so be sure the focal point of the image is in the center. <br>HashTags should be added at the end of the content.', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'divider_related', 'alias', 'divider', '{
    "style": "large",
    "title": "Related Contents",
    "description": "These are additional contents and data related to the current campaign.",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '13', 'full', NULL, 'Some or all of these may be visible on the page', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'divider_security', 'alias', 'divider', '{
    "style": "large",
    "title": "Security and Authentication Information",
    "description": "These items are primarily read only!",
    "hr": true,
    "margin": false
}', '0', NULL, '0', '0', '0', '1', '17', 'full', NULL, 'Only edit in extreme cases (such as an application server error)!', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'end_date', 'datetime', 'datetime', '{
    "iconLeft": "timer_off",
    "format": "mdy",
    "localized": true,
    "showRelative": false
}', '0', NULL, '0', '0', '0', '0', '9', 'half', NULL, 'This is the date and time the campaign should end', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'id', 'integer', 'primary-key', NULL, '0', NULL, '0', '0', '1', '1', '1', 'full', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'contents_campaigns', 'image', 'file', 'file', '{
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
}', '0', NULL, '1', '0', '0', '0', '6', 'full', NULL, 'The image to use with the campaign', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'modified_by', 'user_updated', 'user-updated', '{
    "template": "{{first_name}} {{last_name}}",
    "display": "both"
}', '0', NULL, '0', '1', '0', '1', '20', 'half', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'contents_campaigns', 'modified_on', 'datetime_updated', 'datetime-updated', NULL, '0', NULL, '0', '1', '0', '1', '21', 'half', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'contents_campaigns', 'name', 'string', 'text-input', '{
    "iconLeft": "fingerprint",
    "trim": true,
    "showCharacterCount": true,
    "formatValue": false,
    "monospace": false
}', '0', NULL, '1', '0', '0', '0', '4', 'half', NULL, 'The display name or tag of the campaign', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'owner', 'owner', 'owner', '{
    "template": "{{first_name}} {{last_name}}",
    "display": "both"
}', '0', NULL, '0', '1', '0', '1', '18', 'half', NULL, NULL, NULL, NULL, NULL), 
	(NULL, 'contents_campaigns', 'page', 'm2o', 'many-to-one', '{
    "icon": "apps",
    "template": "{{name}}",
    "visible_fields": "name,path,headline",
    "placeholder": "Select one page",
    "threshold": "0"
}', '0', NULL, '1', '0', '0', '0', '14', 'half', NULL, 'The page to which this campaign leads', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'slug', 'slug', 'slug', '{
    "placeholder": "URL Safe Representation of Title or Name...",
    "onlyOnCreate": false,
    "forceLowercase": true,
    "mirroredField": "name"
}', '0', NULL, '0', '0', '0', '0', '5', 'half', NULL, 'URL Safe representation of the name', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'start_date', 'datetime', 'datetime', '{
    "iconLeft": "timer",
    "format": "mdy",
    "localized": true,
    "showRelative": false,
    "defaultToCurrentDatetime": true
}', '0', NULL, '1', '0', '0', '0', '8', 'half', NULL, 'This is the date and time the campaign should start', '[]', NULL, NULL), 
	(NULL, 'contents_campaigns', 'status', 'status', 'status', '{
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
}', '0', NULL, '1', '0', '0', '0', '3', 'full', NULL, 'Display Status - only active, online or published rows will be displayed!', NULL, NULL, NULL), 
	(NULL, 'contents_campaigns', 'url', 'string', 'text-input', '{
    "iconLeft": "link",
    "trim": true,
    "showCharacterCount": true,
    "formatValue": false,
    "monospace": false
}', '0', NULL, '0', '0', '0', '0', '11', 'half', NULL, 'The external URL of the campaign - optional if no internal post or page content exists!', '[]', NULL, NULL);

--
-- Insert Collection Options: directus_collection_presets 
--

INSERT INTO `directus_collection_presets` (`id`, `title`, `user`, `role`, `collection`, `search_query`, `filters`, `view_type`, `view_query`, `view_options`, `translation`) 
VALUES 
	(NULL, NULL, NULL, NULL, 'contents_campaigns', NULL, NULL, 'cards', '{
    "tabular": {
        "fields": "name,start_date,page,content",
        "sort": "-start_date"
    }
}', '{
    "tabular": {
        "spacing": "comfortable"
    },
    "cards": {
        "src": "image",
        "title": "name",
        "subtitle": "start_date"
    }
}', NULL);

--
-- Insert Collection Options: directus_relations 
--

INSERT INTO `directus_relations` (`id`, `collection_many`, `field_many`, `collection_one`, `field_one`, `junction_field`) 
VALUES 
	(NULL, 'joins_contents_campaigns_endpoints', 'campaign', 'contents_campaigns', NULL, NULL), 
	(NULL, 'joins_contents_campaigns_endpoints', 'campaign', 'contents_campaigns', 'apis', 'endpoint'), 
	(NULL, 'app_activity', 'item', 'contents_campaigns', 'activity', NULL);