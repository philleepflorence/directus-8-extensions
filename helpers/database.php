<?php

/*
	Author - PhilleepFlorence
	Description - Database Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Application\Application;
use Zend\Db\Adapter\Adapter;
use Directus\Util\ArrayUtils;

use function Directus\base_path;
use function Directus\get_api_project_from_request;
use function Directus\get_auth_info;
use function Directus\template;

class Database 
{	
	protected static $collections = [
		"directus_activity" => [
			"collection" => "string",
			"item" => "string"
		],
		"directus_collection_presets" => [
			"collection" => "string"
		],
		"directus_collections" => [
			"collection" => "string"
		],
		"directus_fields" => [
			"collection" => "string"
		],
		"directus_permissions" => [
			"collection" => "string"
		],
		"directus_relations" => [
			"collection_many" => "string",
			"collection_one" => "string"
		],
		"directus_revisions" => [
			"collection" => "string",
			"data" => "json",
			"delta" => "json"
		],
		"directus_webhooks" => [
			"collection" => "string"
		]
	];
	
	protected static $fields = [
		"directus_collection_presets" => [
			"view_query" => [
				"where" => "`collection` = '{{collection}}'",
				"update" => "`view_query` = NULL, SET `view_options` = NULL"	
			]
		],
		"directus_fields" => [
			"field" => [
				"where" => "`collection` = '{{collection}}' AND `field` = '{{old_name}}'",
				"update" => "`field` = '{{new_name}}'"
			]
		],
		"directus_relations" => [
			"field_many" => [
				"where" => "`collection_many` = '{{collection}}' AND `field_many` = '{{old_name}}'",
				"update" => "`field_many` = '{{new_name}}'"
			],
			"junction_field" => [
				"where" => "`collection_many` = '{{collection}}' AND `junction_field` = '{{old_name}}'",
				"update" => "`junction_field` = '{{new_name}}'"
			],
			"field_one" => [
				"where" => "`collection_one` = '{{collection}}' AND `field_one` = '{{old_name}}'",
				"update" => "`field_one` = '{{new_name}}'"
			]
		],
		"directus_revisions" => [
			"data" => [
				"where" => "`collection` = '{{collection}}'",
				"update" => "`data` = REPLACE(`data`, '\"{{old_name}}\":', '\"{{new_name}}\":')"
			],
			"delta" => [
				"where" => "`collection` = '{{collection}}'",
				"update" => "`delta` = REPLACE(`delta`, '\"{{old_name}}\":', '\"{{new_name}}\":')"
			]
		]
	];
	
	protected static $schema = [
		"directus_collections" => "`collection` = '{{collection}}'",
		"directus_fields" => "`collection` = '{{collection}}'",
		"directus_permissions" => "`collection` = '{{collection}}'",
		"directus_relations" => "`collection_one` = '{{collection}}' OR `collection_many` = '{{collection}}'"
	];
	
	private static $archive_directory = "/.cache/:project/database/archives";
	private static $archive_path = ":project/database/archives";
	
	private static $backup_directory = "/.cache/:project/database/backups";
	private static $backup_path = ":project/database/backups";
	
	private static $migrations_directory = "/.cache/:project/database/migrations";
	private static $migrations_path = ":project/database/migrations";
	
	private static $schema_zip = "/.cache/:project/database/collections/zip";	
	private static $schema_directory = "/.cache/:project/database/collections";
	private static $schema_path = ":project/database/collections";
	
	private static function Parse ($input = NULL, $type = "value")
	{
		switch ($type)
		{
			case "value":
			
				if (is_array($input) || is_object($input)) 
				{
					$input = json_encode($input);
					$input = str_replace("'", "\'", $input);
				}
				else $input = addslashes($input);
				
			break;
		}
		
		return $input;
	}
	
	/*
		Database Utility
		Archive Directus Activity and Revisions Collections - Requires the Super Admin Token!
		ARGUMENTS:
			params - @Array
				date - only archive rows created before the date - defaults to NOW()
				folder - the directory in which the rows will be stored - defaults to .cache/<project>/database/archives/<date>
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Archive ($params = [], $debug)
	{
		$date = ArrayUtils::get($params, 'date', date('Y-m-d'));
		$folder = ArrayUtils::get($params, 'folder', self::$archive_directory);
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');
				
		# Super Admin Token and Administrative Permissions are required for destructive updates to the Database
		
		if (!Api::SuperAdmin($super_admin_token, false))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.archive.super-admin-token')
			];
		}
		
		$base_path = base_path();
		$project = get_api_project_from_request();
		
		$directory = str_replace(":project", $project, $folder);
		$directory = "{$base_path}{$directory}/{$date}";
		
		# Get the Max ID of Directus Activity - Directus Revisions is linked to directus Activity via Row ID
		
		$connection = Api::Connection();
		$max = $connection->query("SELECT MAX(id) AS id FROM `directus_activity` WHERE `action_on` < '{$date}';", Adapter::QUERY_MODE_EXECUTE);
		$max = $max->toArray();
		$max = ArrayUtils::get($max, '0.id');
		
		$app = Application::getInstance();
		$database = $app->getConfig()->get('database');
		$collections = [
			"directus_activity" => [
				"collection" => "directus_activity",
				"params" => [
					"limit" => -1,
					"filter" => [
						"action_on" => [
							"<" => $date
						]
					]
				]
			], 
			"directus_revisions" => [
				"collection" => "directus_revisions",
				"params" => [
					"limit" => -1,
					"filter" => [
						"id" => [
							"<=" => $max
						]
					]
				]
			]
		];
		
		$machine = [
			php_uname('s'),
			php_uname('r'),
			php_uname('m')
		];
		$machine = implode(' - ', $machine);
		$host = ArrayUtils::get($database, 'host');
		$dbname = ArrayUtils::get($database, 'name');
		$sname = ArrayUtils::get($_SERVER, 'HTTP_HOST') ?: ArrayUtils::get($_SERVER, 'SERVER_NAME');
		
		$response = [
			"meta" => [
				"debug" => $debug,
				"mode" => "archives",
				"collections" => ["directus_activity", "directus_revisions"],
				"directory" => $directory,
				"information" => []
			],
			"data" => []
		];
				
		foreach ($collections as $collection => $row) 
		{
			$statements = [];
			$parameter = $row['params'];
		
			# Get Items
			
			$tableGateway = Api::TableGateway($collection);
			$items = $tableGateway->getItems($parameter);
			$items = ArrayUtils::get($items, 'data');
			$count = count($items);
			
			$information = [
				"-- Directus MySQL Collection Archive",
				"--",
				"-- Host: {$host}    Database: {$dbname}",
				"-- Collection: {$collection}",
				"-- Items: {$count}",
				"-- Archive Date: {$date}",
				"-- ------------------------------------------------------",
				"-- Server Domain: {$sname}",
				"-- Server Version: {$machine}"
			];
			
			ArrayUtils::set($response, "meta.information.{$collection}", $information);
			
			foreach ($items as $item)
			{
				$fields = array_keys($item);
				$values = array_values($item);
				
				array_walk($values, function (&$value)
				{
					$value = Database::Parse($value);
				});
				
				$fields = implode('`, `', $fields);
				$values = implode("', '", $values);
				
				$statement = "INSERT INTO `{$collection}` (`{$fields}`) VALUES ('{$values}');";
				
				array_push($statements, $statement);
			}
			
			$filepath = "{$directory}/{$collection}.sql";
			
			ArrayUtils::set($response, "meta.filepath.{$collection}", $filepath);
			ArrayUtils::set($response, "data.{$collection}", $statements);
			
			if ($debug === false)
			{
				$information = implode("\n", $information);
				$statements = implode("\n", $statements);
		
				$content = "{$information}\n\n\n{$statements}";
		
				$created = Filesystem::set($filepath, $content);
				
				ArrayUtils::set($response, "meta.created.{$collection}", file_exists($filepath));
			}
		}
		
		# Delete rows but do not truncate, keeping the auto-increment
		
		$connection->query("DELETE FROM `directus_activity` WHERE `action_on` < '{$date}';", Adapter::QUERY_MODE_EXECUTE);
		$connection->query("DELETE FROM `directus_revisions` WHERE `id` <= '{$max}';", Adapter::QUERY_MODE_EXECUTE);
		
		return $response;
	}
	
	/*
		Database Utility
		Back up all collections in MySQL or MariaDB as .SQL - Requires the Super Admin Token!
		TODO - Back up Functions and Procedures...
		ARGUMENTS:
			params - @Array
				collections - only export the provided CSV list of collections (defaults to all)
				file - the name of the file to save in the uploads directory (uploads/database/...)
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Backup ($params = [], $debug)
	{
		$collections = ArrayUtils::get($params, 'collections');
		$file = ArrayUtils::get($params, 'file');
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');
		
		$project = get_api_project_from_request();
		
		$app = Application::getInstance();
		$database = $app->getConfig()->get('database');
		
		# Get the Domain, Protocol, and CDN Path
		
		$protocol = Server::Protocol();
        $domain = $protocol . Server::Domain(); 
		$path = str_replace(":project", $project, self::$backup_path);
		$cdn = Api::Settings('application.cdn.url') . '/';
		$path = "{$path}/{$file}.sql";
		$cdn = $cdn . $path;	
		
		$filepath = str_replace(":project", $project, (base_path() . self::$backup_directory));
		$filepath = "{$filepath}/{$file}.sql";
		
		# Super Admin Token and Administrative Permissions are required for destructive updates to the Database
		
		if (!Api::SuperAdmin($super_admin_token))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.backup.super-admin-token')
			];
		}
		
		# Build the information and server comment
		
		$machine = [
			php_uname('s'),
			php_uname('r'),
			php_uname('m')
		];
		$machine = implode(' - ', $machine);
		$host = ArrayUtils::get($database, 'host');
		$dbname = ArrayUtils::get($database, 'name');
		$sname = ArrayUtils::get($_SERVER, 'HTTP_HOST') ?: ArrayUtils::get($_SERVER, 'SERVER_NAME');
		$connection = Api::Connection();
		$information = [
			"-- Directus MySQL Dump",
			"--",
			"-- Host: {$host}    Database: {$dbname}",
			"-- ------------------------------------------------------",
			"-- Server Domain: {$sname}",
			"-- Server Version: {$machine}"
		];
		$contents = [
			implode("\n", $information)
		];
		
		# Get all the collections in the DB - if applicable!
		
		if ($collections)
		{
			$collections = explode(',', $collections);
		}
		else 
		{
			$result = $connection->query('SHOW TABLES', Adapter::QUERY_MODE_EXECUTE);
			$result = $result->toArray();
			
			$collections = [];
			
			foreach ($result as $value) 
			{
				$collection = array_values($value);
				
				$collections = array_merge($collections, $collection);
			}
		}		
		
		# Cycle through all collections and prepare statements
		
		foreach ($collections as $collection)
		{
			# Drop and Create Collection
			
			$result = $connection->query("SHOW CREATE TABLE `{$collection}`;", Adapter::QUERY_MODE_EXECUTE);
			$result = $result->toArray();
			$result = reset($result);
			$result = end($result);
			$result = str_replace("''", "\'", $result);
			$contents[] = "--\n-- Drop Collection: {$collection} \n--";
			$contents[] = "DROP TABLE IF EXISTS `{$collection}`;";
			$contents[] = "--\n-- Create Collection: {$collection} \n--";
			$contents[] = "{$result};";
						
			$result = $connection->query("SELECT * FROM `{$collection}`;", Adapter::QUERY_MODE_EXECUTE);
			$result = $result->toArray();
			
			if (!count($result)) continue;
			
			$fields = reset($result);
			$fields = array_keys($fields);
			$fields = "`" . implode("`, `", $fields) . "`";
			$values = [];
						
			foreach ($result as $row)
			{
				$vals = array_values($row);
				
				array_walk($vals, function (&$val)
				{
					if (isset($val))
					{
						$val = str_replace("'", "\'", $val);
						$val = "'" . $val . "'";
					}
					else
					{
						$val = 'NULL';
					}	
				});
				
				$vals = implode(', ', $vals);
				$values[] = "({$vals})";
			}
			
			$values = implode(", \n\t", $values);
						
			$insert = "INSERT INTO `{$collection}` ({$fields}) \nVALUES \n\t{$values};";
			
			$contents[] = "--\n-- Insert Collection Items: {$collection} \n--";
			
			$contents[] = $insert;
		}		
		
		if ($debug == true) return [
			"meta" => [
				"url" => $cdn,
				"file" => $filepath
			],
			"data" => $contents
		];
		
		# Write the contents to the file provided
		
		FileSystem::Set($filepath, implode("\n\n", $contents));
		
		return [
			"meta" => [
				"url" => $cdn,
				"file" => $filepath,
				"success" => file_exists($filepath)
			],
			"data" => $collections
		];
	}
	
	/*
		Database Utility
		Update the name of a collection in MySQL or MariaDB - Requires the Super Admin Token!
		ARGUMENTS:
			params - @Array
				old_name - the current name of the collection
				new_name - the new name of the collection
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Collection ($params = [], $debug)
	{
		$debug = ArrayUtils::get($params, 'debug');
		$old_name = ArrayUtils::get($params, 'old_name');
		$new_name = ArrayUtils::get($params, 'new_name');
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');
		
		# Make sure the old and new names are not blank and have more than 2 characters!
		
		if ((!$old_name || strlen($old_name) < 3) || (!$new_name || strlen($new_name) < 3))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.collection.missing-name')
			];
		}
		
		# Super Admin Token and Administrative Permissions are required for destructive updates to the Database
		
		if (!Api::SuperAdmin($super_admin_token))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.collection.super-admin-token')
			];
		}
		
		# Make sure the authenticated user can update all collections that will be updated
		
		$acl = Api::Acl();
		
		foreach (self::$collections as $collection => $options)
		{
			if (!$acl->canUpdateAll($collection)) 
			{
				return [
					"error" => true,
					"message" => str_replace('{{collection}}', $collection, Api::Responses('database.collection.update-permission'))
				];
			}
		}
		
		# Prepare statements for updating the name of the collection
		
		$statements = [];
		
		foreach (self::$collections as $collection => $options)
		{
			foreach ($options as $field => $type)
			{
				if ($type === "string") 
				{
					ArrayUtils::set($statements, "{$collection} -> {$field}", "UPDATE `{$collection}` SET `{$field}` = '{$new_name}' WHERE `{$field}` = '{$old_name}';");
				}
				elseif ($type === "json") 
				{
					ArrayUtils::set($statements, "{$collection} -> {$field}", "UPDATE `{$collection}` SET `{$field}` = REPLACE(`{$field}`, '\"collection\":\"{$old_name}\"', '\"collection\":\"{$new_name}\"');");
				}
			}
		}
		
		# Prepare statements for Alter Table Query
		
		ArrayUtils::set($statements, 'alter -> table', "RENAME TABLE `{$old_name}` TO `{$new_name}`;");
		
		if ($debug) return $statements;
		
		# Process Statements
		
		$result = [
			"meta" => [
				"mode" => "collection",
				"total" => count($statements)
			],
			"data" => []
		];
		$connection = Api::Connection();
		
		foreach ($statements as $collection => $statement)
		{
			$connection->query($statement, Adapter::QUERY_MODE_EXECUTE);
			
			array_push($result['data'], [
				"collection" => $collection
			]);
		}
		
		return $result;
	}
	
	/*
		Database Utility
		Update the name of a field in a collection in MySQL or MariaDB - Requires the Super Admin Token!
		NOTE: Directus Collections Preset will have to be manually recreated where applicable
		ARGUMENTS:
			params - @Array
				old_name - the current name of the field
				new_name - the new name of the field
				collection - the name of the collection
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Field ($params = [], $debug)
	{
		$collection = ArrayUtils::get($params, 'collection');
		$old_name = ArrayUtils::get($params, 'old_name');
		$new_name = ArrayUtils::get($params, 'new_name');
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');
		
		# Make sure all parameters are not empty to prevent destructive DB updates!
		
		if (!$collection || !$old_name || !$new_name)
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.field.validation')
			];
		}
		
		# Super Admin Token and Administrative Permissions are required for destructive updates to the Database
		
		if (!Api::SuperAdmin($super_admin_token))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.field.super-admin-token')
			];
		}
		
		# Make sure the authenticated user can update the current collection
		
		$acl = Api::Acl();
		
		if (!$acl->canUpdateAll($collection)) 
		{
			return [
				"error" => true,
				"message" => str_replace('{{collection}}', $collection, Api::Responses('database.field.update-permission'))
			];
		}
		
		# Make sure the authenticated user can update all collections that will be updated
		
		$acl = Api::Acl();
		
		foreach (self::$fields as $currcollection => $options)
		{
			if (!$acl->canUpdateAll($currcollection)) 
			{
				return [
					"error" => true,
					"message" => str_replace('{{collection}}', $currcollection, Api::Responses('database.field.update-permission'))
				];
			}
		}
		
		# Prepare statements for updating the name of the collection
		
		$statements = [];
		
		foreach (self::$fields as $currcollection => $collections)
		{
			foreach ($collections as $field => $row)
			{
				$update = template($row['update'], $params);
				$where = template($row['where'], $params);
				
				ArrayUtils::set($statements, "{$currcollection} -> {$field}", "UPDATE `{$currcollection}` SET {$update} WHERE {$where};");
			}
		}
		
		# Load the Schema for the field
		
		$tableGateway = Api::TableGateway($collection, true);
		$schema = $tableGateway->getField($old_name, $collection);
		$column_type = ArrayUtils::get($schema, 'column_type');
		$default_value = ArrayUtils::get($schema, 'default_value');
		$comment = ArrayUtils::get($schema, 'note');
		
		$comment = $comment ? "'{$comment}'" : 'NULL';
		$default_value = $default_value ? "'{$default_value}'" : 'NULL';
		
		# Process the Alter Table Statement from the Schema
		
		ArrayUtils::set($statements, "Alter -> table", "ALTER TABLE `{$collection}` CHANGE `{$old_name}` `{$new_name}` {$column_type} DEFAULT {$default_value} COMMENT {$comment};");
				
		if ($debug) return $statements;
		
		# Process Statements
		
		$result = [
			"meta" => [
				"mode" => "field",
				"total" => count($statements)
			],
			"data" => []
		];
		$connection = Api::Connection();
		
		foreach ($statements as $collection => $statement)
		{
			$connection->query($statement, Adapter::QUERY_MODE_EXECUTE);
			
			array_push($result['data'], [
				"collection" => $collection
			]);
		}
		
		return $result;
	}
	
	/*
		Database Utility
		Creates or Deletes an index for fields in a collection in MySQL or MariaDB - Requires the Super Admin Token!
		ARGUMENTS:
			params - @Array
				collection - the name of the collection to update
				field - the name of the field to index (CSV if more than one field)
				options - @Array
					name - the name of the index (should start with idx_)
					type - index, unique, primary_key, delete
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Index ($params = [], $debug)
	{
		$collection = ArrayUtils::get($params, 'collection');
		$field = ArrayUtils::get($params, 'field');
		$options = ArrayUtils::get($params, 'options');
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');
		
		# Make sure all parameters are not empty to prevent destructive DB updates!
		
		if (!$collection || !$field || !$options)
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.index.validation')
			];
		}
		
		# Super Admin Token and Administrative Permissions are required for destructive updates to the Database
		
		if (!Api::SuperAdmin($super_admin_token))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.index.super-admin-token')
			];
		}
		
		# Make sure the authenticated user can update all collections that will be updated
		
		$acl = Api::Acl();
		
		if (!$acl->canUpdateAll($collection)) 
		{
			return [
				"error" => true,
				"message" => str_replace('{{collection}}', $collection, Api::Responses('database.index.update-permission'))
			];
		}
		
		# Prepare statement
		
		$field = explode(',', $field);
		$field = "`" . implode('`,`', $field) . "`";
		
		$name = ArrayUtils::get($options, 'options.name');
		$type = ArrayUtils::get($options, 'options.type');
		
		if ($type === "delete") $statement = "ALTER TABLE `{$collection}` DROP INDEX `{$name}`;";
		elseif ($type === "primary_key") $statement = "ALTER TABLE `{$collection}` ADD PRIMARY KEY ({$field});";
		elseif ($type === "index") $statement = "ALTER TABLE `{$collection}` ADD INDEX `{$name}` ({$field});";
		elseif ($type === "unique") $statement = "ALTER TABLE `{$collection}` ADD CONSTRAINT `{$name}` UNIQUE KEY ({$field});";
		
		if ($debug) return $statement;
		
		# Process Statement
		
		$connection = Api::Connection();
		$updated = $connection->query($statement, Adapter::QUERY_MODE_EXECUTE);
		
		$result = [
			"meta" => [
				"mode" => "index"
			],
			"data" => [
				[
					"collection" => $collection,
					"field" => $field,
					"name" => $name,
					"type" => $type
				]
			]
		];
		
		return $result;
	}
	
	/*
		Database Utility
		Migrate the data and/or schema of a database collection 
		- useful for updating collections and databases.
		- Requires the Super Admin Token!
		ARGUMENTS:
			params - @Array
				collection - the name of the collection to update
				params - parameters to pass to the Gateway
				path - path to save the SQL within .cache/app/migrations/...
				options - @Array
					where - the field to use for the updates
					update - the fields to update
					alter - the fields that need to be altered in the collection
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Migrate ($params = [], $debug)
	{
		$collection = ArrayUtils::get($params, 'collection');
		$parameters = ArrayUtils::get($params, 'params');
		$path = ArrayUtils::get($params, 'path');
		$note = ArrayUtils::get($params, 'note');
		$where = ArrayUtils::get($params, 'options.where');
		$update = ArrayUtils::get($params, 'options.update');
		
		# Validate SUper Admin Token
		
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');

		if (!Api::SuperAdmin($super_admin_token, false))
		{
			return [
				"error" => true,
				"message" => Api::Responses('database.index.super-admin-token')
			];
		}		
		
		$app = Application::getInstance();
		$database = $app->getConfig()->get('database');
		
		# Build the information and server comment
		
		$machine = [
			php_uname('s'),
			php_uname('r'),
			php_uname('m')
		];
		$machine = implode(' - ', $machine);
		$host = ArrayUtils::get($database, 'host');
		$dbname = ArrayUtils::get($database, 'name');
		$sname = ArrayUtils::get($_SERVER, 'HTTP_HOST') ?: ArrayUtils::get($_SERVER, 'SERVER_NAME');
		$connection = Api::Connection();
		$information = [
			"-- Directus MySQL Collection Migration",
			"--",
			"-- Host: {$host}    Database: {$dbname}",
			"-- Migration: " . str_replace("/", " - ", $path),
			"-- Note: {$note}",
			"-- ------------------------------------------------------",
			"-- Server Domain: {$sname}",
			"-- Server Version: {$machine}"
		];
		
		$base_path = base_path();
		$directory = Database::$migrations_directory;
		$project = get_api_project_from_request();
		$realpath = str_replace(":project", $project, "{$base_path}{$directory}/{$path}");
		
		# Get Items
		
		$tableGateway = Api::TableGateway($collection);
		$items = $tableGateway->getItems($parameters);
		$items = ArrayUtils::get($items, 'data');
		$migrations = [];
		
		# Process Migrations Statements
		
		$where = $where ? explode(',', $where) : NULL;
		$update = $update ? explode(',', $update) : NULL;
		
		foreach ($items as $item)
		{
			if (is_array($where) && is_array($update)) 
			{
				$update_fields = [];
				$where_fields = [];
				
				foreach ($item as $field => $value)
				{
					$value = Database::Parse($value);

					if (in_array($field, $where)) array_push($where_fields, "`{$field}` = '{$value}'");
					elseif (in_array($field, $update)) array_push($update_fields, "`{$field}` = '{$value}'");
				}
				
				$update_string = implode(', ', $update_fields);
				$where_string = implode(' AND ', $where_fields);
				
				$statement = "UPDATE `{$collection}` SET {$update_string} WHERE {$where_string};";
			}
			else 
			{
				$fields = array_keys($item);
				$values = array_values($item);
				
				array_walk($values, function (&$value)
				{
					$value = Database::Parse($value);
				});
				
				$fields = implode('`, `', $fields);
				$values = implode("', '", $values);
				
				$statement = "INSERT INTO `{$collection}` (`{$fields}`) VALUES ('{$values}');";
			}
			
			array_push($migrations, $statement);
		}
		
		$response = [
			"meta" => [
				"mode" => "migrations",
				"total" => count($migrations),
				"path" => $realpath,
				"information" => $information
			],
			"data" => $migrations
		];
		
		if ($debug === true) return $response;
		
		$information = implode("\n", $information);
		$statements = implode("\n", $migrations);
		
		$statements = "{$information}\n\n\n{$statements}";
		
		Filesystem::set($realpath, $statements);
		
		return $response;
	}
	
	/*
		Database Utility
		Export Collections Schema in MySQL or MariaDB as .SQL - Requires the Super Admin Token!
		Useful for migrating collections to an existing Directus Instance.
		Rows should be exported via the main Export Endpoint to avoid clashes!
		ARGUMENTS:
			params - @Array
				collections - only export the provided CSV list of collections
				download - print: display schema instead of saving
					zip: download schema as a zip archive
				super_admin_token - super admin token (prevents non admins from changing DB Schema)
			
		@return array
	*/
	
	public static function Schema ($params = [], $debug)
	{
		$collections = ArrayUtils::get($params, 'collections');
		$download = ArrayUtils::get($params, 'download');
		$super_admin_token = ArrayUtils::get($params, 'super_admin_token');	
		
		$app = Application::getInstance();
		$database = $app->getConfig()->get('database');
		
		# Get the Domain, Protocol, and CDN Path
		
		$protocol = Server::Protocol();
        $domain = $protocol . Server::Domain();
        $project = get_api_project_from_request(); 
		$cdn = Api::Settings('application.cdn.url') . '/';
		
		# Build the information and server comment
		
		$machine = [
			php_uname('s'),
			php_uname('r'),
			php_uname('m')
		];
		$machine = implode(' - ', $machine);
		$host = ArrayUtils::get($database, 'host');
		$dbname = ArrayUtils::get($database, 'name');
		$sname = ArrayUtils::get($_SERVER, 'HTTP_HOST') ?: ArrayUtils::get($_SERVER, 'SERVER_NAME');
		$connection = Api::Connection();
		$information = [
			"-- Directus MySQL Collection Schema and Options Dump",
			"--",
			"-- Host: {$host}    Database: {$dbname}",
			"-- ------------------------------------------------------",
			"-- Server Domain: {$sname}",
			"-- Server Version: {$machine}"
		];
		
		# Get all the collections in the DB - if applicable!
		
		if ($collections)
		{
			$collections = explode(',', $collections);
		}
		else 
		{
			$result = $connection->query('SHOW TABLES', Adapter::QUERY_MODE_EXECUTE);
			$result = $result->toArray();
			
			$collections = [];
			
			foreach ($result as $value) 
			{
				$collection = array_values($value);
				
				$collections = array_merge($collections, $collection);
			}
		}
				
		foreach ($collections as $collection)
		{
			# Create Table
			
			$result = $connection->query("SHOW CREATE TABLE `{$collection}`;", Adapter::QUERY_MODE_EXECUTE);
			$result = $result->toArray();
			$result = reset($result);
			$result = end($result);
			$result = str_replace("''", "\'", $result);
			
			$contents[$collection] = [
				implode("\n", $information)
			];
			
			$contents[$collection][] = "--\n-- Create Collection: {$collection} \n--";
			$contents[$collection][] = "{$result};";
			
			$contents[$collection][] = "--\n-- Reset Collection: {$collection} \n--";
			$contents[$collection][] = "ALTER TABLE `{$collection}` AUTO_INCREMENT = 1;";
			
			# Get rows from the options for the current collection
			
			foreach (self::$schema as $currcollection => $where)
			{
				$where = template($where, [
					"collection" => $collection
				]);
				
				$result = $connection->query("SELECT * FROM `{$currcollection}` WHERE {$where};", Adapter::QUERY_MODE_EXECUTE);
				$result = $result->toArray();
				
				if (!count($result)) continue;
				
				# Remove the primary ID value to avoid clashes with existing directus instance
				
				array_walk_recursive($result, function (&$value, $key)
				{
					if ($key === 'id') $value = NULL;
				});
			
				$fields = reset($result);
				$fields = array_keys($fields);
				$fields = "`" . implode("`, `", $fields) . "`";
				$values = [];
							
				foreach ($result as $row)
				{
					$vals = array_values($row);
					
					array_walk($vals, function (&$val)
					{
						if (isset($val))
						{
							$val = str_replace("'", "\'", $val);
							$val = "'" . $val . "'";
						}
						else
						{
							$val = 'NULL';
						}	
					});
					
					$vals = implode(', ', $vals);
					$values[] = "({$vals})";
				}
				
				$values = implode(", \n\t", $values);
							
				$insert = "INSERT INTO `{$currcollection}` ({$fields}) \nVALUES \n\t{$values};";
				
				$contents[$collection][] = "--\n-- Insert Collection Options: {$currcollection} \n--";
				
				$contents[$collection][] = $insert;
			}
			
			$contents[$collection] = implode("\n\n", $contents[$collection]);
		}
		
		$directory = base_path() . self::$schema_directory;
		$directory = str_replace(':project', $project, $directory);
		
		$zip_directory = base_path() . self::$schema_zip;
		$zip_directory = str_replace(':project', $project, $zip_directory);
		$zip_path = "{$zip_directory}/schema.zip";
		
		$url = $cdn . self::$schema_path;
		$url = str_replace(':project', $project, $url);
		
		if ($debug) 
		{
			return [
				"meta" => [
					"directory" => $directory,
					"zip" => $download === "zip" ? $zip_path : NULL
				],
				"data" => $contents
			];			
		}
		
		if ($download === "print")
		{
			print implode("\n\n", $contents);
			
			die();
		}
		
		$result = [
			"meta" => [
				"directory" => $directory,
				"url" => $url
			],
			"data" => []
		];
		
		$filepaths = [];
		
		foreach ($contents as $collection => $content)
		{
			# Write contents to file in the database collections directory
			
			$filepath = "{$directory}/{$collection}.sql";
			$fileurl = "{$url}/{$collection}.sql";
			
			array_push($filepaths, $filepath);
					
			FileSystem::Set($filepath, $content);
			
			$result['data'][] = [
				"filepath" => $filepath,
				"url" => $fileurl,
				"success" => file_exists($filepath)
			];
		}
		
		if ($download === "zip" && count($filepaths)) FileSystem::Download($filepaths, $zip_path);
					
		return $result;
	}
}