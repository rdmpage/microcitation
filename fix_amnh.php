<?php

// Fix Bull AMNH

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$sql = 'SELECT * FROM publications WHERE issn="0003-0090" and spage ="--"';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$pages = $result->fields['abstract'];
	$epage = '';
	
	$sql = '';
	
	echo "-- $pages\n";
	
	
	$matched = false;
	
	// p. [119]-122 ; 24 cm.
	if (!$matched)
	{
		if (preg_match('/p.\s+\[(?<spage>\d+)\]-\[?(?<epage>\d+)/', $pages, $m))
		{
			$spage = $m['spage'];
			$epage = $m['epage'];
			
			echo "-- $spage-$epage|\n";
			
			$sql = 'UPDATE publications SET spage="' . $spage . '", epage="' . $epage . '" WHERE guid="' . $guid . '";';
			
			$matched = true;
		}
	}
	
	// 809 p. ;
	if (!$matched)
	{
		if (preg_match('/(?<epage>\d+)\s+p.\s*[,|:|;]/', $pages, $m))
		{
			$spage = 1;
			$epage = $m['epage'];
			
			echo "-- $spage-$epage|\n";
			
			$sql = 'UPDATE publications SET spage="' . $spage . '", epage="' . $epage . '" WHERE guid="' . $guid . '";';
			
			$matched = true;
		}
	}
	
	// p. 161 ;
	if (!$matched)
	{
		if (preg_match('/p.\s+(?<epage>\d+)\s*[;]/', $pages, $m))
		{
			$spage = 1;
			$epage = $m['epage'];
			
			echo "-- $spage-$epage|\n";
			
			$sql = 'UPDATE publications SET spage="' . $spage . '", epage="' . $epage . '" WHERE guid="' . $guid . '";';
			
			$matched = true;
		}
	}

	if ($matched)
	{
		
		echo $sql . "\n";
	}
	
	$result->MoveNext();
}
	


?>