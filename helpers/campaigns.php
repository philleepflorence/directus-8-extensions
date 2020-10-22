<?php

/*
	Author - PhilleepFlorence
	Description - Directus Cache Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateTimeUtils;

class Campaigns 
{
	private static $fields = [
		"*.*",
		"page.id",
		"page.path",
		"article.id",
		"article.path",
		"owner.id",
		"modified_by.id",
		"image.filename_disk",
		"apis.endpoint.*.*"
	];
		
	/*
		Send the campaign to all the API endpoints
		PARAMETERS:
			id - Primary Key of the Item to send (overrides start and end date)
			queue - Push all campaigns within the current data range,
			fields - the fields to load from the DB
	*/
	
	public static function Send ($params = [], $debug)
	{
		$id = ArrayUtils::get($params, "id");
		$queue = ArrayUtils::get($params, "queue");
		$start_date = date('Y-m-d H:i:s');
		$fields = ArrayUtils::get($params, "fields") ?: self::$fields;
		
		# Get all campaigns matching the parameters
		
		if ($id)
		{
			$filter = [
				"id" => $id
			];
		}
		elseif ($queue)
		{
			$filter = [
				"start_date" => [
					"lte" => $start_date
				],
				"status" => "pending"
			];
		}
				    
	    $campaignsGateway = Api::TableGateway('contents_campaigns', true);	
	    $entries = $campaignsGateway->getItems([
		    "fields" => $fields,
		    "filter" => $filter
	    ]);
	    $entries = ArrayUtils::get($entries, "data");
	    $campaigns = [];
	    
	    foreach ($entries as &$entry)
	    {
		    # Loop through the API Endpoints
		    
		    $entry = Utils::ToArray($entry);
		    
		    $apis = ArrayUtils::get($entry, "apis");
		    
		    if (!is_array($apis)) continue;
		    
		    foreach ($apis as $api)
		    {
			    $api = ArrayUtils::get($api, "endpoint");
			    
			    $endpoint = ArrayUtils::get($api, "endpoint");
			    $fields = ArrayUtils::get($api, "fields");
			    $data = ArrayUtils::get($api, "data");
			    $properties = ArrayUtils::get($api, "properties");
			    
			    $api_provider = ArrayUtils::get($api, "api_provider");
			    $credentials = ArrayUtils::get($api_provider, "credentials");
			    
			    # Process endpoint if applicable
			    
			    if (is_array($credentials)) $endpoint = Utils::Compile($endpoint, $credentials);
			    			    
			    # Process properties from the API Provider
			    
			    $parameters = [];
			    
			    if (is_array($properties)) foreach ($properties as $property) ArrayUtils::set($parameters, $property, ( ArrayUtils::get($credentials, $property) ));
			    
			    # Process fields
			    
			    if (is_array($fields)) foreach ($fields as $field => $value) ArrayUtils::set($entry, $field, ( Utils::Compile($value, $entry) ));
			    
			    # Process the API data
			    
			    if (is_array($data)) foreach ($data as $property => $field) ArrayUtils::set($data, $property, ( ArrayUtils::get($entry, $field) ));
			    
			    # Build Campaigns
			    
			    array_push($campaigns, [
				    "api" => [
					    "url" => $endpoint,
					    "data_type" => ArrayUtils::get($api, "data_type"),
					    "response_type" => ArrayUtils::get($api, "response_type"),
					    "method" => ArrayUtils::get($api, "method"),
				    ],
				    "data" => array_merge($data, $parameters),
				    "campaign" => [
					    "id" => ArrayUtils::get($entry, "id"),
					    "api" => ArrayUtils::get($api, "id")
				    ]				    
			    ]);
		    }
	    }
	    
	    if ($debug) return $campaigns;
	    
	    # Process and send campaigns to APIs via Curl::API
	    
	    $json_responses = [];
	    
	    $return = [
		    "meta" => [
			    "total" => count($campaigns)
		    ],
		    "data" => []
	    ];
	    
	    $activityGateway = Api::TableGateway('app_activity', true);
	    
	    foreach ($campaigns as $campaign)
	    {
		    # Send to the API endpoint
		    
		    $response = Curl::Api($campaign['api'], $campaign['data']);
		    $response = array_merge($campaign['campaign'], $response);
		    
		    $campaign_id = ArrayUtils::get($campaign, 'campaign.id');
		    
		    array_push($return['data'], $response);
		    
		    # Update the json_response with code, duration and API response
		    
		    $activityGateway->createRecord([
			    "action" => "curl",
			    "method" => "send",
			    "collection" => "contents_campaigns",
			    "item" => $campaign_id,
			    "data" => $response
		    ]);
		    
		    $campaignsGateway->updateRecord($campaign_id, [
			    "status" => "published"
		    ]);
	    }
	    
	    return $return;
	}
}