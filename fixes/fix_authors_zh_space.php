<?php

// fix cases where Chinese authors are separated by 

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');



//----------------------------------------------------------------------------------------
function fix_authors($value)
{
	echo "-- $value\n";
	
	// skip for now
	if (preg_match('/\(/u', $value))
	{
		return $value;
	}
	
	if (preg_match('/\p{Han}+/u', $value))
	{
		// we have Chinese		
		if (strpos($value, ';') === false)
		{
			// no ';'
			$value = preg_replace('/(\p{Han}+)\s+(\p{Han}+)/u', '$1;$2', $value);
			$value = preg_replace('/(\p{Han}+)\s+(\p{Han}+)/u', '$1;$2', $value);

			$value = preg_replace('/(\p{Han})，\s*([A-Z])/u', '$1;$2', $value);
			$value = preg_replace('/([A-Z])，\s*(\p{Han})/u', '$1;$2', $value);
			//$value = preg_replace('/(\p{Han})，\s*(\p{Han}+)/u', '$1;$2', $value);
			
			
			$value = preg_replace('/；/u', ';', $value);
			
			$value = preg_replace('/\s+$/u', '', $value);
			
			//$value = preg_replace('/、/u', ';', $value);
		}
	}
	else
	{
		
		$value = preg_replace('/;，/u', '，', $value);
		
		$value = preg_replace('/\|/u', ';', $value);
		
		$value = preg_replace('/\s+And\s+/u', ';', $value);
	
	
		// Chinese names in English style
		if (strpos($value, ';') === false)
		{
			// clean up hyphens
			$value = preg_replace('/\s+\-\s+/u', '-', $value);
			$value = preg_replace('/\s*[－|-]/u', '-', $value);
			
			
			$value = preg_replace('/\s+And\s+/u', ';', $value);
			$value = preg_replace('/，/u', ';', $value);
			//$value = preg_replace('/，/u', '|', $value);
			
			//  Xu Jian-Chu
			$value = preg_replace('/(([A-Z]\w+) ([A-Z]\w+)(-[A-Z]\w+)?)\s/u', '$1;', $value);
			
			
			



		}	
	
	
	}

	echo "-- $value\n";

	return $value;
}

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


if (0)
{
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
}

if (1)
{

	$sql = 'SELECT * FROM multilingual WHERE guid="' . $guid . '" AND `key`="authors" AND language="zh"';

	// Chinese
	$sql = 'SELECT * FROM multilingual WHERE guid LIKE "http://journal.kib.ac.cn/CN/abstract/abstract%" AND `key`="authors" AND language="zh" AND value NOT LIKE "%;%" AND value like "% %"';

	$sql = 'SELECT * FROM multilingual WHERE guid LIKE "http://journal.kib.ac.cn/CN/abstract/abstract%" AND `key`="authors" AND language="en" AND value like "% and %"';
	
	// English
	/*
	$sql = 'SELECT * FROM multilingual WHERE guid LIKE "http://journal.kib.ac.cn/CN/abstract/abstract%" AND `key`="authors" AND language="en" AND value NOT LIKE "%;%" AND value like "% %"';

	$sql = 'SELECT * FROM multilingual WHERE guid LIKE "http://journal.kib.ac.cn/CN/abstract/abstract%" AND `key`="authors" AND language="en" AND  value like "%，%";';

	$sql = 'SELECT * FROM multilingual WHERE guid LIKE "http://journal.kib.ac.cn/CN/abstract/abstract%" AND `key`="authors" AND language="en" AND  value like "%|%";';
	*/
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$guid = $result->fields['guid'];
		$language = $result->fields['language'];
		$value = $result->fields['value'];
	
		$value_fixed = fix_authors($value);
		
		if ($value != $value_fixed)
		{
			echo 'REPLACE INTO `multilingual`(guid, `key`, language, value) VALUES ("' 
				. $guid . '", "authors", "' . $language . '", "' . addcslashes($value_fixed, '"') . '");' . "\n";
		}
		else
		{
			// echo "Not fixed\n";
		}
	
		$result->MoveNext();
	}
}




?>