<?php

// Export reference(s) in RIS format with a preferred language

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

$field_to_ris_key = array(
	'title' 	=> 'TI',
	'journal' 	=> 'JO',
	'issn' 		=> 'SN',
	'volume' 	=> 'VL',
	'issue' 	=> 'IS',
	'spage' 	=> 'SP',
	'epage' 	=> 'EP',
	'year' 		=> 'Y1',
	'abstract'	=> 'N2',
	'url'		=> 'UR',
	'pdf'		=> 'L1',
	'doi'		=> 'DO',
	'publisher_id'		=> 'ID'
	);

//---------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$sql = "SELECT * FROM publications INNER JOIN multilingual USING (guid) WHERE issn='0037-8844'";
$sql = "SELECT * FROM publications WHERE journal='Atti Della Societa Italiana Di Scienze Naturali Milano'";

//$sql .= " AND year BETWEEN 1980 AND 1989";
//$sql .= " AND year BETWEEN 1970 AND 1979";
//$sql .= " AND year BETWEEN 1950 AND 1969";
//$sql .= " AND year BETWEEN 1940 AND 1949";
//$sql .= " AND year BETWEEN 1930 AND 1939";
$sql .= " AND year < 1930";


$sql .= ' ORDER BY CAST(series as SIGNED), CAST(volume as SIGNED), issue, CAST(spage as SIGNED)';

//---------------------------------------------------------------------------------------

$preferred_language = 'it';

$references = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$keys = array('title', 'authors', 'journal', 'issn', 'volume', 'issue', 'spage', 'epage', 'year', 'key', 'guid', 'value');

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	
	if (!isset($references[$guid]))
	{
		$references[$guid] = new stdclass;
	}
	
	foreach ($keys as $k)
	{
		if ($result->fields[$k] != '' )
		{
			switch ($k)
			{
				case 'journal':
					$references[$guid]->{$k} = $result->fields[$k];
					if ($result->fields[$k] = 'Atti Della Societa Italiana Di Scienze Naturali Milano')
					{
						$references[$guid]->issn = '0037-8844';
					}
					break;
					
				case 'issn':
				case 'volume':
				case 'issue':
				case 'spage':
				case 'epage':
				case 'year':
					$references[$guid]->{$k} = $result->fields[$k];
					break;	
					
				case 'authors':
					$references[$guid]->{$k} = explode(';', $result->fields[$k]);
					break;
					
				case 'guid':
					$references[$guid]->publisher_id = $guid;
					break;
					
				case 'title':
					if (!isset($references[$guid]->{$k}))
					{
						$references[$guid]->{$k} = $result->fields[$k];
					}
					break;
					
				case 'value':
					if ($result->fields['language'] == $preferred_language)
					{
						$references[$guid]->title = $result->fields[$k];
					}
					break;
			
				default:
					break;
			}
		}
	
	}


	$result->MoveNext();
}

//print_r($references);

foreach ($references as $reference)
{
	// print_r($reference);

	$ris = '';	
	$ris .= "TY  - JOUR\n";

	foreach ($reference as $k => $v)
	{
		//echo $k . "\n";
		switch ($k)
		{
				
			case 'authors':
				foreach ($v as $a)
				{
					$ris .= "AU  - " . $a ."\n";
				}
				break;
				
			default:
				$ris .= $field_to_ris_key[$k] . "  - " .  html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\n"; 
				break;
		}
	}
	
	$ris .= "ER  - \n";
	echo $ris . "\n";

}



?>