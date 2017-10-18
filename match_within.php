<?php


// In cases where we may have duplicate references (e.g., one copy from CrossRef other from journal site)
// use approximate search to match them and merge

// Code will match on title within a year, if one has DOI and other doesn't, will merge
// by adding PDF to DOI rcord, then deleting PDF record.


require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/www/fingerprint.php');
require_once(dirname(__FILE__) . '/www/lcs.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$issn = '0065-1710';
$years = array('2004');

//foreach ($years as $year)
for ($year = 2005; $year < 2017; $year++)
{
	// 1. get 
	$sql = 'SELECT * FROM `publications` WHERE `issn` = "' . $issn . '" AND `doi` IS NOT NULL AND `year`="' . $year . '";';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$source = array();
	
	while (!$result->EOF) 
	{
		$source[$result->fields['doi']] = strip_tags($result->fields['title']);
		
		$result->MoveNext();
	}
	
	//print_r($source);
	
	// lookup 
	
	foreach ($source as $doi => $title)
	{
		$sql = 'SELECT guid, doi, title, pdf, url, MATCH (title) AGAINST ("' 
			. addcslashes($title, '"') 
			. '") AS score FROM publications AS score WHERE MATCH (title) AGAINST ("' 
			. addcslashes($title, '"') . '")';

		$sql .= ' AND `year`="' . $year . '"';
		$sql .= ' AND `issn`="' . $issn . '"';
		$sql .= ' AND `doi` IS NULL';
		$sql .= ' ORDER BY score DESC LIMIT 1;';
		
		//echo $sql . "\n";
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

		while (!$result->EOF) 
		{
			echo "-- $title\n";
			echo "-- " . $result->fields['title'] . "\n";
			
			// check
			$percent = 0;
			similar_text($title, $result->fields['title'], $percent);
			if ($percent > 80)
			{
				echo 'UPDATE `publications` SET pdf="' . $result->fields['pdf'] . '" WHERE `doi`="' . $doi . '";' . "\n";
				echo 'DELETE FROM `publications` WHERE guid="' . $result->fields['pdf'] . '";' . "\n";
			}
		
			$result->MoveNext();
		}
		
		
		
	}
	
	

	
}

exit();

		


?>