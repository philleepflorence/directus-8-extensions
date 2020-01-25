<?php

/*
	Author - PhilleepFlorence
	Description - Styles Variables Helper Functions
*/

namespace Directus\Custom\Helpers;

use Directus\Util\ArrayUtils;

class Styles 
{
	protected static $css = "styles/variables.css";
	protected static $less = "styles/variables.less";
	
	/*
		Normalize Collection Rows as CSS Variables
		
		@return array
	*/
	
	public static function CSS ($style = true, $debug = false)
	{
		$colors = Styles::Colors();
	    $response = [
			"style" => [
				"/* !CSS Property Definitions - Accent Colors */ \n\n",
				":root { \n"
			]
	    ];
	    
	    # Build Color Definitions
		
		foreach ($colors as $row)
	    {
		    $name = $row['name'];
		    $color = $row['color'];
		    $text = $row['text_color'];
		    $gradient = $row['gradient_color'];
		    $variable = $row['css_variable'];
		    
		    # Set the CSS Color Variables
		    
		    if ($style === true)
		    {
			    array_push($response['style'], "\t{$variable}: {$color}; \n");
			    array_push($response['style'], "\t{$variable}-text: {$text}; \n");
			    array_push($response['style'], "\t{$variable}-gradient: {$gradient}; \n");
		    }
	    }
	    
	    if ($style === true) array_push($response['style'], "}");	
	    
	    $variables = ArrayUtils::get($response, 'style');
	    $variables = implode('', $variables);    
	    
	    ArrayUtils::set($response, 'style', $variables);
			    	    
	    if ($debug === true) return $response;
	    	    
	    if ($variables) Styles::Write($variables, 'css');
	    
	    return $response;
	}
	
	/*
		Normalize Collection Rows as LESS Variables
		
		@return array
	*/
	
	public static function LESS ($style = true, $debug = false)
	{
		$colors = Styles::Colors();
	    $response = [
		    "definitions" => [],
			"array" => [],
			"style" => [
				"/* !Bootstrap Color Definitions - Accent Colors */ \n\n"
			]
	    ];
    
	    # Build Color Definitions
		
		foreach ($colors as $row)
	    {
		    $name = $row['name'];
		    $color = $row['color'];
		    $text = $row['text_color'];
		    $gradient = $row['gradient_color'];
		    
		    # Set the text colors for light and dark backgrounds
		    
		    if ($name === 'light') array_push($response['definitions'], "@colortextdark: {$text};");
		    else if ($name === 'dark') array_push($response['definitions'], "@colortextlight: {$text};");
		    else if ($name === 'primary') array_push($response['definitions'], "@colortextprimary: {$text};");
		    else if ($name === 'secondary') array_push($response['definitions'], "@colortextsecondary: {$text};");
		    
		    array_push($response['definitions'], "@color{$name}: {$color};");
		    array_push($response['array'], "{$name} {$color} {$text} {$gradient}");
	    }
	    
	    if ($style === true)
	    {
		    foreach ($response['definitions'] as $row) array_push($response['style'], "{$row} \n");
		    
		    $imploded = implode(",\n\t", $response['array']);
		    
		    array_push($response['style'], "\n\n");
		    array_push($response['style'], "/* !Bootstrap Color override Loop - Colors must have a definition above @color{color} */ \n\n");
		    array_push($response['style'], "@bootstrapcolors: \n\t");
		    array_push($response['style'], $imploded);
		    array_push($response['style'], "; \n");
	    }	
	    
	    $variables = ArrayUtils::get($response, 'style');
	    $variables = implode('', $variables);    
	    
	    ArrayUtils::set($response, 'style', $variables);
			    	    
	    if ($debug === true) return $response;
	    	    
	    if ($variables) Styles::Write($variables, 'less');	    		
	    	    
	    return $response;
	}
	
	/*
		Loader Helper Function
	*/
	
	public static function Colors ()
	{
		$tableGateway = Api::TableGateway('app_colors', false);
	    $colors = $tableGateway->getItems([
		    "status" => "published"
	    ]);
	    $colors = ArrayUtils::get($colors, 'data');
	    
	    return $colors;
	}
	
	/*
		Write the variables to file in the CDN
	*/
	
	public static function Write ($style = '', $type = 'less')
	{
		$path = $type === 'less' ? Styles::$less : Styles::$css;
		
		if (is_array($style)) implode('', $style);
		
		$filepath = FileSystem::CDN($path);
		    
		FileSystem::Set($filepath, $style);
		
		return file_exists($filepath);
	}
}