<?php

/*
	Author - PhilleepFlorence
	Description - API Configuration Helper Functions
*/

namespace Directus\Custom\Helpers;

use Directus\Application\Application;
use Directus\Database\TableGatewayFactory;

use Directus\Util\ArrayUtils;

use function Directus\base_path;
use function Directus\get_auth_info;

class Api 
{
	protected static $app;
	protected static $container;
	
	protected static $dbConnection;
	protected static $acl;
	
	protected static $configuration;
	
	private static function App ()
	{
		self::$app = Application::getInstance();
	    self::$container = self::$app->getContainer();
	
		self::$dbConnection = self::$container->get('database');
		self::$acl = self::$container->get('acl');
	}
	
	public static function Acl ()
	{
		if (!self::$app) self::App();
		
		return self::$acl;
	}
	
	public static function Connection ()
	{
		if (!self::$app) self::App();
		
		return self::$dbConnection;
	}
	
	/*
		Load Data from App Configuration Collection
		
		@return array
	*/
	
	public static function Configuration ()
	{
		if (self::$configuration) return self::$configuration;
		
		$tableGateway = Api::TableGateway('app_configuration', false);	
		
		$configuration = [];
			
		$entries = $tableGateway->getItems();
	    $entries = ArrayUtils::get($entries, 'data');
	    
	    # Build configuration object into Multi Dimensional Array
	    
	    foreach ($entries as $entry) 
	    {
		    $key = ArrayUtils::get($entry, 'key');
		    $section = ArrayUtils::get($entry, 'section');
		    $value = ArrayUtils::get($entry, 'value');
		    $format = ArrayUtils::get($entry, 'format');
		    
		    if (is_string($value))
		    {
			    switch ($format)
			    {
				    case 'boolean':
				    	$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
				    break;
				    case 'integer':
				    	$value = strip_tags($value);
				    	$value = intval($value);
				    break;
				    case 'json':
				    	$value = json_decode($value, true);
				    break;
				    case 'plaintext':
				    	$value = strip_tags($value);
				    break;
			    }
		    }		    	    
		    
		    ArrayUtils::set($configuration, "{$section}.{$key}", $value);
	    }
	    
	    self::$configuration = $configuration;
	    	    
	    return $configuration;
	}
	
	/*
		Makes sure the Authenticated User is a Super Admin
		ARGUMENTS:
			$token - @String: Super Admin Token
			@admin - @Boolean: Makes sure the authenticated user is an Admin in Directus
	*/
	
	public static function SuperAdmin ($token = NULL, $admin = true)
	{
		$configpath = base_path() . "/config/__api.json";
		
		$config = FileSystem::get($configpath, true);
		
		$super_admin_token = ArrayUtils::get($config, 'super_admin_token');
		
		$authenticated = ( $super_admin_token === $token && is_string($super_admin_token) );
		
		if ($admin === true) $authenticated = $authenticated && get_auth_info('role') == 1;
		
		return $authenticated;
	}
	
	/*
		Directus Database TableGateway Container and Wrapper
		ARGUMENTS:
			$collection - @String: The name of the collection
			$acl - @Boolean: True for update or fetch, false for fetch only
		CREATES:
			A Zend\Db\Adapter\Adapter instance - $dbConnection
			A Directus\Permissions\Acl instance - $acl
			
		@return Directus\Database\TableGateway\RelationalTableGateway
	*/
	
	public static function TableGateway ($collection = null, $update = true)
	{
		if (!$collection) return null;
		
		if (!self::$app) self::App();
		
		$acl = $update !== false ? self::$acl : false;
		
		$tableGateway = TableGatewayFactory::create($collection, [
			"connection" => self::$dbConnection,
			'acl' => $acl
		]);
		
		return $tableGateway;
	}
	
	/*
		Get a configuration value via key path
		ARGUMENTS:
			$find - @String: Dot Syntax Property to find
		
		@return value
	*/
	
	public static function Settings ($find = null)
	{
		$configuration = self::Configuration();
		
		if (!is_string($find)) return null;
		
		return ArrayUtils::get($configuration, $find);
	}
}