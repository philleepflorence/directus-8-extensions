<?php

/*
	Author - PhilleepFlorence
	Description - Mailer Helper Functions
*/

namespace Directus\Custom\Helpers;

include_once(dirname(__DIR__) . "/vendor/handlebars/src/Handlebars/Autoloader.php");
include_once(dirname(__DIR__) . "/vendor/PHPMailer/src/Exception.php");
include_once(dirname(__DIR__) . "/vendor/PHPMailer/src/PHPMailer.php");
include_once(dirname(__DIR__) . "/vendor/PHPMailer/src/SMTP.php");

\Handlebars\Autoloader::register();

use Directus\View\JsonView;

use Directus\Util\ArrayUtils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Handlebars\Handlebars;

use function Directus\get_directus_setting;

class Mail 
{
	/*
		Compile Email Template
		ARGUMENTS:
			$input - @Array: Collection Data from App Templates
			$data - @rray: Form Data to use to compile Template
		DEPENDENTS:
			Handlebars
			
		@return array
	*/
	
	public static function Compile ($input = [], $data = [])
	{
		$template = ArrayUtils::get($input, 'template');
	    $layout = ArrayUtils::get($input, 'layout.template') ?: ArrayUtils::get($input, 'layout');
	    $headers = ArrayUtils::get($data, 'headers');
	    $headers = $headers ?: getallheaders();
	    
	    if (!$template) return $input;
	    
	    $engine = new Handlebars;
	    
	    # Compile the template and layout using Handlebars
	    
	    ArrayUtils::set($data, 'headers', $headers);
	    
	    $template = $engine->render($template, $data);
	    $plaintext = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('><', '> <', $template))));
	    
	    $data = array_merge($data, [
		    "body" => [
			    "html" => $template,
			    "text" => $plaintext
		    ]
	    ]);
	    
	    $body = $layout ? $engine->render($layout, $data) : $template;
	    	    
	    return [
		    "html" => $body,
		    "text" => $plaintext
	    ];
	}
	
	/*
		Compile email HTML and save to mailbox
		ARGUMENTS:
			$user - @Array: User Data - to whom the Email is sent
			$data - @Array: Form Data
			@template - @Array: Email Template Data
			@message - @string: Optional Plaintext Message
		
		@return array
	*/
	
	public static function Mailbox ($user = [], $data = [], $template = [], $message = NULL)
	{
	    $email = ArrayUtils::get($user, 'email');
	    
	    if (!$email) return NULL;
	    
		$project_url = Api::Settings('application.website');
		
		$headers = getallheaders();
	    
	    # Build Mailbox browser link
		
		$salt = Utils::Hash(true, $email, time());
	    $browser = "{$project_url}/api/app/mailbox/{$salt}";
	    
	    ArrayUtils::set($data, 'browser', $browser); 
	    	    
	    $compiled = self::Compile($template, $data);
	    
	    # Build Mailbox object so user can view in browser
	    
	    $mailbox = [
		    "uuid" => $salt,
		    "form" => ArrayUtils::get($template, 'name'),
		    "mailbox" => "outbox",
		    "email" => $email,
		    "first_name" => ArrayUtils::get($user, 'first_name'),
		    "last_name" => ArrayUtils::get($user, 'last_name'),
		    "url" => ArrayUtils::get($user, 'url'),
		    "subject" => ArrayUtils::get($template, 'title'),
		    "message" => $message,
		    "body" => ArrayUtils::get($compiled, 'html'),
		    "ip_address" => ArrayUtils::get($headers, 'Ip-Address'),
		    "telephone" => ArrayUtils::get($user, 'telephone')
	    ];
    
	    # Initialize TableGateway and save mail to outbox
	    
	    $tableGateway = Api::TableGateway('app_mailbox', true);	
	    
	    $tableGateway->createRecord($mailbox);
	    
	    return $compiled;
	}
	
	/*
		Save outgoing mail in outbox - App Mailbox
		ARGUMENTS:
			$insert - @Array: Row Data to insert in DB
			$body - @String: HTML Email Body
		
		@return array
	*/
	
	public static function Outbox ($insert = [], $body = '')
	{		
		ArrayUtils::set($insert, 'mailbox', 'outbox'); 
	    ArrayUtils::set($insert, 'body', $body);    
	    
	    $tableGateway = Api::TableGateway('app_mailbox');	
	    
	    $tableGateway->createRecord($insert);
	    
	    return $insert;
	}
	
	/*
		Send mail using SMTP or API (cURL)
		ARGUMENTS:
			$form - @Array: Incoming Form Data
			$user - @Array: Authenticated App User or Form Data
			$configuration - @Array: API App Configuration
			$compiled - @Array: HTML and Plaintext List
			$from - @Array: Sender Data
		
		@return array
	*/
	
	public static function Send ($form = [], $user = [], $configuration = [], $compiled = [], $from = [])
	{
		$project_url = get_directus_setting('app_url');
		$headers = getallheaders();
		
		# Save in Mailbox before sending
		
		$smtp = ArrayUtils::get($configuration, 'mail.smtp', []); 
	    $api = ArrayUtils::get($configuration, 'mail.api', []); 
	    
	    $toEmail = ArrayUtils::get($user, 'email');
		$toName = ArrayUtils::get($user, 'name') ?: $toEmail;
		
		if (!$toEmail) 
		{
			return [
			    "error" => true,
			    "message" => Api::Responses('mail.send.email')
		    ];			
		}
		
		$subject = ArrayUtils::get($form, 'notification.subject') ?: ArrayUtils::get($form, 'subject') ?: ArrayUtils::get($form, 'title');
		
		if (!$subject) return [
		    "error" => true,
		    "message" => Api::Responses('mail.send.subject')
	    ];
		
		$plaintext = ArrayUtils::get($compiled, 'text');
		$body = ArrayUtils::get($compiled, 'html');
	    
	    $fromName = ArrayUtils::get($from, 'name') ?: ArrayUtils::get($configuration, 'application.name'); 
	    $fromEmail = ArrayUtils::get($from, 'email') ?: ArrayUtils::get($configuration, 'mail.sender.email') ?: ArrayUtils::get($configuration, 'application.email'); 
		
		if (!$fromName || !$fromEmail) 
		{
			return [
			    "error" => true,
			    "message" => Api::Responses('mail.send.from')
		    ];			
		}
	    
	    $apiParams = ArrayUtils::get($form, 'api', []);
	    
	    $outbox = false;
	    
	    $headers = ArrayUtils::get($api, 'headers', ["Content-Type: application/x-www-form-urlencoded"]);
	    
	    if (is_string($headers)) $headers = implode(',', $headers);
	    
	    # API - Via cURL
	    
	    if (ArrayUtils::get($api, 'url'))
	    {
		    ArrayUtils::set($api, 'authentication', filter_var(ArrayUtils::get($api, 'authentication'), FILTER_VALIDATE_BOOLEAN));
		    
		    ArrayUtils::set($api, 'headers', $headers);
		    
		    $post = [
			    "from" => trim("{$fromName} <{$fromEmail}>"),
			    "to" => trim("{$toName} <{$toEmail}>"),
			    "cc_mail" => ArrayUtils::get($configuration, '.outgoing.cc'),
			    "bcc_mail" => ArrayUtils::get($configuration, '.outgoing.bcc'),
			    "subject" => $subject,
			    "text" => $plaintext,
			    "html" => $body
		    ];
		    
		    $post = array_merge($apiParams, $post);
		    
		    try
		    {
			    $response = Curl::Api($api, $post);
			    
			    return $response;
		    }
		    catch (Exception $e)
		    {
			    return [
				    "error" => true,
				    "message" => str_replace('{{message}}', $e->getMessage(), Api::Responses('mail.send.exception'))
			    ];
		    }
	    }
	    
	    # SMTP - Via PHPMailer
	    
	    elseif (ArrayUtils::get($smtp, 'host'))
	    {
		    $mail = new PHPMailer(true);
		    
		    try
		    {
			    $mail->isSMTP();
			    
			    # Server settings
			    
			    if (ArrayUtils::get($_REQUEST, 'debug')) $mail->SMTPDebug = 3;
			    
			    $mail->Host = trim(ArrayUtils::get($smtp, 'host'));
			    $mail->Username = trim(ArrayUtils::get($smtp, 'username'));
			    $mail->Password = trim(ArrayUtils::get($smtp, 'password'));
			    $mail->SMTPAuth = filter_var(ArrayUtils::get($smtp, 'authentication'), FILTER_VALIDATE_BOOLEAN); 
			    $mail->SMTPSecure = trim(ArrayUtils::get($smtp, 'encryption'));
			    $mail->Port = trim(ArrayUtils::get($smtp, 'port'));
			    
			    # Recipients
			    
			    $mail->setFrom($fromEmail, $fromName);
			    $mail->addAddress($toEmail, $toName);
			    
			    $mail->addCC(ArrayUtils::get($configuration, '.outgoing.cc'));
			    $mail->addBCC(ArrayUtils::get($configuration, '.outgoing.bcc'));
			    
			    # Content
			    
			    $mail->isHTML(true);
			    $mail->Subject = $subject;
			    $mail->Body = $body;
			    $mail->AltBody = $plaintext;
			    
			    $response = $mail->send();
			    
			    return $response;
		    }
		    catch (Exception $e)
		    {
			    return [
				    "error" => true,
				    "message" => str_replace('{{message}}', ($mail->ErrorInfo ?: $e->getMessage()), Api::Responses('mail.send.exception'))
			    ];
		    }
	    }
	    
	    else return [
		    "error" => true,
		    "message" => Api::Responses('mail.send.configuration')
	    ];
	}
	
	/*
		Get Email Template from App Templates for the Mailer
		ARGUMENTS:
			$template - @String: Name of the template (app_templates.name)
		
		@return array
	*/
	
	public static function Template ($template = NULL)
	{
		if (!$template) return NULL;
			
		$tableGateway = Api::TableGateway('app_templates', true);	
		$entries = $tableGateway->getItems([
			"fields" => "*.*",
			"limit" => 1,
		    "filter" => [
			    "name" => [
				    "eq" => $template
			    ]
		    ]
	    ]);
	    
	    return ArrayUtils::get($entries, 'data.0');
	}
	
	/*
		Get or Parse a User Object for the Mailer
		ARGUMENTS:
			$user - @Array or @Integer: User ID or User Object
			$form - @Array: If no user try to build user from Form Data
		
		@return array
	*/
	
	public static function User ($user = NULL, $form = NULL)
	{
		$id = is_array($user) ? ArrayUtils::get($user, 'id') : ( is_numeric($user) ? $user : NULL );
		
		if ($id) 
		{
			$tableGateway = Api::TableGateway('app_users', false);	
			$entries = $tableGateway->getItems([
			    "id" => $id,
			    "single" => 1,
			    "limit" => 1,
			    "preview" => 1
		    ]);
		    
		    $user = ArrayUtils::get($entries, 'data');
		}
		elseif (is_array($form)) $user = $form;
		else return NULL;
		
		if (!ArrayUtils::get($user, 'first_name')) $user = $form;			
	    
	    ArrayUtils::set($user, 'name', (ArrayUtils::get($user, 'first_name') . ' ' . ArrayUtils::get($user, 'last_name')));
	    
	    return $user;
	}
}