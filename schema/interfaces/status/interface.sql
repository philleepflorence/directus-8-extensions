/*
	Atuhor: Philleep Florence.
	Collection Prefixes: App, Contents, and Meta Collections - Join Collections obey the Relation Interface Options.
	Collections: Directus Fields.
	Fields: All fields except `id`, `collection`, `field`, `type`, `interface`.
	Extension: Database.Migrate
	Roles: 
		Administrator - All Status Properties
		Moderator - Draft, Pending, Review, Publish 
		Editor - Draft, Pending, Review 
		Contributor - Draft, Pending, Review
	Concepts:
		Directus Field Note - Markdown Reference  and guide for the Interface
		Directus Field Group - One of the pre-defined interface groups that reflect the Nature, Value, and Identity of the Data value
		Directus field Options - Preset options for based on the the context of the collection.	
		Directus Field Translation - Label with a standard template that references the field but has context within the collection.	
*/

# App and Content Collections

UPDATE 
	`directus_fields` 
SET 
	`note`  = '[**Data Field** - *status*](#database-nomenclature \"Concept\") [**Objective** *What is the current state of this item?*](#discovery-interrogative-methods \"Concept\") [**Concept** - *Content: `Publish`, `Draft`. Task: `Pending`, `Review`. Data: `Online`, `Offline`. Item: `Deleted`, `Hidden`*](#status-interface \"Guide\")', 
	`translation`  = '[{\"newItem\":true,\"locale\":\"en-US\",\"translation\":\"Workflow Status\"}]'
WHERE 
	(`field` = 'status' AND `type` = 'status' AND `interface` = 'status') 
	AND 
	(`collection` LIKE 'app_%' AND `collection` NOT LIKE 'contents_%');

# Meta and Join Collections

UPDATE 
	`directus_fields` 
SET 
	`note`  = '[**Objective** *What is the view status of this item?*](#discovery-interrogative-methods "Concept") [**Concept** - *Content: `Active` `Draft`. Access: `Deleted`, `Hidden`*](#status-interface "Guide") [**Dependents** - *`User Role` `Required Fields` `Validated Values`*](#interface-components "Guide") [**Field** - *`status`*](#database-nomenclature "Concept")  
[**Utility** - *`Data` `Configuration` `Settings`*](#database-nomenclature "Concept")',
	`translation`  = '[{\"newItem\":true,\"locale\":\"en-US\",\"translation\":\"Item Status\"}]' 
WHERE 
	(`field` = 'status' AND `type` = 'status' AND `interface` = 'status') 
	AND 
	(`collection` LIKE 'meta_%' AND `collection` NOT LIKE 'joins_%');