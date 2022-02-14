<?php

// Export reference(s) in RIS format
// phtotaxa and zootaxa cites export to do

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
	'pdf_filename' => 'L1',
	);

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 'root', '', 'phytotaxacites');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$sql = 'SELECT * FROM `cites`';

$sql .= ' ORDER BY  title';

$sql .= 'LIMIT 10';

//$sql .= ' LIMIT 1000 OFFSET 1000';



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
				
				
			case 'sha1':
				// http://bionames.org/bionames-archive/pdf/ad/b5/d6/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1.pdf
				$pdf = 'http://bionames.org/bionames-archive/pdfstore?sha1=' . $v;				
				$ris .= "L1  - " . $pdf . "\n";				
				break;
				
			
			case 'pdf':
				// eat
				break;
			
			case 'pdf_filename':
				if ($result->fields['pdf_filename'] != '')
				{
					$ris .= "L1  - file://" . $v . "\n";	
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
						
					
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
				}
				break;
		}
	}
	
	$ris .= "ER  - \n";
	echo $ris . "\n";
	
	$result->MoveNext();
}
	


?>