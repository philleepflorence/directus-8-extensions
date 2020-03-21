<?php

/*
	Author - PhilleepFlorence
	Description - Application User Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class User 
{
	protected static $hidden = [
		'login_token', 
		'password', 
		'reset_token', 
		'secret_answer', 
		'salt'
	];
		
	/*
		Parse and Return Normalized User Object
		ARGUMENTS:
			$user - @Array: Incoming User Data
			
		@return array
	*/
	
	public static function Parse ($user = [])
	{
		if (!$user) return NULL;
		
		$configuration = Api::Configuration();
		
		$first_name = ArrayUtils::get($user, 'first_name');
		$last_name = ArrayUtils::get($user, 'last_name');
		$token = ArrayUtils::get($user, 'login_token');
		$image = ArrayUtils::get($user, 'image');
		
		# Normalize name and token
		
		if ($first_name || $last_name) ArrayUtils::set($user, 'name', "{$first_name} {$last_name}");
		if ($token) ArrayUtils::set($user, 'token', $token);
		
		# Remove Sensitive Credentials
		
		foreach (self::$hidden as $hidden) unset($user[$hidden]);
	    
	    if (is_array($image)) $user = Response::CDN($user);
	    
	    return $user;
	}
	
	/*
		Return the Fields in the App Users Collection that is public
		PARAMTERS:
			mode - @String (public or private)
			
		@return array
	*/
	
	public static function Fields ($mode = "query")
	{
		$fields = [];
		
		switch ($mode)
		{
			case "mail":
				$fields = [
					"id", 
					"first_name", 
					"last_name", 
					"username", 
					"email", 
					"location", 
					"privilege.name", 
					"privilege.description", 
					"privilege.privilege",
					"telephone"
				];
			break;
			
			case "privilege":
				$fields = [
					"id", 
					"status", 
					"username", 
					"privilege"
				];
			break;
			
			case "private":
				$fields = ["*.*.*"];
			break;
			
			case "public":
				$fields = [
					"id", 
					"first_name", 
					"last_name", 
					"username", 
					"email", 
					"location"
				];
			break;
			
			case "query":
				$fields = ["*"];
			break;
		}
		
		return implode(",", $fields);
	}
	
	/*
		User Account - Metadata
		Get all metadata that are related to the authenticated user
		PRARAMETERS:
			form: {
				user: id of user,
				rows: [{...}]
			}
			params: {
				form,
				template
			}
			
		@return array
	*/
	
	public static function Metadata ($params = [], $debug)
	{
		$form = ArrayUtils::get($params, 'form');
		
		# The Application user must be the same user updating the user metadata
		
		$headers = getallheaders();
		$appUser = ArrayUtils::get($headers, 'App-User');
		$user = ArrayUtils::get($form, 'user');
		
		if ($user != $appUser)
		{
			return [
				"error" => true,
				"message" => Api::Responses('user.metadata.validation')
			];
		}
		
		$tableGateway = Api::TableGateway("app_users", true);
		
		# Make sure the user is active and authorized!
		
		$user = $tableGateway->getItems([
			"limit" => 1,
			"fields" => User::Fields ("query"),
			"filter" => [
				"authorized" => 1,
				"id" => $user
			]
		]);
		$user = ArrayUtils::get($user, "data.0");
		
		if (!is_array($user))
		{
			return [
				"error" => true,
				"message" => Api::Responses('user.metadata.authorized')
			];
		}
		
		$tableGateway = Api::TableGateway("app_users_metadata", true);
		$rows = ArrayUtils::get($form, 'rows');
		
		# Process each metadata row as an update or insert
		
		foreach ($rows as &$row)
		{
			$key = ArrayUtils::get($row, 'key');
			
			if (!ArrayUtils::get($row, 'user')) 
			{
				ArrayUtils::set($row, 'user', $appUser);
			}
			
			# The user and key fields must be unique
			
			$existing = $tableGateway->getItems([
				"status" => "*",
				"limit" => 1,
				"fields" => User::Fields ("query"),
				"filter" => [
					"user" => $appUser,
					"key" => $key
				]
			]);
			$existing = ArrayUtils::get($existing, "data.0");
			
			if ($existing)
			{
				$id = ArrayUtils::get($existing, 'id');
				
				ArrayUtils::set($row, 'id', $id);
				
				if (!$debug) $tableGateway->updateRecord($id, $row);
			}
			elseif (!$debug)
			{
				$tableGateway->createRecord($row);
			}
		}
		
		# Send out notifications if applicable
		
		$formname = ArrayUtils::get($params, 'params.form') ?: 'metadata';
		$template = ArrayUtils::get($params, 'params.template') ?: 'user.settings';
		
		$response = [
			"meta" => [
				"collection" => "users",
				"mode" => "metadata",
				"success" => true
			],
			"data" => $rows
		];
		
		$response = Form::Notify($formname, $form, $template, $user, $response);
	    
	    return $response;
	}
	
	/*
		User Account - Notifications
		Process the Notifications Relation Collection (useful for new users) - Email, SMS, or Push!
		PARAMETERS:
			form - form object with a filter for the users collection
			
		@return array
	*/
	
	public static function Notifications ($params = [], $debug)
	{
		$filter = ArrayUtils::get($params, 'form.filter');
		$user_ids = [];
		
		if (!$filter)
		{
			return [
				"error" => true,
				"message" => Api::Responses('user.notifications.filter')
			];
		}
		
		# Get all users
    
	    $tableGateway = Api::TableGateway("app_users", true);
	    $entries = $tableGateway->getItems([
		    "fields" => User::Fields("privilege"),
		    "filter" => $filter,
		    "status" => "published"
	    ]);
	    $users = ArrayUtils::get($entries, 'data');
	        
	    # Get all available notifications
	    
	    $tableGateway = Api::TableGateway("app_notifications", true);
	    $entries = $tableGateway->getItems([
		    "status" => "published"
	    ]);
	    $notifications = ArrayUtils::get($entries, 'data');
    
	    # Add relationship between notifications and users if applicable
	    
	    $tableGateway = Api::TableGateway("joins_app_users_notifications", true);
	    
	    foreach ($users as $user)
	    {
		    $privilege = ArrayUtils::get($user, 'privilege');
		    $user_id = ArrayUtils::get($user, 'id');
		    
		    if (is_null($privilege)) continue;
		    
		    if (!in_array($user_id, $user_ids)) array_push($user_ids, $user_id);
		    
		    foreach ($notifications as $notification)
		    {
			    $currprivilege = ArrayUtils::get($notification, 'privilege');
			    $notification_id = ArrayUtils::get($notification, 'id');
			    
				if ($currprivilege > $privilege) continue;
				
				# Make sure the relationship does not exist
				
				$existing = $tableGateway->getItems([
					"limit" => 1,
					"status" => "draft,published",
				    "filter" => [
					    "user_id" => $user_id,
					    "notification_id" => $notification_id
				    ]
			    ]);
			    
			    if (ArrayUtils::get($existing, 'data.0')) continue;
			    
			    if (!$debug) 
			    {
				    $tableGateway->createRecord([
					    "user_id" => $user_id,
					    "notification_id" => $notification_id
				    ]); 				    
			    }
		    }	    
	    }
	    
	    # Return all notifications that were created
	    
	    $entries = $tableGateway->getItems([
		    "filter" => [
			    "user_id" => [
				    "in" => implode(',', $user_ids)
			    ]
		    ]
	    ]);
	    
	    return [
		    "meta" => [
			    "collection" => "notifications",
			    "mode" => "notifications",
			    "success" => true,
			    "total" => count($entries)
		    ],
		    "data" => $entries
	    ];
	}
	
	/*
		User Account - Settings
		Update the account information for the authenticated user
		PARAMETERS:
			form - user account data
			
		@return array
	*/
	
	public static function Settings ($params = [], $debug)
	{
		$form = ArrayUtils::get($params, 'form.user');
		$user_id = ArrayUtils::get($params, 'form.id');
		$notifications = ArrayUtils::get($params, 'form.notifications');
		
		# The Application user must be the same user updating the user metadata
		
		$headers = getallheaders();
		$appUser = ArrayUtils::get($headers, 'App-User');
		
		if ($user_id != $appUser)
		{
			return [
				"error" => true,
				"message" => Api::Responses('user.settings.validation')
			];
		}
		
		# Validate the email address - username and email must always be included!
	
		$email = ArrayUtils::get($form, 'email');
		$username = ArrayUtils::get($form, 'username');
		
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
		{
			return [
				"error" => true,
				"message" => Api::Responses('user.settings.email')
			];
		}
    
	    # Make sure both passwords match
	    
	    $password = ArrayUtils::get($form, 'password');
	    $confirm_password = ArrayUtils::get($form, 'confirm_password');
	    $secret = ArrayUtils::get($form, 'secret_answer');
	    
	    if ($password !== $confirm_password) 
	    {
		    return [
			    "error" => true,
			    "message" => Api::Responses('user.settings.passwords')
		    ];
	    }
    
		# Get current user and encrypt password and secret answer
    
	    $tableGateway = Api::TableGateway("app_users", true);
	    
	    $user = $tableGateway->getItems([
		    "status" => "draft,published",
		    "limit" => 1,
		    "filter" => [
			    "id" => $user_id,
			    "authorized" => 1
		    ]
	    ]);
	    
	    $user = ArrayUtils::get($user, 'data.0');
	    
	    if (!is_array($user)) 
	    {
		    return [
			    "error" => true,
			    "message" => Api::Responses('user.settings.authorized')
		    ];
	    }
	    
	    $salt = ArrayUtils::get($user, 'salt');
	    
	    ArrayUtils::set($form, 'password', Utils::Hash(true, $password, $salt));
	    
	    if ($secret) ArrayUtils::set($form, 'secret_answer', Utils::Hash(true, strtolower($secret), $salt));
	    
	    # Check for existing user if email or username changed
    
	    $users = $tableGateway->getItems([
		    "status" => "*",
		    "filter" => [
			    "email" => [
				    "eq" => $email,
				    "logical" => "or"
			    ],
			    "username" => [
				    "eq" => $username,
				    "logical" => "or"
			    ]
		    ]
	    ]);
	    
	    $users = ArrayUtils::get($users, 'data');
	    
	    if (is_array($users))
	    {
		    foreach ($users as $curruser)
		    {
			    if ($user_id == ArrayUtils::get($curruser, "id")) continue;
			    
			    $match = ArrayUtils::get($curruser, 'email') == $email ? "email" : ( ArrayUtils::get($curruser, 'username') == $username ? "username" : NULL );
			    
			    return [
				    "error" => true,
				    "message" => str_replace('{{match}}', $match, Api::Responses('user.settings.existing-user'))
			    ];
		    }
	    }
	    
	    # Update the current user 
	    	    
	    $tableGateway->updateRecord($user_id, $form);
	    
	    # Update related notifications collection - if applicable (update only!)
	    
	    if (is_array($notifications))
	    {
		    $tableGateway = Api::TableGateway('joins_app_users_notifications', true);
		    
		    foreach ($notifications as $notification) 
		    {
			    $notification_id = ArrayUtils::get($notification, "id");
			    
			    if ($notification_id) $tableGateway->updateRecord($notification_id, $notification);
		    }
	    }
	    else 
	    {
		    $tableGateway = Api::TableGateway('app_users', true);
	    }
	    
	    # Get the updated user object
	    
	    $user = $tableGateway->getItems([
		    "limit" => 1,
		    "fields" => User::Fields("private"),
		    "filter" => [
			    "id" => $user_id
		    ]
	    ]);
	    $user = ArrayUtils::get($user, 'data.0');
	    
	    $user = User::Parse($user);
				
		$formname = ArrayUtils::get($params, 'params.form') ?: 'settings';
		$template = ArrayUtils::get($params, 'params.template') ?: 'user.settings'; 
		
		$response = [
			"meta" => [
				"collection" => "users",
				"mode" => "settings",
				"notifications" => count($notifications),
				"success" => true
			],
			"data" => $user
		];
	    
	    $form = User::Parse($form);
		
		$response = Form::Notify($formname, $form, $template, $user, $response);
		
		return $response;
	}
	
	/*
		User Account Helper
		Generate Username from User Object
		PARAMETERS:
			$user - @Array: Email Address to parse
	*/
	
	public static function Username ($user = [])
	{
		$email = ArrayUtils::get($user, 'email');
		$first_name = ArrayUtils::get($user, 'first_name');
		$last_name = ArrayUtils::get($user, 'last_name');
		
		$emails = explode('@', $email);
		
		if (count($emails) > 1)
		{
			$username = [reset($emails)];
			$emails = end($emails);
			$emails = explode('.', $emails);
			
			array_push($username, reset($emails));
			
			$username = implode('@', $username);
			
			return $username;			
		}
		elseif ($first_name && $last_name)
		{
			return implode('@', [$first_name, $last_name]);
		}
		elseif ($email)
		{
			return Utils::Hash(false, $email, time());
		}
		
		return Utils::Hash(false, time(), time());
	}
	
	/*
		Get a user from the User Collection via ID
	*/
	
	public static function User ($user_id = 0)
	{
		if (!$user_id) return null;
		
		$tableGateway = Api::TableGateway('app_users', true);	
					
		$entries = $tableGateway->getItems([
			"filter" => [
				"id" => [
					"eq" => $user_id
				]
			]
		]);
	    $entries = ArrayUtils::get($entries, 'data.0');
	    
	    return $entries;
	}
}