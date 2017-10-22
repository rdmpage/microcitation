<?php

// Fix some things

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$sql = 'SELECT * FROM publications WHERE issn="0749-6737" AND title LIKE "0%"';

$sql .= ' ORDER BY CAST(series as SIGNED), CAST(volume as SIGNED), issue, CAST(spage as SIGNED)';



$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$title = $result->fields['title'];
	
	echo "-- $title\n";
	
	if (preg_match('/(\d+)\.\s+(.*)/u', $title, $m))
	{
		// print_r($m);
		
		$m[2] = preg_replace('/\.$/u', '', $m[2]);
		
		echo 'UPDATE publications SET volume="' . $m[1] . '", title="' . addcslashes($m[2], '"') . '" WHERE guid="' . $guid . '";' . "\n";
	} 
	
	$result->MoveNext();
}
	


?>