<?php

// Export reference(s) in RIS format

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');

function fix_latin1_mangled_with_utf8_maybe_hopefully_most_of_the_time($str)
{
    return preg_replace_callback('#[\\xA1-\\xFF](?![\\x80-\\xBF]{2,})#', 'utf8_encode_callback', $str);
}

function utf8_encode_callback($m)
{
    return utf8_encode($m[0]);
}

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
	'doi'		=> 'DO'
	);

$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';

//$guid = '';

$table = 'publications';

if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
}

if (isset($_GET['table']))
{
	$table = $_GET['table'];
}

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

//$db->EXECUTE("set publications 'utf8'"); 
$db->EXECUTE("set names 'utf8'"); 



//--------------------------------------------------------------------------------------------------

$sql = 'SELECT * FROM ' . $table . ' WHERE guid="' . $guid . '"';



if (!preg_match('/^10\./', $guid))
{	
	if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>\d+)/', $guid, $m))
	{
		$sql .= ' OR jstor=' . $m['id'];
	}
	else
	{
	
		if (preg_match('/^http:\/\//', $guid))
		{
			$sql .= ' OR url="' . $guid . '"';
		}
	}
}

// Wikidata
if (preg_match('/^Q\d+$/', $guid))
{
	$sql = 'SELECT * FROM  ' . $table . '  WHERE wikidata="' . $guid . '"';
}


$sql .= ' LIMIT 1;';

//echo $sql;

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$ris = '';
	
	$ris .= "TY  - JOUR\n";
	
	if ( $result->fields['oai'] != '')
	{
		$ris .= "ID  - " . $result->fields['oai'] . "\n";	
	}
	else
	{ 
		$ris .= "ID  - " . $result->fields['guid'] . "\n";
	}


	foreach ($result->fields as $k => $v)
	{
		switch ($k)
		{
			case 'authors':
			//echo $v . "\n";
				if ($v != '')
				{
					$authors = preg_split("/;/u", $v);
					foreach ($authors as $a)
					{
						$ris .= "AU  - " . $a ."\n";
						//$ris .= "AU  - " . $a ."\n";
					}
					//$ris .= $authors[0] . "\n";
				}
				break;
				
			case 'year':
					if ($result->fields['date'] == '')
					{
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
					else
					{
						$ris .= "PY" . "  - " . str_replace('-', '/', $result->fields['date']) . "/\n";
					}
				break;
				
				/*
			case 'sha1':
				// http://bionames.org/bionames-archive/pdf/ad/b5/d6/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1.pdf
				$pdf = 'http://bionames.org/bionames-archive/pdfstore?sha1=' . $v;				
				$ris .= "L1  - " . $pdf . "\n";				
				break;
				*/
				
			case 'pdf':
				if ($result->fields['sha1'] == '')
				{
					$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
				}
				break;
			
			
				
			default:
				if ($v != '')
				{
					if (isset($field_to_ris_key[$k]))
					{
						// clean
						//$v = preg_replace('/\s＂\s/u', '"', $v);
						
						if ($k == 'journal')
						{
							if ($result->fields['series'] != '')
							{
								$v .= ' series ' . $result->fields['series'];
							}
						}
						
					
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
				}
				break;
		}
	}
	
	$ris .= "ER  - \n\n";
	
	header("Content-type: text/plain");
	echo $ris . "\n";
	
	$result->MoveNext();
}
	
}

?>