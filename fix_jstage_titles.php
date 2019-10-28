<?php

// Export reference(s) in RIS format

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$issn = '0006-808X';


$sql = 'SELECT * FROM publications WHERE issn ="0006-808X" AND title LIKE "%^%"';
$sql = 'SELECT * FROM publications WHERE issn ="0006-808X" AND title LIKE "%;%"';


$bugs = array();


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	
	$title = $result->fields['title'];
	
	// echo $title . "\n";
	
	if (preg_match('/(\^\|\^[a-z]+);/i', $title, $m))
	{
		//print_r($m);
		$bugs[] = $m[0];
		
		$title = str_replace('^|^', '&', $title);
		
		$title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		
		echo "-- $title\n";
		
		echo 'UPDATE publications SET title="' . addcslashes($title, '"') . '" WHERE guid="' . $guid . '";' . "\n";
		
	}
	
	if (preg_match('/(&[a-z]+);/i', $title, $m))
	{
		//print_r($m);
		$bugs[] = $m[0];
		
		$title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		
		echo "-- $title\n";
		
		echo 'UPDATE publications SET title="' . addcslashes($title, '"') . '" WHERE guid="' . $guid . '";' . "\n";
		
	}	

	
	
	$result->MoveNext();
}
/*
$bugs = array_unique($bugs);

print_r($bugs);

/
foreach ($bugs as $b)
{
	echo "'$b' => '',\n";

}
*/
	


?>