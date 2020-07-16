<?php

/*
	Author - PhilleepFlorence
	Description - Directus Cache Helper Functions
*/

namespace Directus\Custom\Helpers;

use Directus\Application\Application;
use Directus\Mail\Mailer;
use Directus\Mail\Message;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateTimeUtils;

use function Directus\base_path;
use function Directus\generate_uuid4;
use function Directus\get_api_project_from_request;
use function Directus\get_directus_setting;
use function Directus\get_kv_directus_settings;
use function Directus\parse_body;
use function Directus\send_mail_with_template;

class Directus 
{
	/*
		Load configuration and data for the Directus External Script(s)
	*/
	
	public static function App ($params)
	{
		$token = ArrayUtils::get($params, 'super_admin_token');
		$user = ArrayUtils::get($params, 'user');
		
		$headers = getallheaders();
		$domain = Server::Domain();
		$referer = ArrayUtils::get($_SERVER, 'HTTP_REFERER') ?: ArrayUtils::get($headers, 'Referer') ?: $domain;
		
		$admin = Api::SuperAdmin($token, false);
		
		$response = [
			"meta" => [
				"referer" => $referer,
				"admin" => $admin
			],
			"data" => []
		];
		
		if (!$admin && stripos($referer, $domain) === false) 
		{
			http_response_code(401); 
			
			return $response;
		}
		
		# Load Directus Settings
		
		ArrayUtils::set($response, "data.settings", Api::Settings(true));
		
		# Load Directus User Metadata Information
		
		$tableGateway = Api::TableGateway('joins_directus_users_metadata', false);
		$items = $tableGateway->getItems([
			"filter" => [
				"user" => $user
			]
		]);
		$items = ArrayUtils::get($items, "data");
		
		ArrayUtils::set($response, "data.toured", $items);
		
		# Load Directus Collections
		
		$tableGateway = Api::TableGateway('directus_collections', NULL);
		$items = $tableGateway->getItems();
		$items = ArrayUtils::get($items, "data", []);
		
		$collections = $items;
		
		# Load Additional Bookmarks Configuration for Collections
		
		$tableGateway = Api::TableGateway('app_collections_configuration', NULL);
		$items = $tableGateway->getItems([
			"status" => "published",
			"filter" => [
				"slug" => "bookmarks",
				"type" => "module"
			]
		]);
		$items = ArrayUtils::get($items, "data.0.options", []);
		
		if ($items) $collections = array_merge($items, $collections);
		
		ArrayUtils::set($response, "data.collections", $collections);
		
		# Load Tour Options
		
		$tableGateway = Api::TableGateway('app_collections_configuration', NULL);
		$items = $tableGateway->getItems([
			"status" => "published",
			"filter" => [
				"slug" => "tours",
				"type" => "tour"
			]
		]);
		$items = ArrayUtils::get($items, "data.0.options");
		
		ArrayUtils::set($response, "data.tours", $items);
		
		return $response;
	}
	
	/*
		Directus internal mailer to API Users
		ARGUMENTS:
			$params
				template - @String: Filename of twig mail template - src/mail
				subject - @String: Subject sprinf template (project name variable is required!)
				users - @Array: User Array
	*/
	
