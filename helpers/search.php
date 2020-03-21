<?php

/*
	Author - PhilleepFlorence
	Description - Search Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Expression;

use function Directus\sorting_by_key;

class Search 
{	
	/*
		Search Utility 
		Compile fields and rows of collections into a cache collection for easy searches.
		Ideal for projects with Collections that have less than 500K Rows
		PARAMETERS:
			cache - @Array: List of configuration options for the collection
				params: getItems Parameters, 
				status: status to use as cache row status (add colon to use field value), 
				fields: the fields to concat in cache, 
				category: category to use in search results grouping, 
				title: title to use in search results, 
				description: description to use in search results, 
				slug: slug to use in search results (add colon to use field value), 
				collections: related collections (TODO - for use in updating cache), 
				relations: array of related collections and fields to concat
			debug - @Boolean: If true return cache rows without updating DB
			
		@return array
	*/
	
	public static function Build ($collection = '', $debug = false)
	{
		if (!$collection) return null;
		
		# Load collection configuration from the DB
		
		$tableGateway = Api::TableGateway("app_collections_configuration", true);	
		$item = $tableGateway->getItems([
			"single" => 1,
			"filter" => [
				"collection" => $collection,
				"type" => "cache"
			]
		]);
		$item = ArrayUtils::get($item, "data");
		$item = Utils::ToArray($item);
		$options = ArrayUtils::get($item, "options");
		$params = ArrayUtils::get($options, "params");
		
		# Initialize database connection to the collection
		
		$tableGateway = Api::TableGateway($collection,  true);
		$rows = $tableGateway->getItems(ArrayUtils::get($options, "params"));
		$rows = ArrayUtils::get($rows, "data");
		$cache = [];
		$terms = [];
		
		# Process columns and relations - row_id, collection, category, cache
		
		foreach ($rows as $row) 
		{
			$currcache = [];
	    
		    $title = ArrayUtils::get($row, ArrayUtils::get($options, "title"));
		    $description = ArrayUtils::get($row, ArrayUtils::get($options, "description"));
		    
		    if (is_string($title)) $title = strip_tags($title);
		    
		    if (is_string($description)) 
		    {
			    $description = explode('><', $description);
			    $description = reset($description);
			    $description = strip_tags($description);
		    }
		    
		    # Process fields
		    
		    $fields = ArrayUtils::get($options, "fields");
		    $fields = explode(',', $fields);
		    
		    foreach ($fields as $field) 
		    {
			    $field = trim($field);
			    $value = ArrayUtils::get($row, $field);
			    
			    if (!$value) continue;
			    
			    # Process search terms for the primary fields
			    
			    $currterm = ArrayUtils::get($options, "terms.fields.{$field}");
			    
			    if ($currterm && is_array($value) && $currterm === "array")
			    {
				    foreach ($value as $currvalue) ArrayUtils::set($terms, trim($currvalue), strlen($currvalue));
			    }
			    elseif ($currterm && is_string($value) && $currterm === "string") ArrayUtils::set($terms, trim($value), strlen($value));
			    
			    if (is_array($value)) $value = implode(", ", $value);
			    
			    $key = str_replace('.data.', ':', $field);
			    
			    $value = str_replace('><', '> <', $value);
			    
			    $value = implode(" - ", [$key, $value]);
			    
			    array_push($currcache, trim($value));
		    }
		    
		    $relations = ArrayUtils::get($options, "relations", []);
		    	    
			foreach ($relations as $relation => $fields)
			{
				$metadatas = ArrayUtils::get($row, "{$relation}");
				
				if (!is_array($metadatas) || !count($metadatas)) continue;				
				
				$metacache = [];
				
				foreach ($metadatas as $metadata)
				{
					foreach ($fields as $field)
					{
						$value = ArrayUtils::get($metadata, $field);
						
						if (!$value) continue;
						
						# Process search terms for the related collection fields
						
						$currterm = ArrayUtils::get($options, "terms.relations.{$relation}.{$field}");
			    
					    if ($currterm && is_array($value) && $currterm === "array")
					    {
						    foreach ($value as $currvalue) ArrayUtils::set($terms, $currvalue, strlen($currvalue));
					    }
					    elseif ($currterm && is_string($value) && $currterm === "string") ArrayUtils::set($terms, $value, strlen($value));
						
						if (is_array($value)) $value = implode(", ", $value);
						
						$_key = str_replace('.data.', ':', $field);
						
						$value = str_replace('><', '> <', $value);
						
						$value = implode(" - ", ["{$relation}:{$_key}", $value]);
						
						array_push($metacache, $value);
					}
				}
				
				$metacache = implode(' - ', $metacache);
				
				array_push($currcache, trim($metacache));
			}
		    
		    $currcache = implode(" --- ", $currcache);
		    
		    $slug = ArrayUtils::get($options, "slug");
		    
		    if (strrpos($slug, ':') === 0)
		    {
			    $slug = str_replace(':', '', $slug);
			    $currslug = ArrayUtils::get($row, $slug);
		    }
		    else if ($slug) {
			    $currslug = explode('/', $slug);
			    $path = [];
			    
			    foreach ($currslug as $currpath)
			    {
				    if (strrpos($currpath, ':') === 0)
				    {
					    $currpath = str_replace(':', '', $currpath);				    
					    $currpath = ArrayUtils::get($row, $currpath);
				    }
				    
				    array_push($path, $currpath);
			    }
	
			    $currslug = implode('/', $path);
		    }
		    
		    $category = ArrayUtils::get($options, "category");
		    
		    $currcategory = strrpos($category, ':') === 0 ? str_replace(':', '', $category) : ArrayUtils::get($row, $category);
		    
		    if (is_string($currcache)) $currcache = strip_tags($currcache);
		    
		    $status = ArrayUtils::get($options, "status");
		    $privilege = ArrayUtils::get($options, "privilege");
		    $privilege = is_numeric($privilege) ? $privilege : ArrayUtils::get($row, $privilege, 0);
		    
		    array_push($cache, [
			    "row_id" => ArrayUtils::get($row, 'id'),
			    "collection" => $collection,
			    "category" => $currcategory,
			    "title" => $title,
			    "description" => $description,
			    "slug" => $currslug,
			    "cache" => $currcache,
			    "status" => ArrayUtils::get($row, $status, "draft"),
			    "privilege" => $privilege
		    ]);
		}
		
		if ($debug === true) return [
			"cache" => $cache,
			"options" => $options,
			"terms" => $terms
		];
		
		# Insert or update the search cache collection
		
		$tableGateway = Api::TableGateway('contents_search_cache',  true);
    
	    foreach ($cache as $row) 
	    {
		    # Check if the collection exist - update if exist - insert if not
		    
		    $entry = $tableGateway->getItems([
			    "limit" => 1,
			    "filter" => [
				    "row_id" => $row["row_id"],
				    "collection" => $row["collection"]
			    ]
		    ]);
			$entry = ArrayUtils::get($entry, 'data.0');
		    
		    ArrayUtils::set($row, 'updated', date("Y-m-d H:i:s"));
		    
		    if ($entry) 
		    {
			    $id = (int) ArrayUtils::get($entry, 'id');
			    
			    if ($id) $tableGateway->updateRecord($id, $row);
		    }
		    else
		    {			    
			    $tableGateway->createRecord($row);
		    }	    
	    }
	    
	    # Insert or update the search terms collection
	    
	    $tableGateway = Api::TableGateway('contents_search_terms',  true);
	    
	    foreach ($terms as $term => $length)
	    {
		    # Check if the collection exist - update if exist - insert if not
		    
		    $entry = $tableGateway->getItems([
			    "limit" => 1,
			    "filter" => [
				    "name" => $term,
				    "length" => $length
			    ]
		    ]);
			$id = ArrayUtils::get($entry, 'data.0.id');
			$row = [
				"name" => $term,
				"length" => $length
			];
			
			if (!$id) $tableGateway->createRecord($row);
	    }

	    return [
		    "meta" => [
			    "collection" => $collection,
			    "cache_total" => count($cache),
			    "rows_total" => count($rows),
			    "terms_total" => count($terms)
		    ]
	    ];
	}
	
	/*
		Search Utility
		Search through cache collection for a query string
		Ideal for projects with Collections that have less than 500K Rows
		PARAMETERS:
			params - @Array
				category - CSV list of filter the items by categories (categories all rows by default)
				collection - CSV list of filter the items by collection (search all collections by default)
				mode - the mode of search: strict (match phrase), all (match all words), any (match any word)
				privilege - the privilege of the application user (must be >= row.privilege)
				query - the query string to find in collection items
				status - the status type (draft, published, soft_delete)
			debug - @Boolean: If true return rows
			
		@return array (max 200 results)
	*/
	
	public static function Cache ($params = [], $debug)
	{
		$category = ArrayUtils::get($params, 'category');
		$collection = ArrayUtils::get($params, 'collection');
		$limit = ArrayUtils::get($params, 'limit') ?: 200;
		$mode = ArrayUtils::get($params, 'mode') ?: 'all';
		$privilege = ArrayUtils::get($params, 'privilege') ?: 0;
		$query = ArrayUtils::get($params, 'query');
		$status = ArrayUtils::get($params, 'status') ?: 'published';
		
		$result = [
			"meta" => [
				"total" => 0,
				"query" => $query
			],
			"data" => []
		];
		$items = [];
		$query = trim($query); 
		
		# For short query strings use the standard tablegateway for rows that contain query.
		
		if (strlen($query) < 5)
		{
			$tableGateway = Api::TableGateway("contents_search_cache", true);
			$options = [
				"limit" => $limit,
				"sort" => "-updated",
				"status" => $status,
				"filter" => [
					"cache" => [
						"contains" => $query
					],
					"privilege" => $privilege					
				]
			];	
			
			if (is_string($category)) ArrayUtils::set($$options, 'filters.category.in', $category);	
			
			if (is_string($collection)) ArrayUtils::set($options, 'filters.collection.in', $collection);
			
			$items = $tableGateway->getItems($options);
			
			$items = ArrayUtils::get($items, 'data');
		}
		
		# Create SQL Statement
		
		$select = '';
		$where = [];
    
	    switch ($mode)
	    {
		    case 'any':
		    	$search = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $query);
		    	$search = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $search);
		    	$search = trim($search);
		    	
		    	array_push($where, new Expression("MATCH (cache) AGAINST ('{$search}' IN NATURAL LANGUAGE MODE)"));
		    break;
		    
		    case 'all':
		    	$search = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $query);
		    	$search = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $search);
		    	
		    	$boolean = explode(' ', $search);
		    	$boolean = implode(' +', $boolean);
		    	$boolean = "+{$boolean}";
		    	
		    	array_push($where, new Expression("MATCH (cache) AGAINST ('{$boolean}' IN BOOLEAN MODE)"));   
		    break;
		    
		    case 'strict':	
		    	$search = str_replace('"', '', $query);
		    	
		    	array_push($where, new Expression("MATCH (cache) AGAINST ('\"{$search}\"' IN BOOLEAN MODE)")); 
		    break;
	    }
	    
	    if (is_string($category)) ArrayUtils::set($where, 'category', explode(',', $category));	
			
		if (is_string($collection)) ArrayUtils::set($where, 'collection', explode(',', $collection));
		
		$tableGateway = Api::TableGateway("contents_search_cache", true);		
		$select = new Select('contents_search_cache'); 
		$select->limit($limit); 
		$select->where($where);  
    
	    # Get from the cache collections all the rows that match
	    
	    $rows = $tableGateway->selectWith($select);
	    $items = $rows->toArray();		
		$total = count($items);
		
		# Process Relevance against Title and Cached Content
		
		$querylength = strlen($query);
		$querymatch = NULL;
    
	    if ($total > 0)
	    {
		    foreach ($items as &$item)
		    {
			    $title = ArrayUtils::get($item, 'title');
			    $description = ArrayUtils::get($item, 'description');
			    $cache = ArrayUtils::get($item, 'cache');
			    
			    $titlelength = strlen($title);
			    
			    # Compare against the title and description
			    
			    similar_text($query, $title, $similar);
			    
			    if (strcasecmp($title, $query) === 0) $querymatch = $query;
			    
			    $similar = round(100 - $similar);
			    $relevance = min(abs(levenshtein($query, $description)), abs(levenshtein($query, $title)));
			    $pattern = "/\b({$query})\b/i";
			    
			    preg_match($pattern, $title, $titlematches, PREG_OFFSET_CAPTURE);
			    
			    if (count($titlematches)) {
				    $titlematches = array_column($titlematches, 1);
				    $titlematches = min($titlematches);
				    
				    $relevance = min($relevance, $titlematches);
			    }
			    else $relevance = ($relevance + $querylength) * $similar;
	
			    preg_match($pattern, $cache, $cachematches, PREG_OFFSET_CAPTURE);
			    
			    if (count($cachematches)) {
				    $cachematches = array_column($cachematches, 1);
				    $cachematches = min($cachematches);
				    
				    $relevance = min($relevance, $cachematches);
			    }
			    else $relevance = ($relevance + strlen($query)) * $similar;	 
			    
			    $relevance = $relevance + (max($titlelength, $querylength) - $querylength);   
			    
			    ArrayUtils::set($item, "relevance", $relevance);
		    }
		    
		    usort($items, sorting_by_key('relevance', 'ASC'));
	    }
				
		# Process the response meta and data arrays.
		$rows = $items;
		
		if (is_array($rows)) 
		{
			foreach ($rows as &$row)
			{
				unset($row["cache"]);
				
				array_push($result["data"], $row);
			}
			
			ArrayUtils::set($result, 'meta.total', $total);
		}
		
		# Save the query and collections to Search Queries - if applicable
    
	    $update = [
		    "query" => $query,
		    "mode" => $mode,
		    "total" => $total,
		    "collections" => []
	    ];
		
		$collections = [];
	        
		foreach ($items as $item)
		{
			$currcategory = ArrayUtils::get($item, 'category');
			$currcollection = ArrayUtils::get($item, 'collection');
			
			if (!in_array($currcollection, $update["collections"])) array_push($update["collections"], $currcollection);
			
			if (!in_array($currcategory, $collections)) array_push($collections, $currcategory);
		}
				    
	    $tableGateway = Api::TableGateway("contents_search_queries", true);
	    $queryitems = $tableGateway->getItems([
		    "limit" => 1,
		    "filter" => [
			    "mode" => $mode,
			    "query" => $query
		    ]
	    ]);

	    $query_id = ArrayUtils::get($queryitems, 'data.0.id');
	    
	    if (is_array($update['collections'])) $update['collections'] = implode(',', $update['collections']);
	    
	    if ($query_id) $tableGateway->updateRecord($query_id, $update);
	    else $tableGateway->createRecord($update);
	    
	    # Get the nearest matches if no match
    
	    $suggestions = null;
	    
	    if (!$querymatch) 
	    {
		    $min = strlen($query) - 2;
		    $max = strlen($query) + 2;
		    
		    if ($min < 2) $min = 2;
		    
		    $tableGateway = Api::TableGateway("contents_search_terms", true);
		    $suggestions = $tableGateway->getItems([
			    "limit" => "-1",
			    "fields" => "name",
			    "filter" => [
				    "length" => [
					    "between" => "{$min},{$max}"
				    ]
			    ] 
			]);
			$suggestions = ArrayUtils::get($suggestions, 'data');
			
			foreach ($suggestions as &$suggestion)
			{
				$currname = ArrayUtils::get($suggestion, 'name');
				
				similar_text($query, $currname, $similar);
				
				$match = ceil($similar / abs(levenshtein($query, $currname)));
				
				ArrayUtils::set($suggestion, 'match', $similar);
				ArrayUtils::set($suggestion, 'similar', abs(levenshtein($query, $currname)));
			}
			
			usort($suggestions, sorting_by_key('match', 'DESC'));
			
			$suggestions = array_slice($suggestions, 0, 5);
			
			ArrayUtils::set($result, 'meta.suggestions', $suggestions);
	    }	    
				
		return $result;
	}
	
	/*
		Search Utility
		Search through all applicable collections for a query - String fields only
		Ideal for finding which collection item contains a query.
		PARAMETER:
			params - @Array
				collections - @String: List of collections in the DB to search
				query - @String: the query string to find in collection items
			
		@return array
	*/
	
	public static function Database ($params = [], $debug)
	{
		$collections = ArrayUtils::get($params, 'collections');
		$query = ArrayUtils::get($params, 'query');
		$types = ['array', 'json', 'string'];
	    $total = 0;
		$options = [
		    "fields" => "id,collection,field,type,interface,hidden_detail",
		    "limit" => -1,
	        "sort" => "collection",
	        "filter" => []
	    ];
	    $result = [
		    "meta" => [
			    "query" => $query,
			    "total" => $total
		    ],			    
		    "data" => [],
	    ];
		
		$query = str_ireplace('+', ' ', $query);
		
		# Get visible collections if no collections were sent
		
		if (!$collections)
		{
			$tableGateway = Api::TableGateway("directus_collections");
			$entries = $tableGateway->getItems([
				"fields" => "collection",
				"filter" => [
					"hidden" => 0
				]
			]); 
			$entries = ArrayUtils::get($entries, 'data');
			$collections = [];
			
			foreach ($entries as $entry)
			{
				array_push($collections, $entry['collection']);
			}
		}
		
		# Get collection structures from Directus Fields 
    
	    $tableGateway = Api::TableGateway("directus_fields");
	    $entries = $tableGateway->getItems($options); 
	    $entries = ArrayUtils::get($entries, 'data');
	    $collections = is_string($collections) ? explode(',', $collections) : $collections;
	    $fields = [];
	    
	    foreach ($entries as $entry)
	    {
		    $visible = $entry["hidden_detail"] === false;
		    $validtype = in_array($entry["type"], $types);
		    $validcollection = in_array($entry["collection"], $collections);
		    
		    if (!$visible || !$validtype || !$validcollection) continue;
		    
		    $collection = ArrayUtils::get($entry, 'collection');
			$field = ArrayUtils::get($entry, 'field');
	    
			ArrayUtils::set($fields, "{$collection}.{$field}", $field);
	    }
	    
	    if ($debug === true) return $fields;
	    
	    # Search through all applicable collections
	    
	    foreach ($fields as $collection => $rows)
	    {
		    $collectionname = ucwords($collection, '_');
		    $collectionname = str_ireplace('_', ' ', $collectionname);
			$tableGateway = Api::TableGateway($collection, null);
		    $filter = [];
	    	    
		    foreach ($rows as $field)
		    {
			    $filter[$field] = [
				    "contains" => $query,
				    "logical" => "or"
			    ];
		    }
	    	    
		    $entries = $tableGateway->getItems([
			    "fields" => "*",
			    "filter" => $filter
		    ]);
		    $entries = ArrayUtils::get($entries, 'data');
		    $count = count($entries);
		    
		    if (!$count) continue;
		    
		    $total = $total + $count;
		    $rows = [];
		    
		    array_walk($entries, function (&$entry) use ($collection, $collectionname, &$rows)
		    {
			    $id = ArrayUtils::get($entry, 'id');
			    $title = ArrayUtils::get($entry, 'title') 
			    		?: ArrayUtils::get($entry, 'name') 
			    		?: ArrayUtils::get($entry, 'category') 
			    		?: ArrayUtils::get($entry, 'author') 
			    		?: ArrayUtils::get($entry, 'subject') 
			    		?: "{$collectionname} : {$id}";
			    $description = ArrayUtils::get($entry, "description") 
			    		?: ArrayUtils::get($entry, "label") 
			    		?: ArrayUtils::get($entry, "content") 
			    		?: ArrayUtils::get($entry, "value") 
			    		?: ArrayUtils::get($entry, "notes") 
			    		?: ArrayUtils::get($entry, "email");
			    
			    $description = strip_tags(str_ireplace('><', '> <', $description));
			    $row = [];
			    
			    ArrayUtils::set($row, 'headline', $collectionname);
			    ArrayUtils::set($row, 'collection', $collection);
			    ArrayUtils::set($row, 'title', $title);
			    ArrayUtils::set($row, 'description', $description);
			    ArrayUtils::set($row, 'path', "/app/collections/{$collection}/{$id}");
			    
			    array_push($rows, $row);
		    });
		    
		    $result['data'] = array_merge($result['data'], $rows);
	    }
	    
	    ArrayUtils::set($result, 'meta.total', $total);
	    
	    return $result;
	}
}