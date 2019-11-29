<?php

// Add article numbers from URL

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$issn = '0006-8063';


$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';

//echo $sql . "\n";


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$url = $result->fields['pdf'];
	
	if (preg_match('/-0?(?<id>\d+)\.pdf/', $url, $m))
	{
	
		echo 'UPDATE publications SET article_number=' . $m['id'] . ' WHERE guid="' . $result->fields['guid'] . '";' . "\n";
	}

	
	$result->MoveNext();
}
	


?>