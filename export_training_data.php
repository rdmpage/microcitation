<?php

// Export reference(s) in RIS format

// Create subsets of data for training, e.g. CRF++

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
	'doi'		=> 'DO'
	);

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


// citation parsing
$sql = 'SELECT * FROM publications WHERE title LIKE "%Mr.%" LIMIT 10;';
//$sql = 'SELECT * FROM publications WHERE title LIKE "%n. sp.%" LIMIT 100;';
$sql = 'SELECT * FROM publications WHERE title REGEXP "[0-9]{4} " AND authors IS NOT NULL AND authors <> "" AND authors NOT LIKE "%anon%" LIMIT 100;';

$sql_list = array();
$sql_list[] = $sql;

foreach ($sql_list as $sql)
{

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$ris = '';
	
		$ris .= "TY  - JOUR\n";
	
		$ris .= "ID  - " . $result->fields['guid'] . "\n";

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
				
				case 'sha1':
					// http://bionames.org/bionames-archive/pdf/ad/b5/d6/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1.pdf
					$pdf = 'http://bionames.org/bionames-archive/pdfstore?sha1=' . $v;				
					$ris .= "L1  - " . $pdf . "\n";				
					break;
				
				case 'pdf':
					// check if local file stored in PII
					if ($result->fields['pii'] != '')
					{
						//$ris .= $field_to_ris_key[$k] . "  - " . $result->fields['pii'] . "\n";
						$ris .= $field_to_ris_key[$k] . "  - file://" . $result->fields['pii'] . ".pdf" . "\n";
					
					}
					else
					{
						if ($result->fields['sha1'] == '')
						{
							$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
						
						
						}
					}
					break;
				
				case 'spage':
				case 'epage':
					if ($no_pages)
					{
						// eat as data is bad
					}
					else
					{
						if ($v != '')
						{
							$ris .= $field_to_ris_key[$k] . "  - " .  $v . "\n"; 					
						}
					}
					break;
				
				default:
					if ($v != '')
					{
						if (isset($field_to_ris_key[$k]))
						{
							// clean
							//$v = preg_replace('/\s＂\s/u', '"', $v);
							
							$v = strip_tags($v);
						
							if ($k == 'journal')
							{
								if ($result->fields['series'] != '')
								{
									$v .= ' series ' . $result->fields['series'];
								}
							}
						
					
							$ris .= $field_to_ris_key[$k] . "  - " .  html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\n"; 
						}
					}
					break;
			}
		}
	
		$ris .= "ER  - \n";
		echo $ris . "\n";
	
		$result->MoveNext();
	}
}	


?>