	public static function Mail ($params)
	{
		$template = ArrayUtils::get($params, 'template', 'user.twig');
		$subject = ArrayUtils::get($params, 'subject');
		$users = ArrayUtils::get($params, 'users');
		$emails = ArrayUtils::get($params, 'emails');
		$sender = ArrayUtils::get($params, 'sender');
		$body = ArrayUtils::get($params, 'body');
		$attachment = ArrayUtils::get($params, 'attachments');
		$from = [];
		
		$container = Application::getInstance()->getContainer();
		$config = $container->get('config');
		
		$maildir = base_path() . '/.cache/' . get_api_project_from_request() . '/mail';
		$mailURL = Server::Host() . '/app/custom/mail/browser?uuid=';
		
		if (is_string($sender))
		{
			$sender = Mail::ParseAddress($sender);			
			
			$from[ $sender['email'] ] = $sender['name'];
			
			$config->set('mail.default.from', $from);
		}
		
		if (is_string($attachment))		
		{
			$attachment = explode(',', $attachment);
			$attachment = array_map('trim', $attachment);
		}
		
		$data = [
	        "request" => Request::Properties(),
	        "settings" => get_kv_directus_settings(),
	        "body" => $body
        ];
        
        $response = [
	        "meta" => [
		        "mode" => "mail",
		        "template" => $template,
		        "message" => Api::Responses('directus.email.error')
	        ],
	        "data" => []	        
        ];
        
        /*
	        Parse Emails and Users and get a Users Array!
        */
                
        if (is_string($emails))
        {
	        $emails = explode(',', $emails);
	        $users = [];
	        
	        foreach ($emails as $email)
	        {		       
		       array_push($users, Mail::ParseAddress($email));
	        }
        }
        elseif ($users)
        {        
	        $tableGateway = Api::TableGateway('directus_users', false);
	        
	        foreach ($users as &$user)
	        {
		        if (!is_numeric($user)) continue;
		        
		        $user = $tableGateway->getItems([ 
			        "filter" => [
				        "id" => $user
			        ] 
		        ]);
		        $user = ArrayUtils::get($user, 'data.0');
	        }	        
        }
        
        /*
	        Send mail to each email address!
        */
                        
        foreach ($users as $user)
        {
	        if (!ArrayUtils::get($user, 'email')) continue;
	        
	        $uuid = generate_uuid4();
	        $mailpath = "{$maildir}/{$uuid}.html";
	        
	        ArrayUtils::set($data, 'form', $params);
	        ArrayUtils::set($data, 'user', $user);				
			ArrayUtils::set($data, 'body', $body);				
			ArrayUtils::set($data, 'browser.url', $mailURL . $uuid);
			
			$mailed = send_mail_with_template($template, $data, function (Message $message) use ($subject, $user, $mailpath) 
			{
		        $message->setSubject(
		            sprintf($subject, get_directus_setting('project_name', ''))
		        );
		        $message->setTo($user['email']);
		        
		        /*
			        Save a copy of the outgoing mail.
			        Allows a user to view the email in a browser.
		        */
		        
		        $contents = $message->getBody();       
		        
		        FileSystem::set($mailpath, $contents);
		    }, 
		    [
			    "attachment" => $attachment,
			    "from" => $from
		    ]); 
		    
		    array_push($response['data'], [
			    "first_name" => ArrayUtils::get($user, 'first_name'),
			    "last_name" => ArrayUtils::get($user, 'last_name'),
			    "email" => ArrayUtils::get($user, 'email'),
			    "uuid" => $uuid,
			    "cache" => $mailpath,
			    "url" => $mailURL . $uuid
		    ]);
        }
        
        ArrayUtils::set($response, 'meta.message', Api::Responses('directus.email.success'));
        ArrayUtils::set($response, 'meta.success', true);
		
		return $response;	
	}
	
	/*
		Post Directus User Metadata - Create Row
	*/
	
	public static function Metadata ($params, $debug = false)
	{
		$user = ArrayUtils::get($params, 'user');
				
		if (!$user) return http_response_code(401); 
		
		$update = [
			"user" => $user,
			"section" => ArrayUtils::get($params, "section"),
			"key" => ArrayUtils::get($params, "key"),
			"value" => ArrayUtils::get($params, "value"),
			"created" => ArrayUtils::get($params, "created", DateTimeUtils::now()->toString())
		];
		
		if ($debug) return $update;
		
		$tableGateway = Api::TableGateway('joins_directus_users_metadata', true);
		$tableGateway->createRecord($update);
		
		return [
			"meta" => [
				"collection" => "joins_directus_users_metadata"
			],
			"data" => $update
		];
	}
}