<?php

// Export reference(s) in RIS format

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
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$issn = '0085-4417';
//$issn = '0037-8941';
$issn = '0372-333X';
$issn = '0037-8941'; // Bulletin de la Société Botanique de France

$issn = '0006-8241'; // Bothalia

$issn = '0211-8327';

$issn = '0024-9637'; // Madroño

$issn = '0001-5709';

$issn = '1005-9628';

$issn = '0011-9970';

$issn = '1055-3177';

$issn = '0320-9180';

$issn = '1018-4171';

$issn = '0342-7536';

$issn = '2156-0382';
$issn = '0035-919X';

$issn = '0187-7151';

$issn = '1018-4171';
$issn = '0028-7199';
$issn = '0370-6583';
$issn = '0033-2615';
$issn = '0004-2625';

$issn = '0370-6583';

//$issn = '0187-7151';

$issn = '0013-8746';
$issn = '0084-5604';

$issn = '8756-971X';

$issn = '0372-333X';
$issn = '1253-8078';
$issn = '0374-5481';
$issn = '1560-7259';
$issn = '0451-9930';
$issn = '1808-2688';
$issn = '0260-1230';
$issn = '0025-1194';
$issn = '0005-6219';
$issn = '0031-0239';
$issn = '0375-0183';
$issn = '1805-5648';
$issn = '0043-5643';
$issn = '0022-1511';
$issn = '2156-0382';
$issn = '0187-7151';
$issn = '0033-2615'; // Psyche
$issn = '0028-7199';

$issn = '0323-6145'; // Berliner entomologische Zeitschrift

$issn = '0021-8375';
$issn = '1018-4171';

$issn = '0001-5326';
$issn = '0749-6737'; // Insecta Mundi
$issn = '0313-122X';

$issn = '1929-7890'; // Journal of The Entomological Society of British Columbia
$issn = '0071-0733'; // Journal of The Entomological Society of British Columbia
$issn = '0316-9049';
$issn = '0375-5223'; // Zeitschrift Der Arbeitsgemeinschaft Österreichischer Entomologen
$issn = '1001-4276'; // 武夷科学
$issn = '0506-7839'; // Vertebrata Hungarica
$issn = '0065-1710'; // Acta Zoologica Cracoviensia
$issn = '0072-9027'; // Gulf Research Reports
$issn = '0312-3162'; // records of the Western Australian Museum
//$issn = '1055-3177'; // Novon

$issn = '0026-6493'; // Annals of the Missouri Botanical Garden
$issn = '0312-3162'; // records of the Western Australian Museum

$issn = '0022-2062'; // Journal of Japanese botany
$issn = '1802-6842'; // Journal of The National Museum (Prague), Natural History Series

$issn = '0036-7575'; // Mitteilungen Der Schweizerischen Entomologischen Gesellschaft
$issn = '1815-8242';
$issn = '0161-8202'; // Journal of Arachnology


$issn = '0312-3162'; // records of the Western Australian Museum

//$issn = '0006-8071'; // Botanical Gazette

$issn = '0892-1016'; // Journal of Raptor Research
$issn = '0099-9059'; // Raptor Research
$issn = '0085-4417'; // Nuytsia
$issn = '0374-7859'; // Gardens' Bulletin Singapore

$issn = '0312-3162'; // records of the Western Australian Museum
$issn = '0002-8444'; // American Fern Journal

$issn = '0187-7151'; // Acta Botánica Mexicana

$issn = '0012-0073';
$issn = '0073-4705';

$issn = '1447-2546';

$issn = '0312-3162'; // records of the Western Australian Museum
$issn = '2278-1587'; // Indian Journal of Arachnology
$issn = '0024-9637'; // Madroño
$issn = '0312-3162'; // Records of the Western Australian Museum
//$issn = '0035-922X';

$issn = '0077-1813'; // Muelleria
$issn = '0365-7779'; // Anales de la Universidad de Chile

//$issn = '0250-4413'; // Entomofauna

$issn = '1559-4491'; // The Wilson Journal of Ornithology

$issn = '1018-4171';

$issn = '8756-971X';
$issn = '0044-586X';
$issn = '0340-4943';
$issn = '0740-2783';

$issn ='0312-3162';

$issn ='0034-740X';
$issn ='0312-3162';
$issn ='1684-4130';
$issn ='1815-8242';
$issn ='0077-1813';
$issn ='0811-3653';
$issn ='0077-1813';
$issn ='0187-7151';

$issn ='0379-0207';

$issn ='0311-9548';
$issn ='1447-2546'; // Memoirs of Museum Victoria
//$issn ='0814-1827'; // Memoirs of the Museum of Victoria
//$issn ='0083-5986';
//$issn ='0187-7151';

$issn = '0312-3162';
$issn = '0187-7151'; // Acta Botánica Mexicana
//$issn = '2304-7534';

//$issn = '1280-9659';

$issn ='1833-0290';

$issn = '0067-1975';
$issn = '0374-7859';
$issn = '0863-1867';
$issn = '0187-7151';

$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';


$sql = 'SELECT * FROM publications WHERE doi="10.1636/0161-8202(2002)030[0219:tansgf]2.0.co;2"';
$sql = 'SELECT * FROM publications WHERE pdf="http://zoolstud.sinica.edu.tw/Journals/46.4/454.pdf"';
$sql = 'SELECT * FROM publications WHERE pdf="http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2056(2)/56rbz357-384.pdf"';
$sql = 'SELECT * FROM publications WHERE guid="http://www.zobodat.at/publikation_articles.php?id=237741"';

$sql = 'SELECT * FROM publications WHERE doi="10.24199/j.mmv.2014.72.07"';

$sql = 'SELECT * FROM publications WHERE doi="10.1206/3748.2"';

$sql = 'SELECT * FROM publications WHERE doi="10.3853/j.2201-4349.67.2015.1646"';

//$sql = 'SELECT * FROM publications WHERE journal="Malakozoologische Blätter"';

$sql = 'SELECT * FROM publications WHERE journal ="The Raffles Bulletin of Zoology"';

//$sql = 'SELECT * FROM publications WHERE journal ="Acta Societatis Zoologicae Bohemicae"';
//$sql .= ' AND year = 2005';

//$sql .= ' AND year = 2016';
//$sql .= ' AND year IN (2004, 2005, 2006)';
//$sql .= ' AND volume = 23 AND ISSUE IS NOT NULL';

//$sql .= ' AND year < 1901 and volume <> 0 AND spage is not null';

$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';

//$sql .= ' AND volume IN(1,2)';

//$sql = 'SELECT * FROM publications where journal like "Studies and%"';

$sql .= ' AND year >= 2017';
//$sql .= ' and spage <> 0';

//$sql .= ' AND doi IS NULL and year > 2011';



$sql .= ' ORDER BY CAST(series as SIGNED), CAST(volume as SIGNED), issue, CAST(spage as SIGNED)';



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
	
	$ris .= "ER  - \n";
	echo $ris . "\n";
	
	$result->MoveNext();
}
	


?>