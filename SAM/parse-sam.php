<?php


require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$filename = "rsam.txt";
$filename = "trssa.txt";

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
		
	echo "-- $line\n";

	// http://www.samuseum.sa.gov.au/Journals/RSAM/RSAM_V001/rsam_v001_p293p324
	// http://www.samuseum.sa.gov.au/Journals/TRSSA/TRSSA_V030/TRSSA_V030_p118p142.pdf

	//if (preg_match('/rsam_v0*(?<volume>\d+)(_\d+)?_p0*(?<spage>\d+)p0*(?<epage>\d+)/u', $line, $m))
	if (preg_match('/TRSSA_v0*(?<volume>\d+)_p0*(?<spage>\d+)p0*(?<epage>\d+)/iu', $line, $m))
	{
		//print_r($m);
		
		$keys = array('journal','issn','volume','spage','epage','pdf','guid');
		$values = array();
		
		$values[] = '"Transactions of the Royal Society of South Australia"';
		$values[] = '"' . "0372-1426" . '"';
		$values[] = $m['volume'];
		$values[] = $m['spage'];
		$values[] = $m['epage'];
		$values[] = '"' . $line . '"';
		$values[] = '"' . $line . '"';
		
		
		$sql = 'SELECT * FROM publications WHERE issn="0372-1426" and volume=' . $m['volume'] 
			. ' and spage=' . $m['spage'];
			
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		
		if ($result->NumRows() == 0)
		{
			echo "-- Not found\n";
			echo 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES(' . join(',', $values) . ');' . "\n";	
		}
		else
		{
		}



		
		
		
		
		//echo 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES(' . join(',', $values) . ');' . "\n";
	}
	else
	{
		echo "*** Error ***\n";
		echo $line . "\n";
		exit();
		
	}
	
}	

