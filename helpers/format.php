<?php

/*
	Author - PhilleepFlorence
	Description - Formats and Normalizer Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateUtils;
use Directus\Util\StringUtils;

class Format 
{
	public function Boolean ($input = '')
	{
		$input = filter_var($input, FILTER_VALIDATE_BOOLEAN);
		
		return $input;
	}
	
	public function CSV ($input = '')
	{
		$input = explode(',', $input);
		
		return $input;
	}
	
	public function Date ($input = '', $format = 'M d, Y')
	{
		$input = date($format, strtotime($input));
		
		return $input;
	}
	
	public function Datetime ($input = '', $format = 'M d, Y h:ia')
	{
		$input = date($format, strtotime($input));
		
		return $input;
	}
	
	public function Float ($input = '')
	{
		$input = filter_var($input, FILTER_VALIDATE_FLOAT);
		
		return $input;
	}
	
	public function Integer ($input = '')
	{
		$input = filter_var($input, FILTER_VALIDATE_INT);
		
		return $input;
	}
	
	public function JSON ($input = '')
	{
		if (substr($input, 0, 1) === "{" || substr($input, 0, 1) === "[" || preg_match("/^\d+$/", $input)) return json_decode($input, true);
		
		/*$object = json_decode($input, true);
		
		if (is_int($object)) return $object;
		
		if ($object === false || json_last_error() === JSON_ERROR_NONE) return $input;*/
		Debugger::Log(substr($input, 0, 1), $input);
		
		return $input;
	}
	
	public function Linebreak ($input = '')
	{
		$input = explode(PHP_EOL, $input);
		
		return $input;
	}
	
	public function Plaintext ($input = '')
	{
		$input = strip_tags( str_replace('</p><p>', '</p> <p>', $input) );
		
		return $input;
	}
	
	public function Textarea ($input = '')
	{
		$input = strip_tags(( preg_replace('/<br(\s+)?\/?>/i', "\n", ( str_replace('</p><p>', '</p> <p>', $format_value) )) ));
		
		return $input;
	}
	
	public function Time ($input = '', $format = 'h:i a')
	{
		$input = date($format, strtotime($input));
		
		return $input;
	}
}