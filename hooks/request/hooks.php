<?php

use Directus\Util\ArrayUtils;

require dirname(dirname(__DIR__)) . '/helpers/functions.php';

return [
    'actions' => [
        'application.boot' => function () 
        {
	        $input = file_get_contents('php://input');
	        
	        if (is_string($input))
	        {
		        $input = json_decode($input, true);
			    
			    if (is_array($input) && json_last_error() == JSON_ERROR_NONE)
			    {
				    $_REQUEST = array_merge_recursive($_REQUEST, $input);
			    }
		    
			    if (ArrayUtils::get($_REQUEST, 'debug') === 'GET') die(json_encode($_REQUEST));
			    
			    $headers = getallheaders();
			    
			    $ipaddress = isset($headers['Ip-Address']) ? $headers['Ip-Address'] : NULL;
			    
			    if ($ipaddress) 
			    {
				    $_SERVER['X_FORWARDED_FOR'] = $ipaddress;
				    $_SERVER['CLIENT_IP'] = $ipaddress;
				    $_SERVER['REMOTE_ADDR'] = $ipaddress;
			    }
	        }
        }
    ]
];
