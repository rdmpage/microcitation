<?php

// Get one page from PDF

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');


$page = 193;
$pdf = 'http://www.jjbotany.com/getpdf.php?tid=3438';


if (isset($_GET['page']))
{
	$page = $_GET['page'];
}
if (isset($_GET['pdf']))
{
	$pdf = $_GET['pdf'];
}


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

//--------------------------------------------------------------------------------------------------

$sql = 'SELECT * FROM publications  INNER JOIN sha1 USING(pdf) WHERE pdf="' . $pdf . '" LIMIT 1';

//echo $sql;


$page_offset = 0;

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$obj = new stdclass;
$obj->query_page = $page;
$obj->pdf = $pdf;


if ($result->NumRows() == 1)
{
	$obj->sha1 = $result->fields['sha1'];
	
	$obj->spage = $result->fields['spage']; 
	$obj->page = $obj->query_page - $obj->spage + 1; 
	$obj->fragment_identifier = '#' . $obj->page;
	
	$obj->sha1 = $result->fields['sha1'];
	$obj->image = 'http://bionames.org/bionames-archive/documentcloud/pages/' . $obj->sha1 . '/' . $obj->page . '-normal';
}
	
echo json_encode($obj);

?>
