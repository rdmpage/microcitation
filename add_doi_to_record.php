<?php

// Add DOIs to reference

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/lib.php');



//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$issn = '1514-5158';

$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '" AND doi IS NULL';



$sql .= ' ORDER BY CAST(series as SIGNED), CAST(volume as SIGNED), issue, CAST(spage as SIGNED)';



$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	
	$title 		= $result->fields['title'];
	$authors 	= $result->fields['authors'];
	$journal 	= $result->fields['journal'];
	$issn	 	= $result->fields['issn'];
	$volume 	= $result->fields['volume'];
	$spage 		= $result->fields['spage'];
	$epage 		= $result->fields['epage'];
	$year 		= $result->fields['year'];
	
	$url 		= $result->fields['url'];
	
	
	if (1)
	{
		 // http://revista.macn.gob.ar/ojs/index.php/RevMus/article/view/563
		 
		if ($spage != '') 
		{
		
			$id = 0;
		
			if (preg_match('/view\/(?<id>\d+)$/', $url, $m))
			{
				$id = $m['id'];
		 
				$doi = '10.22179/revmacn.' . $volume . '.' . $m['id'];
				echo "-- $doi\n";
		
				$sql = 'UPDATE publications SET doi="' . $doi . '"'
					. ' WHERE guid="' . $guid . '";';
	
	
				echo $sql . "\n";	
			}
		}
	}
	
	
	if (0)
	{
		$str = $title . ' ' . $journal . ' ' . $volume . ' ' . $spage . '-' . $epage . ' ' . $year;

		echo "-- $str\n";

		$url = 'https://mesquite-tongue.glitch.me/search?q=' . urlencode($str);


		$json = get($url);

		echo $json;
	}
	
	
	if (0)
	{
	
		$openurl = '';

		$openurl .= '&atitle=' . urlencode($title);		
		
		$openurl .= '&title=' . urlencode($journal);
		$openurl .= '&issn=' . $issn;
		$openurl .= '&volume=' . $volume;
		$openurl .= '&spage=' . $spage;
		$openurl .= '&epage=' . $epage;
		$openurl .= '&date=' . $year;
		
		$url = 'http://www.crossref.org/openurl?' . $openurl . '&pid=rdmpage@gmail.com&redirect=false';
		
		//echo $url . "\n";
		
		$xml = get($url);
		
		//echo $xml . "\n";
		
		if ($xml != '')
		{
			$xml = str_replace(' version="2.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.crossref.org/qrschema/2.0 http://www.crossref.org/schema/queryResultSchema/crossref_query_output2.0.xsd"', '', $xml);
			$xml = str_replace('xmlns="http://www.crossref.org/qrschema/2.0"', '', $xml);
			
			$dom= new DOMDocument;
			$dom->loadXML($xml);
			$xpath = new DOMXPath($dom);
			$xpath_query = '//doi[@type="journal_article"]';
			$nodeCollection = $xpath->query ($xpath_query);
		
			foreach($nodeCollection as $node)
			{
				$doi = $node->firstChild->nodeValue;

				$doi = strtolower($doi);

				$sql = 'UPDATE publications SET doi="' . $doi . '"'
					. ', guid="' . $doi . '"'				
					. ' WHERE guid="' . $guid . '";';
				
				echo $sql . "\n";
				
			}
			
			//print_r($reference);
		
		}
		
	
	
	
	
	
	
	}

	
	$result->MoveNext();
}
	


?>