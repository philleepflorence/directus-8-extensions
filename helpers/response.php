<?php

/*
	Author - PhilleepFlorence
	Description - Response Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class Response 
{	
	/*
		Process uploads relative paths with CDN URL
		ARGUMENTS - $input - @Array
	*/
	
	public static function CDN ($input = [])
	{
		# Update image paths with CDN
	    
	    $protocol = Server::Protocol();
        $domain = $protocol . Server::Domain();
		$cdn = Api::Settings('application.cdn.url') . '/';     

		# Replace /uploads/ with CDN domain

        array_walk_recursive($input, function (&$value, $key) use ($cdn, $domain)
        {
            if (is_string($value) && strpos($value, '/uploads/') === 0) {
	            $value = str_ireplace('/uploads/', $cdn, $value);
            }
        });
        
        return $input;
	}
	
	/*
		Response Utility - Add Editable links to rows in collection where applicable
		ARGUMENTS:
			$input - @Array: Incoming Payload Data Array
			$collection - @String: The Collection Attribute of the Payload
		
		@return array
	*/
	
	public static function Editable ($input = [], $collection)
	{
		$data = ArrayUtils::get($input, 'data');
		
		if (!$collection || !$data) return $input;
		
		$protocol = Server::Protocol();
        $domain = $protocol . Server::Domain(); 
        $id = ArrayUtils::get($row, 'id');
                
        if (!$id && ArrayUtils::get($data, 0)):
        
        	# Multiple rows - get id to form editable link
		
			foreach($data as $key => $row):
			
				$id = ArrayUtils::get($row, 'id');
				
				if ($id) $data[$key]['contenteditable'] = "{$domain}/items/{$collection}/{$id}";
							
			endforeach;
			
		elseif ($id):
		
			# Single row - no need for id when making edits
			
			$data['contenteditable'] = "{$domain}/items/{$collection}/{$id}";
		
		endif;
		
		$input['data'] = $data;
				
		return $input;
	}
}