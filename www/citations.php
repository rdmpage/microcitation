<?php

// Export citations

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');

//----------------------------------------------------------------------------------------
function fix_latin1_mangled_with_utf8_maybe_hopefully_most_of_the_time($str)
{
    return preg_replace_callback('#[\\xA1-\\xFF](?![\\x80-\\xBF]{2,})#', 'utf8_encode_callback', $str);
}

function utf8_encode_callback($m)
{
    return utf8_encode($m[0]);
}




$guid = '10.11646/phytotaxa.186.4.4';

if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
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

// reference
$citeproc_obj = array();

//$citeproc_obj['id'] = $guid;

if (preg_match('/^10./', $guid))
{
	$citeproc_obj['DOI'] = strtolower($guid);
}

$citeproc_obj['type'] = 'article-journal';

// references cited
$citeproc_obj['reference'] = array();



$sql = 'SELECT * FROM cites WHERE guid="' . $guid . '"';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$reference = new stdclass;
	
	$reference->key = $result->fields['key'];	
	
	if ($result->fields['doi'] != '')
	{
		$reference->DOI = strtolower($result->fields['doi']);
	}
	
	if ($result->fields['rdmp_doi'] != '')
	{
		$reference->DOI = strtolower($result->fields['rdmp_doi']);
	}

	if ($result->fields['unstructured'] != '')
	{
		$reference->unstructured = $result->fields['unstructured'];
	}

	if ($result->fields['article-title'] != '')
	{
		$reference->{'article-title'} = $result->fields['article-title'];
	}

	if ($result->fields['journal-title'] != '')
	{
		$reference->{'journal-title'} = $result->fields['journal-title'];
	}
	
	if ($result->fields['volume-title'] != '')
	{
		$reference->{'volume-title'} = $result->fields['volume-title'];
	}

	if ($result->fields['series-title'] != '')
	{
		$reference->{'series-title'} = $result->fields['series-title'];
	}
	
	if ($result->fields['issn'] != '')
	{
		$reference->ISSN = $result->fields['issn'];
	}

	if ($result->fields['volume'] != '')
	{
		$reference->volume = $result->fields['volume'];
	}

	if ($result->fields['first-page'] != '')
	{
		$reference->{'first-page'} = $result->fields['first-page'];
	}

	if ($result->fields['last-page'] != '')
	{
		$reference->{'last-page'} = $result->fields['last-page'];
	}
	
	if ($result->fields['year'] != '')
	{
		$reference->year = $result->fields['year'];
	}

	$citeproc_obj['reference'][] = $reference;

	$result->MoveNext();	
	

}

echo json_encode($citeproc_obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>