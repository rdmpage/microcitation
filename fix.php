<?php

// Merge duplicate references into a single record (e.g, from two different sources)
// and export as RIS

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');require_once(dirname(__FILE__) . '/config.inc.php');



//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


// > 89

// Journal of The Royal Society of Western Australia
$sql = 'SELECT * FROM publications WHERE issn="0035-922X" AND volume = 96';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$issn = $result->fields['issn'];
	$volume = $result->fields['volume'];
	$pdf = $result->fields['pdf'];
	
	if ($pdf != '' && $issn != '' && $volume != '')
	{
		// http://www.rswa.org.au/publications/Journal/96(1)/ROY%20SOC%2096.1%20OBIT%20DE%20LAETER%20P29.pdf
		// http://www.rswa.org.au/publications/Journal/96(2)/ROY%20SOC%2096.2%20SAEFURAHMAN%20ET%20AL%2077.pdf
		// http://www.rswa.org.au/publications/Journal/96(2)/ROY%20SOC%2096.2%20ROZAIMI%20ET%20AL%2081-83.pdf
		if (preg_match('/%20[P]?(?<spage>\d+)(-(?<epage>\d+))(%20LOW)?\.pdf/', $pdf, $m))
		{
			$spage = $m['spage'];
			$epage = $m['epage'];
			
			$sql = 'UPDATE publications SET epage=' . $epage . ' WHERE issn="' . $issn . '" AND volume=' . $volume . ' AND spage=' . $spage . ';';
			
			echo $sql . "\n";
			
			
		
		}
	
	}




	$result->MoveNext();
}


// export



?>