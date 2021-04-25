/*
	Atuhor: Philleep Florence.
	Collection Prefixes: App, Contents, and Meta Collections - Join Collections obey the Relation Interface Options.
	Collections: *.
	Fields: status`.
	Extension: Database.Migrate
	Roles: 
		Webmaster
		Administrator
*/

ALTER TABLE `<app_|contents_|data_|meta_>` CHANGE `status` `status`  VARCHAR(10) NOT NULL DEFAULT 'draft' COMMENT 'Content, Task, Role, Permissions Status';