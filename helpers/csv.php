<?php

/*
	Author - PhilleepFlorence
	Description - CDN Helper Methods (Uploads Directory or External CDN)
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class CSV 
{
	/*
		Convert CSV String Stream to Associative Array
	*/
	
	public static function Decode ($stream, $options = [])
	{
		$delimeter = ArrayUtils::get($options, 'delimeter') ?: ",";
		$enclosure = ArrayUtils::get($options, 'enclosure') ?: '"';
		$escape = ArrayUtils::get($options, 'escape') ?: "\\";

		try 
		{
			$entries = array_map('str_getcsv', $stream);
			$header = $entries[0];
			
			# Replace human friendly headers to MySQL Safe Column Name!
			
			array_walk($header, function (&$value)
			{
				$value = str_replace(' ', '_', $value);
				$value = strtolower($value);
			});
			
			array_walk($entries, function (&$row) use ($header)
			{
				$row = array_combine($header, $row);
			});
			
			array_shift($entries);
		}
		catch (Exception $e) 
		{
			return [
				"error" => true,
				"message" => $e->getMessage()
			];
		}
		
		return $entries;
	}
	
	/*
		Export DB Collection as CSV
		Read/Write Access is required when passing filter parameters!
		PARAMETERS:
			collection - the collection to load and convert to CSV
			params - the params to pass to the RelationalGateway
			path - the CSV file path (in the CDN) to create and return in response (defaults to download)
			options
				humanize - format the names of the fields to be human readable
		
		@return Array with CSV
	*/
		
	public static function Export ($params = [], $debug)
	{
		$collection = ArrayUtils::get($params, 'collection');
		$parameters = ArrayUtils::get($params, 'params');
		$path = ArrayUtils::get($params, 'path');
		$download = ArrayUtils::get($params, 'download');
		
		$humanize = filter_var(( ArrayUtils::get($_REQUEST, 'options.humanize') ), FILTER_VALIDATE_BOOLEAN);
		
		if (!$collection) return [
			"error" => true,
			"message" => Api::Responses('csv.export.collection')
		];
		
		$realpath = $path ? FileSystem::CDN($path) : NULL;
		
		$response = [
			"meta" => [
				"mode" => "Export",
				"collection" => $collection,
				"path" => $path,
				"download" => $realpath
			]
		];
		
		$tableGateway = Api::TableGateway($collection, NULL);
		$entries = $tableGateway->getItems($parameters);
		$entries = ArrayUtils::get($entries, 'data');
		
		# Convert arrays to JSON String where applicable
		
		foreach ($entries as &$entry)
		{
			foreach ($entry as &$value)
			{
				if (is_array($value)) $value = count($value) ? json_encode($value) : NULL;
			}
		}
		
		if ($debug === true)
		{
			ArrayUtils::set($response, "data", $entries);
			
			return $response;
		}
		
		# Generate CSV data from array - don't create a file, attempt to use memory instead
		
		$fh = fopen('php://temp', 'rw');
    
	    # Write out the headers
	    
	    $headers = array_keys(current($entries));
	    
	    if ($humanize === true)
	    {
		    array_walk($headers, function (&$header)
		    {
			    $header = str_replace('_', ' ', $header);
			    $header = strtolower($header);
		    });
	    }
	    
	    fputcsv($fh, $headers);
	    
	    # Write out the data
	    
	    foreach ($entries as $row) fputcsv($fh, $row);
	    
	    rewind($fh);
	    
	    $csv = stream_get_contents($fh);
	    
	    fclose($fh);
	    
	    # Force download of CSV file if no path is given
	    
	    if (!$path)
	    {
		    header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename={$collection}.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			
			print $csv;
			
			die();
	    }
	    
	    FileSystem::set($realpath, $csv);
	    
	    ArrayUtils::set($response, "meta.success", is_file($realpath));
		
		return $response;
	}
	
	/*
		Import CSV Spreadsheet into the DB
		Read/Write Access is required when creating or updating records!
		PARAMETERS:
			collection - the collection to load and import into the DB
			options
				delimeter - Set the field delimiter (one character only).
				enclosure - Set the field enclosure character (one character only).
				escape - Set the escape character (at most one character). 
					Defaults as a backslash (\) An empty string ("") disables the proprietary escape mechanism.
			File
				CSV File to Import
	*/
	
	public static function Import ($params = [], $debug)
	{
		$collection = ArrayUtils::get($params, 'collection');
		$options = ArrayUtils::get($params, 'options') ?: [];
		$csv = ArrayUtils::get($_FILES, 'csv');
		
		# User friendly error handling
		
		if (!$collection)
		{
			return [
				"error" => true,
				"message" => Api::Responses('csv.import.collection')
			];
		}
		
		# Make sure the user can create or update records in the collection
		
		$tableGateway = Api::TableGateway($collection, true);
		
		# Make sure a file is received
		
		if (!$csv || !ArrayUtils::get($csv, 'tmp_name') || ArrayUtils::get($csv, 'error'))
		{
			return [
				"error" => true,
				"message" => Api::Responses('csv.import.csv')
			];
		}
		
		$entries = CSV::Decode(file(ArrayUtils::get($csv, 'tmp_name')), $options);
		
		if (ArrayUtils::get($entries, "error"))
		{
			return $entries;
		}
				
		$response = [
			"meta" => [
				"mode" => "import",
				"collection" => $collection,
				"total" => count($entries)
			],
			"data" => $entries
		];
				
		if ($debug === true) return $response;
		
		foreach ($entries as $row)
		{
			$id = ArrayUtils::get($row, "id");
			
			if ($id) $tableGateway->updateRecord($id, $row);
			else $tableGateway->createRecord($row);
		}
		
		return $response;
	}
}