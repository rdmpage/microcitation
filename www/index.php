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
function display_form()
{
	echo 
'<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<style type="text/css" title="text/css">
	body {
		font-family: sans-serif;
		margin:20px;
		}
</style>
<title>Microcitation</title>
</head>
<body>
<h1>Microcitation</h1>
<p>Service to locate a microcitation</p>
<form method="get" action=".">
	ISSN<br>
	<input type="text" name="issn" value="0019-1019">
	<br>
	Volume<br>
	<input type="text" name="volume" value="99">
	<br>
	Page<br>
	<input type="text" name="page" value="275">
	<br>
	Year<br>
	<input type="text" name="year" value="">
	<br>
	Series<br>
	<input type="text" name="series" value="">
	<br>
	<input type="submit" value="Go"></input>
</form>
</body>
</html>';
}


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
		$sql .= ' AND volume=' . $volume;
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
	
	$obj->results = array();
	
	while (!$result->EOF) 
	{
		$hit = new stdclass;
	
		if ($result->fields['doi'])
		{
			$hit->doi = $result->fields['doi'];
		}
		if ($result->fields['handle'])
		{
			$hit->handle = $result->fields['handle'];
		}		
		if ($result->fields['jstor'])
		{
			$hit->jstor = $result->fields['jstor'];
		}
		if ($result->fields['pdf'])
		{
			$hit->pdf = $result->fields['pdf'];
		}
		if ($result->fields['url'])
		{
			$hit->url = $result->fields['url'];
		}
				
		$obj->results[] = $hit;
		
		$result->MoveNext();
	}
	
	$obj->found = count($obj->results) > 0;
	
	return $obj;

}






function main()
{
	$text = '';
	$format = '';
	if (isset($_GET['issn']) 
		&& (isset($_GET['volume']) || isset($_GET['year']))
		&& isset($_GET['page']))
	{
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
	}
	else
	{
		display_form();
	}
}
	
main();


?>