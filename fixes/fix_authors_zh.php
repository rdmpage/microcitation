<?php

// fix cases where we have both English and Chinese names in author field
// Sometimes Wangfang data conacatenates Chinese and English name sof authors into one string.
// Detect these by testing if string has even number of parts (split on ';') and one half is 
// Chinese the other half English. If so, split by langauge and upldate database.

// example 钟应洪;黎祖群;高向雄;Zhong Yinghong;Li Zuqun;Gao Xiangxiong 10.3969/j.issn.1003-4692.1999.05.013

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');

//----------------------------------------------------------------------------------------
function split_by_language($value)
{
	$result = array();
	
	$parts = explode(';', $value);
	
	//print_r($parts);
	
	$n = count($parts);

	// even
	if ($n % 2 == 0)
	{
		$half = $n/2;
	
		$left = '';
		$right = '';
		
		$left = join(";", array_splice($parts, 0, $half));
		$right = join(";", $parts);
	
	
		$language_left = 'en';
		$language_right = 'en';
	
		if (preg_match('/\p{Han}+/u', $left))
		{
			$language_left = 'zh';
		}
		if (preg_match('/\p{Han}+/u', $right))
		{
			$language_right = 'zh';
		}
		
		if ($language_left != $language_right)
		{
			$result[$language_left] = $left;
			$result[$language_right] = $right;
		}
	
	}	


	return $result;

}

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$sql = 'SELECT * FROM multilingual WHERE guid="10.3969/j.issn.1003-4692.1999.05.013" AND `key`="authors"';

$sql = 'SELECT * FROM multilingual WHERE guid LIKE "10.3969/j.issn.1003-4692%" AND `key`="authors"';

$sql = 'SELECT * FROM multilingual WHERE guid LIKE "10.3969/j.issn.1000-1565%" AND `key`="authors"';

$sql = 'SELECT * FROM multilingual WHERE guid LIKE "10.3969/j.issn.1000-3142%" AND `key`="authors"';

$sql = 'SELECT * FROM multilingual WHERE guid LIKE "10.3969/j.issn.1005-9628%" AND `key`="authors"';


//echo $sql . "\n";

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$value = $result->fields['value'];
	
	$r = split_by_language($value);
	
	if (count($r) > 0)
	{
		$count = 0;
		foreach ($r as $language => $string)
		{
			echo 'REPLACE INTO `multilingual`(guid, `key`, language, value) VALUES ("' 
			. $guid . '", "authors", "' . $language . '", "' . addcslashes($string, '"') . '");' . "\n";
			
			if ($count++ == 0)
			{
				echo 'UPDATE `publications` SET authors="' . addcslashes($string, '"') . '" WHERE guid="' . $guid. '";' . "\n";
			}
		}
	}
	
	
	$result->MoveNext();
}



?>