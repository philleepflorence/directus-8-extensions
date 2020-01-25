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
	
	public function Datetime ($input = '', $format = 'M d, Y h:i a')
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
		$input = json_decode($input, true);
		
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