<?php

// Parse a TSV file and match references against microcitation

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

require_once(dirname(__FILE__) . '/www/lcs.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$filename = 'Annals and Magazine of Natural History_view.tsv';
$filename = 'bibliography_view.tsv';
$filename = 'query_result.tsv';

$count = 0;

$keys = array();
$key_to_index = array();

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = trim(fgets($file_handle));
		
	$parts = explode("\t",$row);
	
	if ($count == 0)
	{
		$keys = $parts;
		
		$n = count($keys);
		for ($i = 0; $i < $n; $i++)
		{
			$key_to_index[$keys[$i]] = $i;
		}
	}
	else
	{
		// row of data
		
		//print_r($parts);
		
		$title = $parts[$key_to_index['PUB_TITLE']];
		
		echo "-- $title\n";
		
		$k = array('PUB_YEAR', 'issn','series', 'volume', 'spage');
		$k = array('issn','series', 'volume', 'spage');
		$t = array(
			'PUB_YEAR' => 'year',
			'issn' => 'issn',
			'series' => 'series',
			'volume' => 'volume',
			'spage' => 'spage',
			'epage' => 'epage'
		);
		
		$q = array();
		
		foreach ($k as $key)
		{
			//echo $key . "\n";
			//echo $t[$key] . "\n";
			//echo $parts[$key_to_index[$key]] . "\n";
			
			if (($parts[$key_to_index[$key]] != '') && ($parts[$key_to_index[$key]] != 'NULL'))
			{			
				$q[] = $t[$key] . '="' . addcslashes($parts[$key_to_index[$key]], '"') . '"';
			}
		}
		
		//print_r($q);
		
		
		
		
		$sql = 'SELECT * FROM publications WHERE ' . join(' AND ', $q) . ';';
		
		echo "-- $sql\n";
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

		if ($result->NumRows() == 1) 
		{
			$doi = $result->fields['doi'];
			
			echo "-- $doi\n";
			
			echo "UPDATE bibliography SET doi='$doi' WHERE PUBLICATION_GUID='" . $parts[$key_to_index['PUBLICATION_GUID']] . "';" . "\n";
		}	
		else
		{
			echo "-- NOT FOUND\n";
		
		}	
		echo "\n";
		
	
	
	}
	
	
	$count++;
	
	if ($count == 1000)
	{
		//exit();
	}
	

	

	
}


?>
