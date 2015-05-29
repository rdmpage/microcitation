<?php

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


//--------------------------------------------------------------------------------------------------
function find ($issn, $volume, $page, $series='', $year = '')
{
	global $db;
	
	$obj = new stdclass;
	$obj->found = false;
	$obj->issn = $issn;
	$obj->page = $page;
	
	if ($year != '')
	{
		$obj->year = $year;
	}
	if ($series != '')
	{
		$obj->series = $series;
	}
	if ($volume != '')
	{
		$obj->volume = $volume;
	}

	$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';
	 
	if (isset($obj->volume))
	{
		$sql .= 'AND volume=' . $volume;
	}
	 
	$sql .= ' AND (' . $page . ' between spage and epage)';
	
	if (isset($obj->year))
	{
		$sql .= ' AND year=' . $year;
	}
	
	if (isset($obj->series))
	{
		$sql .= ' AND series=' . $series;
	}
	
	$obj->sql = $sql;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$obj->found = true;
		if ($result->fields['doi'])
		{
			$obj->doi = $result->fields['doi'];
		}
	}
	
	return $obj;

}


$issn = '';
$volume = '';
$page = '';
$year = '';
$series = '';

if (isset($_GET['issn']))
{
	$issn = $_GET['issn'];
}
if (isset($_GET['volume']))
{
	$volume = $_GET['volume'];
}
if (isset($_GET['page']))
{
	$page = $_GET['page'];
}
if (isset($_GET['year']))
{
	$year = $_GET['year'];
}
if (isset($_GET['series']))
{
	$series = $_GET['series'];
}


$obj = find($issn, $volume, $page, $series, $year);

header("Content-Type:text/plain");
echo json_format(json_encode($obj));

//print_r($obj);

?>