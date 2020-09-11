<?php

// Find a citation in our MySQL Database


require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/nameparse.php');
require_once(dirname(__FILE__) . '/fingerprint.php');
require_once(dirname(__FILE__) . '/lcs.php');
require_once(dirname(__FILE__) . '/api_utils.php');
require_once(dirname(__FILE__) . '/reconciliation_api.php');



//----------------------------------------------------------------------------------------
class BioStorService extends ReconciliationService
{
	//----------------------------------------------------------------------------------------------
	function __construct()
	{
		$this->name 			= 'BioStor';
		
		$this->identifierSpace 	= 'https://biostor.org/';
		$this->schemaSpace 		= 'http://rdf.freebase.com/ns/type.object.id';
		$this->Types();
		
		$view_url = 'https://biostor.org/reference/{{id}}';

		$preview_url = '';	
		$width = 430;
		$height = 300;
		
		if ($view_url != '')
		{
			$this->View($view_url);
		}
		if ($preview_url != '')
		{
			$this->Preview($preview_url, $width, $height);
		}
	}
	
	//----------------------------------------------------------------------------------------------
	function Types()
	{
		$type = new stdclass;
		$type->id = 'https://schema.org/CreativeWork';
		$type->name = 'CreativeWork';
		$this->defaultTypes[] = $type;
	} 	
		

	
	// MySQL 
	//----------------------------------------------------------------------------------------------
	// Handle an individual query
	function OneQuery($query_key, $text, $limit = 1, $properties = null)
	{
		global $config;
		
				
		$url = $config['web_server'] . $config['web_root'] . 'api_openurl.php?rft.dat=' . urlencode($text);
				
		$json = get($url);
		
		//file_put_contents('/tmp/q.txt', $json, FILE_APPEND);

		if ($json != '')
		{
			$obj = json_decode($json);
			
			if ($obj->found)
			{
				$n = min(3, count($obj->results));
				for ($i = 0; $i < $n; $i++)
				{
					$hit = new stdclass;
					$hit->id 	= $obj->results[$i]->guid;					
					$hit->name 	= $obj->results[$i]->title;			
					$hit->score = $obj->results[$i]->score;
					
					if (isset($obj->results[$i]->wikidata))
					{
						$hit->wikidata = $obj->results[$i]->wikidata;
					}
					
					$hit->match = true;
					$this->StoreHit($query_key, $hit);				
				}
			}
		}
		

		
	}	
	
	
	
	
}

$service = new BioStorService();


if (0)
{
	file_put_contents('/tmp/q.txt', $_REQUEST['queries'], FILE_APPEND);
}


$service->Call($_REQUEST);

?>