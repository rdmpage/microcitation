<?php

// fix cases where we have both multiple languages in title field
// Detect these by testing if string has even number of parts (split on '=') and one half is 
// Japanese the other half English. If so, split by langauge and upldate database.

// example 
// Earwigs (Dermaptera) collected in airplanes and ships called at ports in Japan = 日本に寄航した航空機および船舶の内部にて採集されたハサミムシ類
// http://dl.ndl.go.jp/info:ndljp/pid/10996148#4

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');

require_once '../vendor/autoload.php';
use LanguageDetection\Language;


//----------------------------------------------------------------------------------------
function split_by_language($marker, $value, $languages = array('en', 'es'))
{
	$result = array();
	
	$parts = explode($marker, $value);
	
	// print_r($parts);
	
	if (count($parts) == 1)
	{
		return $result;
	}
	
	$left = $parts[0];
	$right = $parts[1];
	
	$ld = new Language($languages);						
	$language_left = $ld->detect($left)->__toString();
	$language_right = $ld->detect($right)->__toString();

	if ($language_left != $language_right)
	{
		$result[$language_left] = $left;
		$result[$language_right] = $right;
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


$sql = 'SELECT * FROM publications WHERE issn="0253-5696" AND title LIKE "% / %"';



//echo $sql . "\n";

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$marker = " / ";

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$title = $result->fields['title'];
	
	$languages = array('en', 'es');
	
	$r = split_by_language($marker, $title, $languages);
	
	//print_r($r);
		
	
	if (count($r) > 0)
	{
		$count = 0;
		foreach ($r as $language => $string)
		{
			echo 'REPLACE INTO `multilingual`(guid, `key`, language, value) VALUES ("' 
			. $guid . '", "title", "' . $language . '", "' . addcslashes($string, '"') . '");' . "\n";
			
			/*
			if ($count++ == 0)
			{
				echo 'UPDATE `publications` SET title="' . addcslashes($string, '"') . '" WHERE guid="' . $guid. '";' . "\n";
			}
			*/
		}
	}
	
	
	
	$result->MoveNext();
}



?>