<?php

/*
	Author - PhilleepFlorence
	Description - Collections Compiler, Processor, and Normalizer Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Application\Application;

class Collections 
{
	private static $Format;
	
	/*
		Database Utility
		Similar to GraphQL, the endpoint allows for the configuration of multiple collections in a single response
		Compile multiple collections into a single response object
		ARGUMENTS:
			params - @Array
				collections - collections (names) from app_collections_configuration - use * for all
				process - process the collections after compiling (see process paramters)
				editable - add editable links
		CONFIGURATION:
			name - the unique name of the configuration item
			slug - the URL safe representation of the name
			collection - the collection name in Directus
			filters - additional filters to merge with each collection
				collection: filter
			options - JSON Configuration Object
				params - the query parameters to send to the TableGateway
				compile - the compilation configuration
					key - the key(s) to use to build JSON response (current collection dot syntax path in response)
					value - reduce current value in row, keeps other values intact (rename listed properties)
						object list of properties and fields - properties will be added to the current row
					values - override current row with new properties and values (rename all properties)
						array of fields to add to the returned (uodsated) row
					reduce - override current row with a new property and value (reduces the row to a single value)
						array of fields to choose - the first not null value is chosen
					process - process current row by combining fields and values using the handlebars template engine
						key - property name to create or override
						value - template string
					formats - array of formats to use for formatting (processing)
						format - the format to use to process the value
						field - the field that has the format to use to process the value
						value - the name of the field that has the value for processing
					pluck - pluck or reduce multi-dimension array into linear - set pluck after loading values
					[{
						property: "<new property to create in current row>",
						array: "<property to pluck - must be an array>",
						key: "<path to pluck>"
					}]
					fields - related rows that should also be processed (schema is similar to compile)
					maps - Create a new object from the collection
			server - if collection is for server use only or allowed for public consumption (protects sensitive data)
			
		@return array
	*/
	
	public static function Compile ($params = [], $debug)
	{
		$collections = ArrayUtils::get($params, 'collections') ?: "*";
		$mode = ArrayUtils::get($params, 'mode') ?: "compile";
		$editable = filter_var(( ArrayUtils::get($_REQUEST, 'editable') ), FILTER_VALIDATE_BOOLEAN);
		$filter = filter_var(( ArrayUtils::get($_REQUEST, 'filter') ), FILTER_VALIDATE_BOOLEAN);
		$process = filter_var(( ArrayUtils::get($_REQUEST, 'process') ), FILTER_VALIDATE_BOOLEAN);
		$filters = ArrayUtils::get($_REQUEST, 'filters');
		$now = date('Y-m-d H:m:s');
				
		# Load the configurations for the collections
		
		$tableGateway = Api::TableGateway('app_collections_configuration', null);
		$query = [
			"status" => "published",
			"fields" => "slug,collection,options,server",
			"filter" => [
				"type" => [
					"eq" => $mode
				]
			]
		];
		
		if ($collections !== "*") ArrayUtils::set($query, "filter.slug", ["in" => $collections]);
		
		$collections = $tableGateway->getItems($query);
		$collections = ArrayUtils::get($collections, 'data');
		$collections = Utils::ToArray($collections);
		$response = [
			"meta" => [
				"mode" => "compile",
				"collections" => count($collections),
				"processed" => $process
			],
			"data" => []
		];
		
		# Load collection rows using the options.params property
		
		foreach ($collections as $row)
		{
			$collection = ArrayUtils::get($row, "collection");
			$collection_name = ArrayUtils::get($row, "slug");
			$server = ArrayUtils::get($row, "server");
			$slug = ArrayUtils::get($row, "slug");
			$tableGateway = Api::TableGateway($collection, null);
			$parameter = (array) ArrayUtils::get($row, "options.params"); 
			$fields = ArrayUtils::get($parameter, "fields"); 
			
			if ($debug && ArrayUtils::get($parameter, "status")) ArrayUtils::set($parameter, "status", "draft,published");
			
			if ($fields) ArrayUtils::set($parameter, "fields", implode(',', $fields));
			
			# Add filter for dynamic content filtering
			
			if ($collection_name && $filters && ArrayUtils::get($filters, $collection_name)) {				
				$parameter['filter'] = $parameter['filter'] ?? [];
				$parameter['filter'] = array_merge_recursive($parameter['filter'], ArrayUtils::get($filters, $collection_name));
			}
			
			# Format dynamic dates
			
			array_walk_recursive($parameter, function (&$param) use ($now) {
				if ($param === "NOW()") $param = $now;
			});
			
			$content = [
				"meta" => [],
				"data" => []
			];

			$entries = $tableGateway->getItems($parameter);
			
			# Add editable links - URLs of the items in Directus
			
			if ($editable) $entries = Response::Editable($entries, $collection);
			
			# Add CDN links for files - if applicable
			
			$entries = Response::CDN($entries);
			
			# Meta data from the collections configuration
			
			$entries = ArrayUtils::get($entries, "data");

			ArrayUtils::set($content, "meta.collection", $collection);
			ArrayUtils::set($content, "meta.server", $server);
			ArrayUtils::set($content, "meta.total", count($entries));
			ArrayUtils::set($content, "data", $entries);
			
			ArrayUtils::set($response, "data.{$slug}", $content);
		}
		
		# Return Compiled Data if process is false
		
		if ($filter === true) $response = array_filter_recursive($response);
		
		if ($process === false) return $response;
				
		# Compile JSON response into format Application can use
		
		$output = [];
	    $input = ArrayUtils::get($response, "data");
	    	    	    
	    # Compile $output using $resonse->data (items) and the Collections Configuration
	    
	    foreach ($collections as $item)
	    {
		    $compile = ArrayUtils::get($item, 'options.compile');
		    
		    if (!$compile) continue;
		    
		    $name = ArrayUtils::get($item, "slug");
			$rows = ArrayUtils::get($input, "{$name}.data");
			$meta = ArrayUtils::get($input, "{$name}.meta");
			
			$fields = ArrayUtils::get($compile, "fields") ?: [];
				
			if ($meta) ArrayUtils::set($output, "{$name}.meta", $meta);
						
			# Skip NULL Objects
						
			if (!is_array($rows)) continue;	
			
			# Must be an Array of Objects, not a single Object
			
			if (ArrayUtils::get($rows, 'id')) $rows = [$rows];
									
			if (!ArrayUtils::get($output, "{$name}.data")) ArrayUtils::set($output, "{$name}.data", []);	
			
			# Loop through the rows in the $input
			
			foreach ($rows as $index => $row)
			{
				# Get the keys (from key) to use to build JSON Response
				
				$keys = Collections::Keys($row, ArrayUtils::get($compile, "key"));
				
				# Process formats - if applicable
							
				$formats = ArrayUtils::get($compile, "formats");
				
				$currow = Collections::Format($row, $formats);
				
				# Process value (keep row and update) or values (override row with currow)
				
				$currow = Collections::Reduce($currow, ArrayUtils::get($compile, "reduce"));
				
				$currow = Collections::Value($currow, ArrayUtils::get($compile, "value"), $currow);
				
				$currow = Collections::Value($currow, ArrayUtils::get($compile, "values"), []);
				
				# Process current row by combining columns
							
				$processes = ArrayUtils::get($compile, "process");
				
				$currow = Collections::Process($currow, $processes);
				
				# Pluck or reduce multi-dimension array into linear
							
				$pluck = ArrayUtils::get($compile, "pluck");
				
				# Process the related fields
				
				foreach ($fields as $related)
				{
					$rel_field = ArrayUtils::get($related, "field");
					$rel_property = ArrayUtils::get($related, "property");					
					$rel_rows = ArrayUtils::get($currow, $rel_field);
					$rel_output = [];
					
					if (is_array($rel_rows))
					{
						foreach ($rel_rows as $rel_index => $rel_row)
						{
							if ($rel_property) $rel_row = ArrayUtils::get($rel_row, $rel_property);
							
							# Get the keys (from key) to use to build JSON Response
							
							$rel_keys = Collections::Keys($rel_row, ArrayUtils::get($related, "key"));
							
							# Process formats - if applicable
							
							$formats = ArrayUtils::get($related, "formats");
							
							$rel_currow = Collections::Format($rel_row, $formats);
							
							# Process value (keep row and update) or values (override row with rel_currow)
							
							$rel_currow = Collections::Value($rel_currow, ArrayUtils::get($related, "value"), $rel_currow);
							
							$rel_currow = Collections::Value($rel_currow, ArrayUtils::get($related, "values"), []);	
				
							# Process current row by combining columns
										
							$processes = ArrayUtils::get($related, "process");
							
							$rel_currow = Collections::Process($rel_currow, $processes);						
							
							# Append the processed rel_row to the output related object
							
							if (!is_string($rel_keys)) $rel_keys = $rel_index;
							
							ArrayUtils::set($rel_output, $rel_keys, $rel_currow);
						}
						
						if (count($rel_output)) ArrayUtils::set($currow, $rel_field, $rel_output);
					}
				}
				
				# Create new object from filter if applicable
							
				$maps = ArrayUtils::get($compile, "maps");
				
				$output = Collections::Map($row, $maps, $keys, $output, $currow);
				
				# Append the processed row to the output data and meta object
				
				if (is_string($keys)) ArrayUtils::set($output, "{$name}.data.{$keys}", $currow);
				elseif (is_array( ArrayUtils::get($output, "{$name}.data") )) array_push($output[$name]['data'], $currow);
			}
	    }
	    
	    # Replace object(s) not compiled
	    
	    foreach ($input as $key => $value) 
	    {
		    if (!is_array(ArrayUtils::get($output, $key))) ArrayUtils::set($output, $key, $value);
	    }
	    
	    # Remove empty properties
	    
	    if ($filter === true) $output = array_filter_recursive($output);
	    
	    ArrayUtils::set($response, "data", $output);
		
		return $response;
	}
	
	/*
		Create new object from filter if applicable
		
		@return array
	*/
	
	private static function Filter ($row = [], $filters = NULL)
	{
		if (is_array($filters))	
		{
			foreach ($filters as $filter)
			{
				$filtername = ArrayUtils::get($filter, 'name');
				$currfilter = ArrayUtils::get($filter, 'filter');
				$currkey = ArrayUtils::get($filter, 'key');
				$filtered = 0;
				$filterrow = $currow;
				$currkeys = [];
		
				if (is_array($currkey))
				{
					foreach ($currkey as $filtersindex) 
					{
						$filterscurrindex = ArrayUtils::get($row, $filtersindex);
						
						if (( is_string($filterscurrindex) || is_numeric($filterscurrindex) ) && strlen(strval($filterscurrindex))) array_push($currkeys, strval($filterscurrindex));
					}
					
					$currkeys = implode('.', $currkeys);
					$currkeys = strtolower($currkeys);
				}
				elseif (!$currkey) $currkeys = $keys;
				
				if (is_array($currfilter)) 
				{
					foreach ($currfilter as $filterkey => $filtervalue) 
					{
						if (ArrayUtils::get($row, $filterkey) == $filtervalue) $filtered++;
						elseif (is_array($filtervalue) && in_array(ArrayUtils::get($row, $filterkey), $filtervalue)) $filtered++;
					}
				}
				
				$currvalues = ArrayUtils::get($filter, 'values');
				
				if (is_array($currvalues)) foreach ($currvalues as $a => $b) ArrayUtils::set($filterrow, $a, ArrayUtils::get($currow, $b));
				elseif (is_string($currvalues)) $filterrow = ArrayUtils::get($currow, $currvalues);
										
				if (!empty($currfilter) && $filtered == count($currfilter)) ArrayUtils::set($output, "{$filtername}.{$currkeys}", $filterrow);
			}
		}
	}
	
	/*
		Format the values of a collection against a set rules in the Helper - Format
		
		@return array
	*/
	
	private static function Format ($input = NULL, $formats = NULL)
	{
		if (!is_array($formats) || !is_array($input)) return $input;
		
		Collections::$Format =  Collections::$Format ?: new Format;
		
		foreach ($formats as $row)
		{
			$field = ArrayUtils::get($row, "field");
			$format = ArrayUtils::get($row, "format");
			$value = ArrayUtils::get($row, "value");			
			$input_value = ArrayUtils::get($input, $value);
			
			if (!$format && $field) $format = ArrayUtils::get($input, $field);
			
			if ($input_value && $format && method_exists(Collections::$Format, $format))
			{
				$formatted = Collections::$Format->{$format}($input_value);
				
				ArrayUtils::set($input, $value, $formatted);
			}
		}
		
		return $input;
	}
	
	/*
		Process keys from key array and row
		Omit keys that are not present or null and create dot syntax keys
		
		@return string
	*/
	
	private static function Keys ($row = [], $key = NULL)
	{
		if (is_array($key))
		{
			$keys = [];
			
			foreach ($key as $index) 
			{
				$currindex = ArrayUtils::get($row, $index);
				
				if (( is_string($currindex) || is_numeric($currindex) ) && strlen(strval($currindex))) array_push($keys, strval($currindex));
			}

			$keys = implode('.', $keys);
			$keys = strtolower($keys);
			$keys = preg_replace('/[^\da-z.-]/i', '-', $keys);
			
			return $keys;
		}
		
		return NULL;
	}
	
	/*
		Create new object from filter if applicable
		
		@return array
	*/
	
	private static function Map ($row = [], $maps = NULL, $keys = NULL, $output = [], $filterrow = NULL)
	{
		if (is_array($maps) && $keys)	
		{
			foreach ($maps as $map)
			{
				$name = ArrayUtils::get($map, 'name');
				$filter = ArrayUtils::get($map, 'filter');
				$currkey = ArrayUtils::get($map, 'key');
				$filtered = 0;
				$filterrow = $filterrow ?: $row;
				$currkeys = [];
		
				if (is_array($currkey))
				{
					foreach ($currkey as $filtersindex) 
					{
						$filterscurrindex = ArrayUtils::get($row, $filtersindex);
						
						if (( is_string($filterscurrindex) || is_numeric($filterscurrindex) ) && strlen(strval($filterscurrindex))) array_push($currkeys, strval($filterscurrindex));
					}
					
					$currkeys = array_filter($currkeys);
					
					$currkeys = implode('.', $currkeys);
					$currkeys = strtolower($currkeys);
				}
				elseif (!$currkey) $currkeys = $keys;
				
				if (is_array($filter)) 
				{
					foreach ($filter as $filterkey => $filtervalue) 
					{
						if (ArrayUtils::get($row, $filterkey) == $filtervalue) $filtered++;
						elseif (is_array($filtervalue) && in_array(ArrayUtils::get($row, $filterkey), $filtervalue)) $filtered++;
					}
				}
				
				$currvalues = ArrayUtils::get($filter, 'values');
				
				if (is_array($currvalues)) foreach ($currvalues as $a => $b) ArrayUtils::set($filterrow, $a, ArrayUtils::get($currow, $b));
				elseif (is_string($currvalues)) $filterrow = ArrayUtils::get($currow, $currvalues);
										
				if (!empty($filter) && $filtered == count($filter)) ArrayUtils::set($output, "{$name}.{$currkeys}", $filterrow);
			}
		}
		
		return $output;
	}
	
	/*
		Normalize a JSON Input into SQL for migrations into a collection
		ARGUMENTS:
			params - @Array
				collection - the collection to create or migrate
				fields - Array of Array Objects
					rows - @Array: create one or more rows
						input - field to create
						output - field to create
				json - List of values (properties) to json_decode (incoming JSON Strings)
				where - REQUIRED for Updates (the fields to use when updating)
				update - REQUIRED for Update (the fields to update)
				data - JSON Data Object
				file - Real Path of file to load (useful for larger or sensitive objects)
		debug - return JSON data for debugging
		
		@returns SQL String
	*/
	
	public static function Migrate ($params = [], $debug)
	{
		$collection = ArrayUtils::get($params, 'params.collection');
		$fields = ArrayUtils::get($params, 'params.fields');
		$json = ArrayUtils::get($params, 'params.json');
		$mode = ArrayUtils::get($params, 'params.mode');
		$where = ArrayUtils::get($params, 'params.where');
		$update = ArrayUtils::get($params, 'params.update');
		
		$data = ArrayUtils::get($params, 'data');
		$file = ArrayUtils::get($params, 'file');
		
		if (is_string($file))
		{
			$data = FileSystem::get($file, true);
			$data = ArrayUtils::get($data, 'data');
		}
		
		if (!$collection || !$fields || !$data)
		{
			return [
				"error" => true,
				"message" => Api::Responses('collections.migrate.validation')
			];
		}
		
		# Process JSON Strings and Decode
		
		foreach ($data as &$row)
		{
			foreach ($json as $field)
			{
				$value = ArrayUtils::get($row, $field);
				
				if (!$value) continue; 
				
				$value = stripslashes($value);
				$value = json_decode($value, true);
				
				ArrayUtils::set($row, $field, $value);
			}
		}
		return $data;
		
		# Parse new Object
		
		$items = [];
		
		foreach ($data as $row)
		{			
			foreach ($fields as $rows)
			{
				$currow = [];
				
				foreach ($rows as $field)
				{
					$input = ArrayUtils::get($field, 'input');
					$output = ArrayUtils::get($field, 'output');
					$format = ArrayUtils::get($field, 'format');
					$value = ArrayUtils::get($field, 'value');
					
					if (is_null($value)) $value = ArrayUtils::get($row, $input);
					
					if ($format) $value = ArrayUtils::get($format, $value);
					
					ArrayUtils::set($currow, $output, $value);					
				}
			
				array_push($items, $currow);
			}
		}
		
		# Process Insert or Update
		
		$inserts = [];
		$where = $where ? explode(',', $where) : NULL;
		$update = $update ? explode(',', $update) : NULL;
		
		foreach ($items as $item)
		{
			$fields = array_keys($item);
			$values = array_values($item);
			
			if ($where && $update) 
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
				
				array_push($inserts, "UPDATE `{$collection}` SET {$update_string} WHERE {$where_string};");
			}
			else
			{
				$fields = implode("`, `", $fields);
				$values = implode("', '", $values);
				
				array_push($inserts, "INSERT INTO `{$collection}` (`{$fields}`) VALUES ('{$values}');");
			}
		}
		
		# Process Server Information and Metadata		
		
		$app = Application::getInstance();
		$database = $app->getConfig()->get('database');		
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
			"-- Collection: {$collection}",
			"-- ------------------------------------------------------",
			"-- Server Domain: {$sname}",
			"-- Server Version: {$machine}"
		];
		
		$response = [
			"meta" => $information,
			"data" => $inserts
		];
		
		if ($debug) return $response;
		
		# Dump the SQL for Migration via SQL
		
		header('Content-Type: application/sql');
		
		print implode("\n", $information);
		print "\n\n";
		print implode("\n", $inserts);
		
		die();
	}
	
	/*
		Pluck or reduce multi-dimension array into linear
		
		@return array
	*/
	
	private static function Pluck ($row = [], $pluck = NULL)
	{
		if (is_array($pluck))
		{
			foreach ($pluck as $b) 
			{
				if (!is_array($b)) continue;
				
				$p = ArrayUtils::get($b, 'property');
				$a = ArrayUtils::get($b, 'array');
				$k = ArrayUtils::get($b, 'key');
				
				if (is_array(ArrayUtils::get($row, $a))) ArrayUtils::set($row, $p, ArrayUtils::pick(ArrayUtils::get($row, $a), $k));
			}
		}
		
		return $row;
	}
	
	/*
		Process current row by combining fields and values using the handlebars template engine
		
		@return array
	*/
	
	private static function Process ($row = [], $processes = NULL)
	{		
		if (is_array($processes))
		{
			foreach ($processes as $field => $process) 
			{
				ArrayUtils::set($row, $field, ( Utils::Compile($process, $row) ));
			}			
		}
		
		return $row;
	}
	
	/*
		Process current row with a new property and value (reduces the row to a single value)
		
		@return mixed
	*/
	
	private static function Reduce ($row = [], $reduce = NULL)
	{
		if (is_array($reduce))
		{
			$value = NULL;
			
			foreach ($reduce as $a => $b) 
			{
				$currvalue = ArrayUtils::get($row, $b);
				
				if (!is_null($currvalue)) $value = $currvalue;
			}
			
			return $value;
		}
		
		return $row;
	}
	
	/*
		Process new values in row or create new row with properties of value
		
		@return array
	*/
	
	private static function Value ($row = [], $value = NULL, $currow = [])
	{
		if (is_array($value) && is_array($currow))
		{
			$index = count($currow) === 0;
			
			foreach ($value as $a => $b) 
			{
				$property = $index ? $b : $a;
				
				ArrayUtils::set($currow, $property, ArrayUtils::get($row, $b));
			}
			
			return $currow;
		}
		elseif (is_string($value) && count($currow))
		{
			$currvalue = ArrayUtils::get($currow, $value);
			
			$currvalue = is_null($currvalue) ? "" : $currvalue;
			
			return $currvalue;
		}
		
		return $row;
	}
}