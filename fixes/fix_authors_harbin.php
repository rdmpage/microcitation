<?php

// fix cases authors in bulletin of botanical research harbin 植物研究

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');



//----------------------------------------------------------------------------------------
function fix_authors($value)
{
	echo "-- $value\n";
	
	if (preg_match('/&/u', $value))
	{
		$value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
	
	$value = preg_replace('/\*/u', '', $value);
	
	$value = preg_replace('/\d(,\d)*/u', '', $value);


	return $value;
}


//----------------------------------------------------------------------------------------
function commas_to_semicolons($value)
{
	echo "-- $value\n";
	
	
	$value = preg_replace('/,\s*/u', ';', $value);

	return $value;
}


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$guid = '10.7525/j.issn.1673-5102.2017.01.015';
$guid = '10.7525/j.issn.1673-5102.2015.04.020';
$guid = '10.7525/j.issn.1673-5102.2015.04.016';
$guid = '10.7525/j.issn.1673-5102.2015.05.004';


$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

$pattern = ' LIKE "%#%"';
$pattern = ' LIKE "%*%"';
$pattern = ' LIKE "%1%"';
$pattern = ' LIKE "%,%"';

$sql = 'SELECT * FROM publications WHERE issn="1673-5102" AND authors ' . $pattern;

//$sql = "select guid, authors from publications where issn='1000-3142' and authors not like '%;%' and authors like '% %';";


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$authors = $result->fields['authors'];
	
	// author strings
	echo "-- $authors\n";
		
	$authors = fix_authors($authors);
	
	$authors = commas_to_semicolons($authors);
	
	echo "-- $authors\n";
	
	echo 'UPDATE publications SET authors="' . addcslashes($authors, '"') . '" WHERE guid="' . $guid . '";' . "\n";

	
	$result->MoveNext();
}

//exit();

// multilingual
$sql = 'SELECT * FROM multilingual WHERE guid="' . $guid . '" and `key`="authors"';

$sql = 'SELECT * FROM multilingual WHERE (guid LIKE "10.7525/j.issn.1673-5102%" OR guid LIKE "http://bbr.nefu.edu.cn%") AND `key`="authors" AND value ' . $pattern;




//$sql = "select guid, authors from publications where issn='1000-3142' and authors not like '%;%' and authors like '% %';";


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid 		= $result->fields['guid'];
	$authors	= $result->fields['value'];
	$language	= $result->fields['language'];
	
	// author strings
	echo "-- $authors\n";
		
	$authors = fix_authors($authors);
	$authors = commas_to_semicolons($authors);

	
	echo "-- $authors\n";
	
	echo 'UPDATE multilingual SET value="' . addcslashes($authors, '"') . '" WHERE guid="' . $guid . '" AND `key`="authors" AND language="' . $language . '";' . "\n";
	

	
	$result->MoveNext();
}




?>