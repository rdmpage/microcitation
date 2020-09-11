<?php

// Fix some things

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$sql = 'SELECT * from publications where issn="0374-6429" and volume is NULL';
$sql = 'SELECT * from publications where issn="0374-6429" and spage is NULL';
$sql = 'SELECT * from publications where issn="0374-6429" and authors LIKE "%.,%"';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$authors = $result->fields['authors'];
	
	$pdf = $result->fields['pdf'];
	
	echo "-- $authors\n";
	
	
	// biologie/48-
	/*
	if (preg_match('/biologie\/(?<volume>\d+)/', $pdf, $m))
	{
		echo 'UPDATE publications SET volume="' . $m['volume'] . '" WHERE pdf="' . $pdf . '";' . "\n";
	}
	*/
	/*
	if (preg_match('/(?<spage>\d+)-(?<epage>\d+)\.pdf/', $pdf, $m))
	{
		echo 'UPDATE publications SET spage="' . $m['spage'] . '" WHERE pdf="' . $pdf . '";' . "\n";
		echo 'UPDATE publications SET epage="' . $m['epage'] . '" WHERE pdf="' . $pdf . '";' . "\n";
	}
	*/
	
	$parts = explode(';', $authors);
	$n = count($parts);
	
	$new = array();
	
	for ($i = 0; $i < $n; $i++)
	{
		$p = explode('., ', $parts[$i]);
		$p = array_reverse($p);
		// print_r($p);
		
		$new[] = join(", ", $p);
	}
	
	//print_r($new);
	echo "-- " . join(';', $new) . "\n\n";
	
	
		
	echo 'UPDATE publications SET authors="' . join(';', $new) . '" WHERE guid="' . $guid . '";' . "\n";
	
	
	
	
	/*
	if (preg_match('/(\d+)\.\s+(.*)/u', $title, $m))
	{
		// print_r($m);
		
		$m[2] = preg_replace('/\.$/u', '', $m[2]);
		
		echo 'UPDATE publications SET volume="' . $m[1] . '", title="' . addcslashes($m[2], '"') . '" WHERE guid="' . $guid . '";' . "\n";
	} 
	*/
	
	$result->MoveNext();
}
	


?>