<?php

// Fix some things

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');

function find_pdf($filename)
{
	global $db;
	
	$pdf = '';

	$sql = 'SELECT * FROM `raffles-pdf` WHERE pdf LIKE "%' . $filename . '" LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$pdf = $result->fields['pdf'];
	}

	
	return $pdf;
}

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$sql = 'SELECT * FROM publications WHERE journal LIKE "%raffles%" AND pdf IS NOT NULL';
$sql .= ' ORDER BY CAST(volume as SIGNED), CAST(spage as SIGNED)';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$pdf = $result->fields['pdf'];
	
	echo "-- $pdf\n";
	
	$parts = explode("/", $pdf);
	
	$filename = $parts[count($parts) - 1];
	
	echo $filename . "\n";
	
	$new_pdf = find_pdf($filename);
	
	if ($new_pdf == '')
	{
		echo "Not found: $pdf\n";
		exit();
	}
	else
	{
		echo "New PDF=$new_pdf\n";
	}
	
	
	
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