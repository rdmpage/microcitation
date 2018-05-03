<?php

// Generate list of unique DOI prefixes and an exemplar DOI

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$prefixes = array();




$sql = 'SELECT DISTINCT LEFT(doi,INSTR(doi,"/")-1) AS prefix FROM publications;';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$prefixes[] = $result->fields['prefix'];
	
	$result->MoveNext();
}

$dois = array();

foreach ($prefixes as $prefix)
{
	$sql = 'SELECT doi FROM publications WHERE doi LIKE "' . $prefix . '%" LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$doi = $result->fields['doi'];
		echo "$doi\n";
		
		$dois[] = '"' . $doi . '"';
	}

}

echo '$dois=array(' . "\n";
echo join(",\n", $dois);
echo ");\n";
	


?>