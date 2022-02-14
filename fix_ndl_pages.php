<?php

// Fix NDL pages that I parsed wrongly

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$sql = 'SELECT * FROM publications WHERE issn="0013-8770" and series IS NOT NULL AND wikidata IS NOT NULL';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid 		= $result->fields['guid'];
	$wikidata 	= $result->fields['wikidata'];
	$series 	= $result->fields['series'];
	
	$parts = explode("|", $series);
	
	if (count($parts) == 2)
	{
		//$quickstaments = array();
		
		$w = array();
		
		$w[] = array('-' . $wikidata, 'P304', '"' . $parts[0] . '"');
		$w[] = array($wikidata, 'P304', '"' . $parts[1] . '"', 'S854', '"' . $guid . '"');
		
		// print_r($w);
		
		$quickstatments = '';
		
		foreach ($w as $st)
		{
			$quickstatments .= join("\t", $st) . "\n";
		}
		
		
		echo $quickstatments;
	
	
	}
	

	
	$result->MoveNext();
}
	


?>