<?php

/*
	Author - PhilleepFlorence
	Description - Authentication Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class Auth 
{
	/*
		Authentication - Confirm
		Locate user via token and authorize for authentication
			
		@return array
	*/
	
	public static function Confirm ($input = [], $debug)
	{
		$form = ArrayUtils::get($input, 'form', []);	
		$formname = ArrayUtils::get($form, 'form') ?: 'credentials';
		$template = ArrayUtils::get($form, 'template') ?: 'user.confirm';
		$token = ArrayUtils::get($form, 'token');
					
		# Check if user exists
		
		$tableGateway = Api::TableGateway("app_users", true);	    
	    $entries = $tableGateway->getItems([
		    "status" => "draft,published",
		    "limit" => 1,
		    "fields" => User::Fields("query"),
		    "filter" => [
			    "reset_token" => [
				    "eq" => $token
			    ]
		    ]
	    ]);
	    
	    $user = ArrayUtils::get($entries, 'data.0', []);
	    $user_id = ArrayUtils::get($user, 'id');
	    
	    # Cancel if no user found
	    
	    if (!$user_id) 
		{
			return [
			    "error" => true,
			    "message" => "Missing or Expired Token! No User Found!"
		    ];
		}
	    
	    # When debugging return the user (parsed) object
	    
	    $user = User::Parse($user);
	    
	    if ($debug) return $user;
	    
	    # Update user and authorize for authentication
	    
	    $updated = $tableGateway->updateRecord($user_id, [
		    "authorized" => 1,
		    "status" => "published"
	    ]);
	    
	    $response = [
		    "meta" => [
			    "collection" => "users",
			    "mode" => "confirm",
			    "success" => true
		    ],
		    "data" => $user		    
	    ]; 
	    
	    # Send information to CRM - if applicable
		
		$crm = Form::CRM($formname, $form);
		
		if (ArrayUtils::get($crm, 'api'))
		{
			$api = ArrayUtils::get($crm, 'api');
		    $post = ArrayUtils::get($crm, 'post');
		    
		    $crm = Curl::Api($api, $post);
		    
		    ArrayUtils::set($response, 'meta.crm', $crm);
		} 
			
		# Check for and send notification using the $template
		
		$template = Mail::Template($template);
		
		if (is_array($template) && ArrayUtils::get($template, 'id'))
		{
			$user = User::Parse($user);
			
			$configuration = Api::Configuration();
			
			$data = [
			    "configuration" => $configuration,
			    "form" => $form,
			    "user" => $user
		    ];
		    
		    $compiled = Mail::Mailbox($user, $data, $template);
		    	    
		    ArrayUtils::set($form, 'subject', ( ArrayUtils::get($form, 'subject') ?: ArrayUtils::get($template, 'title') ));
		    
		    $response['data'][] = $form;
		    
		    $mail = Mail::Send($form, $user, $configuration, $compiled);
		    
		    $error = ArrayUtils::get($mail, 'error');
		    $success = ArrayUtils::get($mail, 'code');
		    $success = $success >= 200 && $success <= 299;
		    
		    if ($error) ArrayUtils::set($response, 'error', $error);
		    
		    ArrayUtils::set($response, 'meta.mail', $mail);
		    ArrayUtils::set($response, 'meta.success', $success);
		}
	    
	    return $response;
	}
	
	/*
		Authentication - Credentials
		Locate user via email for updating credentials
			
		@return array
	*/
	
	public static function Credentials ($input = [], $debug)
	{
		$form = ArrayUtils::get($input, 'form', []);	
		$formname = ArrayUtils::get($form, 'form') ?: 'credentials';
		$template = ArrayUtils::get($form, 'template') ?: 'user.credentials';
		
		$email = ArrayUtils::get($form, 'email');
		$salt = Utils::Hash(true, $email, time());
		$reset_token = Utils::Hash(false, $email, time());
		$update = [
			"reset_token" => $reset_token
		];
			
		# Check if user exists and return user
		
		$tableGateway = Api::TableGateway("app_users", true);	    
	    $entries = $tableGateway->getItems([
		    "status" => "published",
		    "limit" => 1,
		    "fields" => User::Fields("query"),
		    "filter" => [
			    "email" => [
				    "eq" => $email,
				    "logical" => "or"
			    ],
			    "username" => [
				    "eq" => $email,
				    "logical" => "or"
			    ],
			    "authorized" => [
				    "eq" => 1,
				    "logical" => "and"
			    ]
		    ]
	    ]);
	    
	    $user = ArrayUtils::get($entries, 'data.0', []);
	    $user_id = ArrayUtils::get($user, 'id');
	    
	    # Cancel if no user found
	    
	    if (!$user_id) 
		{
			return [
			    "error" => true,
			    "message" => "Missing or Unauthorized User! No User Found!"
		    ];
		}
	    
	    # Update user reset_token with updated token to avoid breaches
	    
	    if (!ArrayUtils::get($user, 'salt')) ArrayUtils::set($update, 'salt', $salt);
	    
	    if ($user_id)
	    {
		    $tableGateway->updateRecord($user_id, $update);
	    }
	    
	    $response = [
		    "meta" => [
			    "collection" => "users",
			    "mode" => "credentials",
			    "success" => false
		    ],
		    "data" => [
			    $user
		    ]		    
	    ];
	    
	    # Set reset link - if applicable
	    
	    $link = ArrayUtils::get($form, 'link');
	    
	    if ($link)
	    {
		    $link = str_ireplace(':token', $reset_token, $link);
		    
		    ArrayUtils::set($user, 'link', $link);
		    ArrayUtils::set($form, 'link', $link);
		    ArrayUtils::set($response, 'meta.link', $link);
	    }    
	    
	    # Send information to CRM - if applicable
		
		$crm = Form::CRM($formname, $form);
		
		if (ArrayUtils::get($crm, 'api'))
		{
			$api = ArrayUtils::get($crm, 'api');
		    $post = ArrayUtils::get($crm, 'post');
		    
		    $crm = Curl::Api($api, $post);
		    
		    ArrayUtils::set($response, 'meta.crm', $crm);
		} 
			
		# Check for and send notification using the $template
		
		$template = Mail::Template($template);
		
		if (is_array($template) && ArrayUtils::get($template, 'id'))
		{
			$user = User::Parse($user);
			
			$configuration = Api::Configuration();
			
			$data = [
			    "configuration" => $configuration,
			    "form" => $form,
			    "user" => $user
		    ];
		    
		    $compiled = Mail::Mailbox($user, $data, $template);
		    	    
		    ArrayUtils::set($form, 'subject', ( ArrayUtils::get($form, 'subject') ?: ArrayUtils::get($template, 'title') ));
		    
		    $response['data'][] = $form;
		    
		    $mail = Mail::Send($form, $user, $configuration, $compiled);
		    
		    $error = ArrayUtils::get($mail, 'error');
		    $success = ArrayUtils::get($mail, 'code');
		    $success = $success >= 200 && $success <= 299;
		    
		    if ($error) ArrayUtils::set($response, 'error', $error);
		    
		    ArrayUtils::set($response, 'meta.mail', $mail);
		    ArrayUtils::set($response, 'meta.success', $success);
		}
	    
	    return $response;
	}
	
	/*
		Authentication - Login
		Authenticate User using username/email address and password and return login_token
			
		@return array
	*/
	
	public static function Login ($input = [], $debug)
	{
		$form = ArrayUtils::get($input, 'form', []);
		
		$formname = ArrayUtils::get($form, 'form') ?: 'login';
		$template = ArrayUtils::get($form, 'template') ?: 'user.login';
		
		$username = ArrayUtils::get($form, 'username');
		$password = ArrayUtils::get($form, 'password');
		
		if (!$username) 
		{
			return [
			    "error" => true,
			    "message" => "Invalid or missing Username!"
		    ];
		}
		
		$tableGateway = Api::TableGateway("app_users", true);
	    $entries = $tableGateway->getItems([
		    "status" => "published",
		    "limit" => 1,
		    "fields" => User::Fields(),
		    "filter" => [
			    "email" => [
				    "eq" => $username,
				    "logical" => "or"
			    ],
			    "username" => [
				    "eq" => $username,
				    "logical" => "or"
			    ],
			    "authorized" => [
				    "eq" => 1,
				    "logical" => "and"
			    ]
		    ]
	    ]);
	    
	    $user = ArrayUtils::get($entries, 'data.0', []);
	    $user_id = ArrayUtils::get($user, 'id');
	    $salt = ArrayUtils::get($user, 'salt');
	    $datetime = date('Y-m-d H:i:s', time());
	    
	    if (!$user_id) 
	    {
		    return [
			    "error" => "Invalid or Unauthorized User - No User Found!"
		    ];
	    }
	    
		$password = Utils::Hash(true, $password, $salt);
		$valid = $password === ArrayUtils::get($user, 'password');
		
		$login_attempts = ArrayUtils::get($user, 'login_attempts') ?: 0;
		
		if (!$valid) 
		{
			if ($login_attempts >= 5)
			{
				return [
				    "error" => true,
				    "attempts" => $login_attempts,
				    "message" => "Too many login attempts!"
			    ];
			}
			
			$tableGateway->updateRecord($user_id, [
				"login_attempts" => $login_attempts + 1,
				"last_failed_login" => $datetime
			]);
			
			return [
			    "error" => "Invalid password!",
			    "attempts" => $login_attempts
		    ];		
		}
		
		$login_token = Utils::Hash(false, time());
		
		/*
			Update record 
		*/
		
		$tableGateway->updateRecord($user_id, [
			"login_attempts" => NULL,
			"login_token" => $login_token,
			"reset_token" => $login_token,
			"last_login" => $datetime,
			"online" => 1
		]);
		
		$user = User::Parse($user);
		
		ArrayUtils::set($user, 'token', $login_token);
		
		$response = [
		    "meta" => [
			    "collection" => "users",
			    "mode" => "login",
			    "success" => true
		    ],
		    "data" => $user
	    ];
		
		/*
			Send information to CRM - if applicable
		*/
		
		$crm = Form::CRM($formname, $form);
		
		if (ArrayUtils::get($crm, 'api'))
		{
			$api = ArrayUtils::get($crm, 'api');
		    $post = ArrayUtils::get($crm, 'post');
		    
		    $crm = Curl::API($api, $post);
		    
		    ArrayUtils::set($response, 'meta.crm', $crm);
		}
		
		/*
			Check for and send notification using the $template
		*/
		
		$template = Mail::Template($template);
		
		if ($template)
		{
			$configuration = Api::configuration();
			
			$data = [
			    "configuration" => $configuration,
			    "form" => $form,
			    "user" => $user
		    ]; 
		    
		    $compiled = Mail::Compile($template, $data);
		    
		    ArrayUtils::set($form, 'subject', ( ArrayUtils::get($form, 'subject') ?: ArrayUtils::get($template, 'title') ));
		    
		    $mail = Mail::Send($form, $user, $configuration, $compiled);
		    
		    ArrayUtils::set($response, 'meta.error', ArrayUtils::get($mail, 'error'));
		    
		    ArrayUtils::set($response, 'meta.mail', $mail);
		}
	
	    return $response;
	}
	
	/*
		Authentication - Logout
		Delete authentication cookies/session and clear out login_token
			
		@return array
	*/
	
	public static function Logout ($input = [], $debug)
	{
		$form = ArrayUtils::get($input, 'form', []);		
		$user_id = ArrayUtils::get($form, 'id');
		
		if (!$user_id) 
		{
			return [
			    "error" => true,
			    "message" => "Invalid or missing User ID!"
		    ];
		}
		
		$tableGateway = Api::TableGateway("app_users", true);
	    $entries = $tableGateway->getItems([
		    "status" => "published",
		    "fields" => User::Fields("query"),
		    "limit" => 1,
		    "filter" => [
			    "id" => [
				    "eq" => $user_id
			    ],
			    "authorized" => [
				    "eq" => 1,
				    "logical" => "and"
			    ]
		    ]
	    ]);
	    
	    $user = ArrayUtils::get($entries, 'data.0');
		
		if (!is_array($user)) 
		{
			return [
			    "error" => true,
			    "message" => "Invalid or Unauthorized User - No User Found!"
		    ];
		}
		
		$user = User::Parse($user);
	    $datetime = date('Y-m-d H:i:s', time());
	
		# Sign off user and clear any applicable sessions!
	    	    
	    $tableGateway->updateRecord($user_id, [
			"login_token" => NULL,
			"last_logout" => $datetime,
			"online" => 0
		]);
		
		return [
		    "meta" => [
			    "collection" => "users",
			    "mode" => "logout",
			    "success" => true
		    ],
		    "data" => $user
	    ];
	}
	
	/*
		Authentication - Reset
		Reset user credentials by confirming email address and salt (or secret question)
			
		@return array
	*/
	
	public static function Reset ($input = [], $debug)
	{
		$form = ArrayUtils::get($input, 'form', []);
	
		$token = ArrayUtils::get($form, 'token');
		$email = ArrayUtils::get($form, 'email');
		
		# Email Address and Reset Token are required!
	
		if (!$token || !$email) 
		{
			return [
				"error" => true,
				"message" => "Missing Email Address and Reset Token!"
			];			
		}
		
		# Make sure the passwords match!
		
		$password = ArrayUtils::get($form, 'password');
	    $confirm_password = ArrayUtils::get($form, 'confirm_password');
	    
	    if ($password !== $confirm_password) 
	    {
		    return [
			    "password" => false,
			    "error" => true,
			    "message" => "Passwords do not match!"
		    ];
	    }
	    
	    # Get the current user via email and token
	    
	    $tableGateway = Api::TableGateway("app_users", true);
	    $entries = $tableGateway->getItems([
		    "limit" => 1,
			"status" => "draft,published",
		    "filter" => [
			    "email" => [
				    "eq" => $email,
				    "logical" => "or"
			    ],
			    "username" => [
				    "eq" => $email,
				    "logical" => "or"
			    ],
			    "reset_token" => [
				    "eq" => $token
			    ]
		    ]
	    ]);
    
		$user = ArrayUtils::get($entries, 'data.0', []);
		$user_id = ArrayUtils::get($user, 'id');
    
	    if (!$user_id) 
	    {
		    return [
			    "error" => true,
			    "message" => "Invalid or missing User ID or Email Address!"
		    ];
	    }
	    
	    $salt = ArrayUtils::get($user, 'salt');
		
		# Optional Secret Question and Answer - for increased security
	
		$secret_question = ArrayUtils::get($form, 'secret_question');
		$secret_answer = ArrayUtils::get($form, 'secret_answer');
		$current_answer = ArrayUtils::get($user, 'secret_answer');
		$secret = $secret_question && $secret_answer;
    
	    if ($secret_question && $secret)
	    {
		    $secret_answer = strval($secret_answer);
		    $secret_answer = Utils::Hash(true, strtolower($secret_answer), $salt);
	    }  
	    
	    if ($secret && $current_answer !== $secret_answer) 
	    {
		    return [
			    "error" => true,
			    "message" => "Invalid Secret Question and Answer! No match!"
		    ];
	    }
	    
	    # Update user and sign user into Application
    
	    $login_token = Utils::Hash(false, time());
	    	    
	    $tableGateway->updateRecord($user_id, [
			"password" => Utils::Hash(true, $password, $salt),
			"login_attempts" => 0,
			"login_token" => $login_token,
			"reset_token" => $login_token,
			"last_login" => date('Y-m-d H:i:s', time()),
			"online" => 1,
			"status" => "published"
		]);
		
		$user = User::Parse($user);
			
		$response = [
		    "meta" => [
			    "collection" => "users",
			    "mode" => "reset",
			    "success" => true
		    ],
		    "data" => $user
	    ];
	    
	    # Send information to CRM - if applicable
		
		$formname = ArrayUtils::get($form, 'form') ?: 'reset';
		$template = ArrayUtils::get($form, 'template') ?: 'user.reset';
		
		$crm = Form::CRM($formname, $form);
		
		if (ArrayUtils::get($crm, 'api'))
		{
			$api = ArrayUtils::get($crm, 'api');
		    $post = ArrayUtils::get($crm, 'post');
		    
		    $crm = Curl::API($api, $post);
		    
		    ArrayUtils::set($response, 'meta.crm', $crm);
		}
			
		/*
			Check for and send notification using the $key
		*/
		
		$template = Mail::Template($template);
		
		if ($template)
		{		
			$configuration = Api::configuration();
			
			$data = [
			    "configuration" => $configuration,
			    "form" => $form,
			    "user" => $user
		    ]; 
		    
		    $compiled = Mail::Mailbox($user, $data, $template);
		    
		    ArrayUtils::set($form, 'subject', ( ArrayUtils::get($form, 'subject') ?: ArrayUtils::get($template, 'title') ));
		    
		    $mail = Mail::Send($form, $user, $configuration, $compiled);
		    
		    ArrayUtils::set($response, 'meta.error', ArrayUtils::get($mail, 'error'));
		    
		    ArrayUtils::set($response, 'meta.mail', $mail);
		}
		
		return $response;
	}
	
	/*
		Authentication - Register
		Add an application user to the Directus DB using email address and full name (and details)
			
		@return array
	*/
	
	public static function Register ($input = [], $debug)
	{
	
		$form = ArrayUtils::get($input, 'form');
		$params = ArrayUtils::get($input, 'params') ?: $form;
		
		$formname = ArrayUtils::get($params, 'form') ?: 'register';
		$template = ArrayUtils::get($params, 'template') ?: 'user.register';
		$link = ArrayUtils::get($params, 'link') ?: 'user.register';
	
		$email = ArrayUtils::get($form, 'email');
		$username = ArrayUtils::get($form, 'username');
		$secret = ArrayUtils::get($form, 'secret_answer');
		$secret = isset($secret) ? strval($secret) : $secret;
		$salt = Utils::Hash(true, $email, time());
    
	    $update = [];
		$reset_token = NULL;
	    $response = [
		    "meta" => [
			    "collection" => "users",
			    "mode" => "register"
		    ],
		    "data" => []
	    ];
	    
	    # Check if the user already exists - via username and email address!
	
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			return [
			    "error" => true,
			    "message" => "Invalid Email Address! Unable to validate your email!"
		    ];
		}
    
	    $tableGateway = Api::TableGateway("app_users", true);	    
	    $entries = $tableGateway->getItems([
		    "status" => "*",
		    "limit" => 1,
		    "fields" => User::Fields("query"),
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
	    
	    $user = ArrayUtils::get($entries, 'data.0') ?: [];
	    $match = ArrayUtils::get($user, 'email') == $email ? "email" : ( ArrayUtils::get($user, 'username') == $username ? "username" : NULL );

		# If user already has a password and is active return message
		
		if ($match)
		{
			return [
			    "error" => true,
			    "message" => "There is an existing user with that '{$match}'!"
		    ];
		}
		
		# Make sure passwords match
    
	    $password = ArrayUtils::get($form, 'password');
	    $confirm_password = ArrayUtils::get($form, 'confirm_password');
	    
	    unset($form['confirm_password']);
	    
	    if ($password !== $confirm_password) 
	    {
		    return [
			    "error" => true,
			    "message" => "Passwords do not match! You must enter the same password twice!"
		    ];
	    }
	    
	    # Set reset_token to be used to confirm user - if applicable
    
	    $reset_token = Utils::Hash(false, $email, $salt);
	    
	    ArrayUtils::set($form, 'reset_token', $reset_token);
	    ArrayUtils::set($form, 'salt', $salt);
	    
	    if (!$username) ArrayUtils::set($form, 'username', User::Username($form));
	    
	    # Set defaults to the user object - activate user if a secret answer is provided
        
	    if ($secret) ArrayUtils::set($form, 'secret_answer', Utils::Hash(true, strtolower($secret), $salt));
	    
	    if ($password) ArrayUtils::set($form, 'password', Utils::Hash(true, $password, $salt));
	    	    
	    if ($secret) ArrayUtils::set($form, "status", "published");
	    
	    $user = User::Parse($form);
	    
	    ArrayUtils::set($response, 'data', $user);
	    
	    # When debugging send back form (insert) and user (parsed) objects
	    
	    if ($debug) 
	    {
		    return [
			    "form" => $form,
			    "user" => $user
		    ];
	    }
	    
	    # Add user to the DB
	    	    
	    $created = $tableGateway->createRecord($form);
	    
	    # Set reset link - if applicable
	    	    
	    if ($link)
	    {
		    $link = str_ireplace(':token', $reset_token, $link);
		    
		    ArrayUtils::set($form, 'link', $link);
		    ArrayUtils::set($response, 'meta.link', $link);
	    }
	    
	    # Send information to CRM - if applicable
	
		$crm = Form::CRM($formname, $form);
		
		if (ArrayUtils::get($crm, 'api'))
		{
			$api = ArrayUtils::get($crm, 'api');
		    $post = ArrayUtils::get($crm, 'post');
		    
		    $crm = Curl::Api($api, $post);
		    
		    ArrayUtils::set($response, 'meta.crm', $crm);
		}
		
		# Check for and send notification using the $template
	
		$template = Mail::Template($template);
		
		if ($template)
		{
			$configuration = Api::configuration();
			
			$data = [
			    "configuration" => $configuration,
			    "form" => $form,
			    "user" => $user
		    ]; 
		    
		    $compiled = Mail::Mailbox($user, $data, $template);
		    	    
		    ArrayUtils::set($form, 'subject', ( ArrayUtils::get($form, 'subject') ?: ArrayUtils::get($template, 'title') ));
		    
		    $mail = Mail::Send($form, $user, $configuration, $compiled);
		    
		    ArrayUtils::set($response, 'meta.error', ArrayUtils::get($mail, 'error'));
		    
		    ArrayUtils::set($response, 'meta.mail', $mail);
		}
		
		ArrayUtils::set($response, 'meta.success', true);
		
		return $response;
	}
}