<?php

/*
	Author - PhilleepFlorence
	Description - Analytics Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class Analytics 
{
	/*
		Set Analytics!
		Add Analytics and Browser data to the Database
		PARAMETERS:
			params - @Array
				analytics
					{
						"type": "timing",
						"category": "Application Loaded",
						"action": "load",
						"label": "Directus Unit Testing",
						"value": 300,
						"url": "/",
						"ip_address": "192.168.1.0",
						"created": "YYYY-MM-DD HH:mm:ss",
						"page": 1
					}
				browser
					{
						"family": "Chrome",
						"major": "74",
						"minor": "0",
						"operating_system_family": "Mac OS X",
						"operating_system_major": "10",
						"operating_system_minor": "14",
						"device_family": "Other",
						"device_major": "0",
						"device_minor": "0",
						"user_agent": "5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36"
					}
	*/
	
	public static function Set ($params = [], $debug)
	{
		$analytics = ArrayUtils::get($params, 'analytics');
		$browser = ArrayUtils::get($params, 'browser'); 
	    
	    if (!count($analytics) || !count($browser)) 
		{
			return [
				"error" => true,
				"message" => "Analytics and Browser Data Required!"   
		    ];
		}
	    
	    # Get browser row if applicable - insert if not exists!
	    
	    $tableGateway = Api::TableGateway('app_browsers', true);    
	    $browsers = $tableGateway->getItems([
		    "limit" => 1,
		    "filter" => $browser
	    ]); 
	
	    $browser_id = ArrayUtils::get($browsers, 'data.0.id');
	    
	    if ($browser_id) ArrayUtils::set($analytics, 'browser', $browser_id);
	    else
	    {
		    $result = $tableGateway->createRecord($browser);
		       
		    $browsers = $tableGateway->getItems([
			    "limit" => 1,
			    "filters" => $browser
		    ]); 
		    
		    $browser_id = ArrayUtils::get($browsers, 'data.0.id'); 
		    
		    ArrayUtils::set($analytics, 'browser', $browser_id);
	    }
	    	    
	    # Update analytics with browser ID and insert
	    
	    $tableGateway = Api::TableGateway('contents_analytics', true);
	    $result = $tableGateway->createRecord($analytics);
	    
	    return [
		    "meta" => [
			    "debug" => $debug,
			    "mode" => "Analytics"
		    ],
		    "data" => [
			    $analytics,
			    $browser
		    ]
	    ]; 
	}
}