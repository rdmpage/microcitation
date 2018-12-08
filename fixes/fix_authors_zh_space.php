<?php

// fix cases where Chinese authors are separated by 

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');



//----------------------------------------------------------------------------------------
function fix_authors($value)
{
	echo "-- $value\n";
	
	if (preg_match('/\p{Han}+/u', $value))
	{
		// we have Chinese		
		if (strpos($value, ';') === false)
		{
			// no ';'
			$value = preg_replace('/\s/u', ';', $value);
		}
	}


	return $value;
}

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$guid = 'http://www.guihaia-journal.com/ch/reader/view_abstract.aspx?file_no=1986Z1003&flag=1';

$guid = 'http://www.guihaia-journal.com/ch/reader/view_abstract.aspx?file_no=20070426&flag=1';

$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

$sql = "select guid, authors from publications where issn='1000-3142' and authors not like '%;%' and authors like '% %';";


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

//if ($result->NumRows() == 1) 
while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$authors = $result->fields['authors'];
	
	
	$authors = fix_authors($authors);
	$language = 'zh';
	
	echo 'UPDATE `publications` SET authors="' . addcslashes($authors, '"') . '" WHERE guid="' . $guid. '";' . "\n";
	echo 'REPLACE INTO `multilingual`(guid, `key`, language, value) VALUES ("' 
		. $guid . '", "authors", "' . $language . '", "' . addcslashes($authors, '"') . '");' . "\n";
	
	
	$result->MoveNext();
}

/*

$sql = 'SELECT * FROM multilingual WHERE guid="' . $guid . '" AND `key`="authors" AND language="zh"';



$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1) 
{
	$guid = $result->fields['guid'];
	$language = $result->fields['language'];
	$value = $result->fields['value'];
	
	$value = fix_authors($value);

	echo 'REPLACE INTO `multilingual`(guid, `key`, language, value) VALUES ("' 
		. $guid . '", "authors", "' . $language . '", "' . addcslashes($value, '"') . '");' . "\n";
	
	$result->MoveNext();
}
*/



?>