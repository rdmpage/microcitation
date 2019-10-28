<?php

// Export reference(s) Mardown format

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$sql = 'SELECT * FROM rdmpage ';

$sql .= ' ORDER BY year DESC';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$md = array();
	
	if ( $result->fields['authors'] != '')
	{
		$md[] = $result->fields['authors'];
	}	

	if ( $result->fields['year'] != '')
	{
		$md[] = '(' . $result->fields['year'] . ')';
	}	

	if ( $result->fields['title'] != '')
	{
		$md[] = $result->fields['title'];
	}	

	if ( $result->fields['journal'] != '')
	{
		$md[] = '_' . $result->fields['journal'] . '_';
	}	

	if ( $result->fields['volume'] != '')
	{
		$md[] = $result->fields['volume'];
	}	

	if ( $result->fields['spage'] != '')
	{
		$md[] = $result->fields['spage'];
	}	

	if ( $result->fields['epage'] != '')
	{
		$md[] = '- ' . $result->fields['epage'];
	}	

	if ( $result->fields['doi'] != '')
	{
		$md[] = 'doi:[' . $result->fields['doi'] . '](https://doi.org/' . $result->fields['doi'] . ')';
	}	

	if ( $result->fields['isbn13'] != '')
	{
		$md[] = 'isbn:' . $result->fields['isbn13'];
	}	

	if ( $result->fields['type'] != '')
	{
		$md[] = '**' . $result->fields['type'] . '**';
	}	


	//print_r($md);
	
	echo join(' ', $md) . "\n";

	
	$result->MoveNext();
}
	


?>