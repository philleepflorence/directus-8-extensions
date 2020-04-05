<?php

/*
	Author - PhilleepFlorence
	Description - Analytics Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

use Zend\Db\Adapter\Adapter;

use function Directus\base_path;
use function Directus\get_api_project_from_request;

class Analytics 
{
	/*
		Internal Helper!
		See Arguments in Analytics::Application 
	*/
	
	private static function Parse ($group, $response, $rows, $sessions, $analytics)
	{
		foreach ($rows as $row)
		{
			$count = intval(ArrayUtils::get($row, 'count'));
			$ratio = $sessions ? round((($count / $sessions) * 100), 2): 0;
			$name = ArrayUtils::get($row, $analytics) ?: 'other';
			$slug = preg_replace("/[^A-Za-z0-9]/", '-', strtolower($name));
			
			ArrayUtils::set($response, "data.{$group}.{$analytics}.{$slug}", [
				"title" => $name,
				"total" => $count,
				"ratio" => $ratio
			]);
		}
		
		return $response;
	}
	
	/*
		Get Application Analytics
		PARAMETERS:
			start_date - the earliest date to load analytics data
			end_date - the latest date to load analytics data
	*/
	
	public static function Application ($params = [], $debug)
	{
		$connection = Api::Connection();
		
		# Build start and end dates
		
		$start_date = ArrayUtils::get($params, 'start_date');
		$end_date = ArrayUtils::get($params, 'end_date', date('Y-m-d H:m:s'));
		
		if (!$start_date)
		{
			$start_date = $connection->query("SELECT MIN(`created`) AS start_date FROM `contents_analytics`;", Adapter::QUERY_MODE_EXECUTE);
			$start_date = $start_date->toArray();
			$start_date = ArrayUtils::get($start_date, '0.start_date');
		}
		
		$response = [
			"meta" => [
				"start_date" => $start_date,
				"end_date" => $end_date
			],
			"data" => [
				
			]
		];
		
		# Get Number of Views to the Application	
		
		$views = $connection->query("SELECT COUNT(*) AS count FROM `contents_analytics` WHERE (action = 'pageview' OR category = 'Application Rendered') AND `created` BETWEEN '{$start_date}' AND '{$end_date}';", Adapter::QUERY_MODE_EXECUTE);
		$views = $views->toArray();
		$views = ArrayUtils::get($views, '0.count');
		$views = intval($views);
		
		ArrayUtils::set($response, 'meta.views', $views);
		
		# Get Number of Visits to the Application	
		
		$sessions = $connection->query("SELECT COUNT(*) AS count FROM `contents_analytics` WHERE `category` = 'Application Loaded' AND `created` BETWEEN '{$start_date}' AND '{$end_date}';", Adapter::QUERY_MODE_EXECUTE);
		$sessions = $sessions->toArray();
		$sessions = ArrayUtils::get($sessions, '0.count');
		$sessions = intval($sessions);
		
		ArrayUtils::set($response, 'meta.total', $sessions);
		
		# Parse the number of Unique Visitors
		
		$visitors = $connection->query("SELECT COUNT(DISTINCT `ip_address`) AS count FROM `contents_analytics` WHERE `category` = 'Application Loaded' AND `created` BETWEEN '{$start_date}' AND '{$end_date}';", Adapter::QUERY_MODE_EXECUTE);
		$visitors = $visitors->toArray();
		$visitors = ArrayUtils::get($visitors, '0.count');
		$visitors = intval($visitors);
		
		ArrayUtils::set($response, 'meta.visitors', $visitors);
		
		# Parse the App Users vs Website Users
		
		$pwas = $connection->query("SELECT COUNT(*) AS count FROM `contents_analytics` WHERE `category` = 'Application Loaded' AND `created` BETWEEN '{$start_date}' AND '{$end_date}' AND `url` LIKE '%source=pwa%';", Adapter::QUERY_MODE_EXECUTE);
		$pwas = $pwas->toArray();
		$pwas = ArrayUtils::get($pwas, '0.count');
		$pwas = intval($pwas);
		
		ArrayUtils::set($response, 'data.sessions', [
			"website" => [
				"total" => ($sessions - $pwas),
				"ratio" => $sessions ? round(((($sessions - $pwas) / $sessions) * 100), 2) : 0
			],
			"pwa" => [
				"total" => $pwas,
				"ratio" => $sessions ? round((($pwas / $sessions) * 100), 2) : 0
			]
		]);
		
		# Parse Performance analytics
		
		$performance = [];
		
		# Parse Performance analytics - Pages
		
		$rows = $connection->query("SELECT COUNT(*) AS count, t2.name AS `page` FROM `contents_analytics` t1 JOIN app_pages t2 ON t2.id = t1.page WHERE (t1.action = 'pageview' OR t1.category = 'Application Rendered') AND t1.created BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY t2.id;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('views', $response, $rows, $views, 'page');
		
		# Parse Performance analytics - View Size
		
		$rows = $connection->query("SELECT COUNT(*) AS count, CONCAT(`view_width`, 'x', `view_height`) AS `viewsize` FROM `contents_analytics` WHERE `category` = 'Application Loaded' AND `created` BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY `view_width`, `view_height`;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('views', $response, $rows, $sessions, 'viewsize');
		
		# Parse Performance analytics - Screen Size
		
		$rows = $connection->query("SELECT COUNT(*) AS count, CONCAT(`screen_width`, 'x', `screen_height`) AS `screensize` FROM `contents_analytics` WHERE `category` = 'Application Loaded' AND `created` BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY `screen_width`, `screen_height`;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('views', $response, $rows, $sessions, 'screensize');
		
		# Parse Performance analytics - app load time
		
		$rows = $connection->query("SELECT ROUND(AVG(`value`)) AS `average`, MIN(`value`) AS `min`, MAX(`value`) AS `max` FROM `contents_analytics` WHERE `category` = 'Application Loaded' AND `created` BETWEEN '2020-02-17 03:12:28' AND '2020-03-04 13:03:22';", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		ArrayUtils::set($response, 'data.performance.loaded', $rows[0]);
		
		# Parse Performance analytics - app download time
		
		$rows = $connection->query("SELECT ROUND(AVG(`value`)) AS `average`, MIN(`value`) AS `min`, MAX(`value`) AS `max` FROM `contents_analytics` WHERE `category` = 'Application Downloaded' AND `created` BETWEEN '2020-02-17 03:12:28' AND '2020-03-04 13:03:22';", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		ArrayUtils::set($response, 'data.performance.downloaded', $rows[0]);
		
		# Parse Performance analytics - app render time
		
		$rows = $connection->query("SELECT ROUND(AVG(`value`)) AS `average`, MIN(`value`) AS `min`, MAX(`value`) AS `max` FROM `contents_analytics` WHERE `category` = 'Application Rendered' AND `created` BETWEEN '2020-02-17 03:12:28' AND '2020-03-04 13:03:22';", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		ArrayUtils::set($response, 'data.performance.rendered', $rows[0]);
		
		# Parse locations analytics
		
		$locations = [];
		$statement = "count(t2.id) AS `count` FROM contents_analytics_locations t1 JOIN `contents_analytics` t2 ON t2.location = t1.id WHERE t2.category = 'Application Loaded' AND t2.created BETWEEN '{$start_date}' AND '{$end_date}'";
		
		# Parse locations - timezones
		
		$rows = $connection->query("SELECT t1.time_zone, {$statement} GROUP BY t1.time_zone;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('locations', $response, $rows, $sessions, 'time_zone');
		
		# Parse locations - countries	
		
		$rows = $connection->query("SELECT t1.country, {$statement} GROUP BY t1.country;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('locations', $response, $rows, $sessions, 'country');	
		
		# Parse locations - regions	
		
		$rows = $connection->query("SELECT t1.region, {$statement} GROUP BY t1.region;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('locations', $response, $rows, $sessions, 'region');	
		
		# Parse browser name analytics
		
		$browser = [];
		$statement = "count(t2.id) AS `count` FROM app_browsers t1 JOIN `contents_analytics` t2 ON t2.browser = t1.id WHERE t2.category = 'Application Loaded' AND t2.created BETWEEN '{$start_date}' AND '{$end_date}'";
		
		# Browser names
		
		$rows = $connection->query("SELECT t1.name, {$statement} GROUP BY t1.name;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('browsers', $response, $rows, $sessions, 'name');
		
		# Browser engine names
		
		$rows = $connection->query("SELECT t1.engine_name, {$statement} GROUP BY t1.engine_name;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('browsers', $response, $rows, $sessions, 'engine_name');
		
		# Browser device vendors
		
		$rows = $connection->query("SELECT t1.device_vendor, {$statement} GROUP BY t1.device_vendor;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('browsers', $response, $rows, $sessions, 'device_vendor');
		
		# Browser device models
		
		$rows = $connection->query("SELECT t1.device_model, {$statement} GROUP BY t1.device_model;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('browsers', $response, $rows, $sessions, 'device_model');
		
		# Browser device types
		
		$rows = $connection->query("SELECT t1.device_type, {$statement} GROUP BY t1.device_type;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('browsers', $response, $rows, $sessions, 'device_type');
		
		# Browser operating system name
		
		$rows = $connection->query("SELECT t1.operating_system_name, {$statement} GROUP BY t1.operating_system_name;", Adapter::QUERY_MODE_EXECUTE);
		$rows = $rows->toArray();
		
		$response = static::Parse('browsers', $response, $rows, $sessions, 'operating_system_name');
		
		return $response;
	}
	
	/*
		Get Dashboard Analytics!
	*/
	
	public static function Dashboard ($params = [], $debug)
	{
		$connection = Api::Connection();
		
		$response = [];
		
		# Get Number of Modules Installed!
		
	    $basepath = base_path();
	    
	    $folder = "{$basepath}/public/extensions/custom/modules";
	    
		if (is_dir($folder))
		{
			$modules = scandir($folder);
			$count = 0;
			
			foreach ($modules as $module)
			{
				if (strrpos($module, '.') === false) $count++;
			}
			
			ArrayUtils::set($response, 'modules.total', $count);
		}
		
		# Get Number of Directus Activity Items	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `directus_activity`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'notifications.total', intval($count));
		
		# Get Number of Directus Settings Items	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `directus_settings`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'settings.total', intval($count));
		
		# Get Number of Files Uploaded	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `directus_files`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'files.total', intval($count));
		
		# Get Number of Collections managed by Directus
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `directus_collections`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'collections.total', intval($count));
		
		# Get Number of Users that can access Directus
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `directus_users`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'users.total', intval($count));
		
		return $response;
	}
	
	/*
		Get Modules Analytics!
	*/
	
	public static function Modules ($params = [], $debug)
	{
		$connection = Api::Connection();
		
		$response = [];
		
		# Get Number of Application Users	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `app_users`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'notifications.total', intval($count));
		
		# Get Number of Directus Collections Configuration	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `app_collections_configuration` WHERE `type` = 'treemap';", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'collections.total', intval($count));
		
		# Get Number of Directus Reports Configuration	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `app_collections_configuration` WHERE `type` = 'reports';", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'reports.total', intval($count));
		
		# Get Number of Directus FAQs	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `contents_faqs` WHERE `application` = 'directus';", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'faqs.total', intval($count));
		
		# Get Number of Visits to the Application	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `contents_analytics` WHERE `category` = 'Application Loaded';", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'analytics.total', intval($count));
		
		# Get Number of Files Uploaded	
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `app_icons`;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'icons.total', intval($count));
		
		# Get Number of Files in the CDN
		
		$files = CDN::Files([ "count" => true ], false);
		
		ArrayUtils::set($response, 'cdn.total', $files);
		
		# Get Number of Collections managed by Directus that are visible
		
		$count = $connection->query("SELECT COUNT(*) AS count FROM `directus_collections` WHERE `hidden` = 0;", Adapter::QUERY_MODE_EXECUTE);
		$count = $count->toArray();
		$count = ArrayUtils::get($count, '0.count');
		
		ArrayUtils::set($response, 'search.total', intval($count));
		
		return $response;
	}
	
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
						"name": "Chrome",
						"version": "79",
						"major": "79.10",
						"operating_system_name": "Mac OS X",
						"operating_system_version": "10.14",
						"engine_name": "Webkit",
						"engine_version": "118.0",
						"device_vendor": "Apple",
						"device_model": "iPad",
						"device_tyoe": "Tablet",
						"user_agent": "5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36"
					}
				location
					{
						"city": "Brooklyn",
						"region": "NY",
						"country": "USA",
						"time_zone": "America/New_York",
						"longitude": "40.178947",
						"latitude": "-194.56897",
						"ip_address": "192.168.1.0"
					}
	*/
	
	public static function Set ($params = [], $debug)
	{
		$analytics = ArrayUtils::get($params, 'analytics');
		$browser = ArrayUtils::get($params, 'browser'); 
		$location = ArrayUtils::get($params, 'location'); 
		 
		$update = ArrayUtils::get($params, 'update'); 
	    
	    if (!count($analytics) || !count($browser) || !count($location)) 
		{
			return [
				"error" => true,
				"message" => Api::Responses('analytics.set.validation')   
		    ];
		}
		
		$ip_address = ArrayUtils::get($analytics, 'ip_address'); 
		$user_agent = ArrayUtils::get($browser, 'user_agent');
	    
	    # Get browser row if applicable - insert if not exists!
	    
	    $tableGateway = Api::TableGateway('app_browsers', true);    
	    $browsers = $tableGateway->getItems([
		    "limit" => 1,
		    "filter" => [
			    "user_agent" => [
				    "eq" => $user_agent
			    ]
		    ]
	    ]); 
	
	    $browser_id = ArrayUtils::get($browsers, 'data.0.id');
	    
	    if ($browser_id) ArrayUtils::set($analytics, 'browser', $browser_id);
	    elseif ($browser_id && $update) $tableGateway->updateRecord($browser_id, $browser);
	    elseif (!$browser_id)
	    {
		    $result = $tableGateway->createRecord($browser);
		    $browser_id = ArrayUtils::get($result, 'data.id');
		    
		    if (!$browser_id)
		    {
			    $browsers = $tableGateway->getItems([
				    "limit" => 1,
				    "filter" => [
					    "user_agent" => [
						    "eq" => $user_agent
					    ]
				    ]
			    ]); 
			    
			    $browser_id = ArrayUtils::get($browsers, 'data.0.id'); 
		    }
		    
		    ArrayUtils::set($analytics, 'browser', $browser_id);
	    }
	    
	    # Get location row if applicable - insert if not exists!
	    
	    $tableGateway = Api::TableGateway('contents_analytics_locations', true);    
	    $locations = $tableGateway->getItems([
		    "limit" => 1,
		    "filter" => [
			    "ip_address" => [
					"eq" => $ip_address
				]
		    ]
	    ]); 
	    
	    $location_id = ArrayUtils::get($locations, 'data.0.id');
	    
	    if ($location_id) ArrayUtils::set($analytics, 'location', $location_id);
	    elseif ($location_id && $update) $tableGateway->updateRecord($location_id, $location);
	    elseif (!$location_id)
	    {
		    $result = $tableGateway->createRecord($location);
		    $location_id = ArrayUtils::get($result, 'data.id');
		    
		    if (!$location_id) 
		    {
			    $locations = $tableGateway->getItems([
				    "limit" => 1,
				    "filter" => [
					    "ip_address" => [
						    "eq" => $ip_address
					    ]
				    ]
			    ]); 
			    
			    $location_id = ArrayUtils::get($locations, 'data.0.id'); 
		    }		    
		    
		    ArrayUtils::set($analytics, 'location', $location_id);
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
			    $browser,
			    $location
		    ]
	    ]; 
	}
}