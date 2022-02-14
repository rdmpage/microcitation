<?php

//error_reporting(E_ALL);


require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(__FILE__) . '/fingerprint.php');
//require_once(dirname(__FILE__) . '/lcs.php');

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

//-----------------------------------------------------------------------------------------
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
	Article title<br>
	<input type="title" name="title" value="">
	<br>
	ISSN<br>
	<input type="text" name="issn" value="" placeholder="0019-1019">
	<br>
	Volume<br>
	<input type="text" name="volume" value="" placeholder="99">
	<br>
	Issue<br>
	<input type="text" name="issue" value="">
	<br>
	Page<br>
	<input type="text" name="page" value="" placeholder="275">
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

//----------------------------------------------------------------------------------------
function find_from_title ($title, $filters = null)
{

	global $db;
	
	$obj = new stdclass;
	$obj->found = false;
	$obj->title = $title;

	$sql = 'SELECT guid, doi, title, pdf, url, cinii, MATCH (title) AGAINST ("' . addcslashes($title, '"') . '") AS score FROM publications AS score WHERE MATCH (title) AGAINST ("' . addcslashes($title, '"') . '")';
	
	if ($filters)
	{
		foreach ($filters as $k => $v)
		{
			if ($v != '')
			{
				$sql .= ' AND `' . $k . '`="' . $v . '"';
			}
		}
	}
	
	
	$sql .= ' ORDER BY score DESC LIMIT 1;';
	
	

	$obj->sql = $sql;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$obj->results = array();
	
	//echo '<pre>';
	//print_r($result);
	//echo '</pre>';
	
	while (!$result->EOF) 
	{
		$hit = new stdclass;
	
		if (isset($result->fields['title']))
		{
			$hit->title = $result->fields['title'];
		}
			
		if (isset($result->fields['doi']))
		{
			$hit->doi = $result->fields['doi'];
		}
		if (isset($result->fields['handle']))
		{
			$hit->handle = $result->fields['handle'];
		}		
		if (isset($result->fields['jstor']))
		{
			$hit->jstor = $result->fields['jstor'];
		}
		if (isset($result->fields['pdf']))
		{
			$hit->pdf = $result->fields['pdf'];
		}
		if (isset($result->fields['url']))
		{
			$hit->url = $result->fields['url'];
		}
		if (isset($result->fields['cinii']))
		{
			$hit->cinii = $result->fields['cinii'];
		}
		
		
		// check
		if (isset($hit->title))
		{
			$v1 = finger_print($obj->title);
			$v2 = finger_print($hit->title);
			
			$hit->fingerprint_query = $v1;
			$hit->fingerprint_match = $v2;
			
			$d = levenshtein($v1, $v2);
			if ($d <= 5)
			{
				$hit->levenshtein = $d;
				$obj->results[] = $hit;
			}	
			
			
		}
				
		
		
		$result->MoveNext();
	}
	
	$obj->found = count($obj->results) > 0;
	
	return $obj;
}


//--------------------------------------------------------------------------------------------------
function find ($issn, $volume, $issue='', $page, $series='', $year = '', $article_number = '', $authors =  '')
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
	if ($issue != '')
	{
		$obj->issue = $issue;
	}
	if ($article_number != '')
	{
		$obj->article_number = $article_number;
	}
	if ($authors != '')
	{
		$obj->authors = $authors;
	}
	
	
	// ISNN probs
	if ($obj->issn == '2095-0845')
	{		
		$obj->issn = '2096-2703';
	}	
	
	//----------------------- build query --------
	
	// range query

	$sql = 'SELECT * FROM publications WHERE issn="' . $obj->issn . '"';
	
	//$sql = 'SELECT * FROM publications WHERE isbn13="' . $obj->issn . '"';
	
	//$sql = 'SELECT * FROM publications_tmp WHERE issn="' . $obj->issn . '" and doi is not null';
	// $sql = 'SELECT * FROM `acta botanica yunnanica-tmp-wd` WHERE issn="' . $obj->issn . '"';
	
	//$sql = 'SELECT * FROM `botanische jahrbücher-zobodat-wd` WHERE issn="' . $obj->issn . '"';

	 
	if (isset($obj->volume))
	{	
		$sql .= ' AND volume="' . $obj->volume . '"';
	}
	if (isset($obj->issue))
	{
		$sql .= ' AND issue="' . $obj->issue . '"';
	}

	if (isset($obj->article_number))
	{
		$sql .= ' AND article_number="' . $article_number . '"';
	}
	
	if ($obj->page != '')
	{	 
		$sql .= ' AND ((' . $page . ' between spage and epage) OR (spage="' . $page . '"))';
	}
	
	if (isset($obj->year))
	{
		$sql .= ' AND year=' . $year;
	}
	
	if (isset($obj->series))
	{
		$sql .= ' AND series=' . $series;
	}
	
	if (isset($obj->authors) && ($obj->issn != '0011-9970'))
	{
		$sql .= ' AND authors LIKE "%' .$obj->authors . '%"';
	}
	
	// hack for multiple records
	//$sql .= ' AND url LIKE "http://www.repository.naturalis.nl/%"';
	//$sql .= ' AND doi IS NOT NULL';
	//$sql .= ' AND guid LIKE "https://biostor.org/%"';
	
	if ($obj->issn == '0041-1752')
	{
		$sql .= ' AND guid LIKE "10520%"';
	}
	
	if ($obj->issn == '0029-8948')
	{
		$sql .= ' AND guid LIKE "10.%"';
	}
	
	if ($obj->issn == '0011-9970')
	{
		// Author names may be in the title not in separate field
		if (isset($obj->authors))
		{
			$sql .= ' AND (authors LIKE "%' .$obj->authors . '%" OR title LIKE "%' .$obj->authors . '%")';
		}
	}	
	
	
	// only have a lower bound
	if (0)
	{
		$sql = 'SELECT * FROM publications_eperiodica WHERE issn="' . $obj->issn . '"';
	 
		if (isset($obj->volume))
		{
			$sql .= ' AND volume="' . $volume . '"';
		}
		
		if (isset($obj->issue))
		{
			$sql .= ' AND issue="' . $issue . '"';
		}

		if ($obj->page != '')
		{	 
			$sql .= ' AND CAST(spage as SIGNED) <= ' . $page;
		}
	
		if (isset($obj->year))
		{
			$sql .= ' AND year=' . $year;
		}
	
	
		if (isset($obj->authors))
		{
			$authors = $obj->authors;
			
			if ($authors == 'C.DC.')
			{
				$parts = array('Candolle');
			}
			else
			{			
				$authors = str_replace('St-', 'Saint-', $authors);
				
				$authors = str_replace(', ', '|', $authors);				
				$authors = str_replace('.-', '|', $authors);
				$authors = str_replace(' & ', '|', $authors);
				
				$authors = str_replace('.', '', $authors);
				
			
				$parts = explode('|', $authors);
			}
		
			$sql .= ' AND authors LIKE "%' . join('%', $parts) . '%"';
		}
		
		$sql .= ' AND title like "N%"';
	
		$sql .= ' ORDER BY CAST(spage as SIGNED) DESC LIMIT 1';
	}

	
	//-----------------
	// lower bound query
	if (0)
	{
		$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';
	 
		if (isset($obj->volume))
		{
			$sql .= ' AND volume="' . $volume . '"';
		}
		if (isset($obj->issue))
		{
			$sql .= ' AND issue="' . $issue . '"';
		}

		if (isset($obj->article_number))
		{
			$sql .= ' AND article_number="' . $article_number . '"';
		}
	 
		$sql .= ' AND (' . $page . ' >= spage)';
	
		$sql .= ' ORDER BY CAST(spage AS SIGNED) DESC LIMIT 1';
	}	
	
	$obj->sql = $sql;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$obj->results = array();
	
	//echo '<pre>';
	//print_r($result);
	//echo '</pre>';
	
	while (!$result->EOF) 
	{
		$hit = new stdclass;
		
		if (isset($result->fields['wikidata']))
		{
			$hit->wikidata = $result->fields['wikidata'];
		}		
	
		if (isset($result->fields['doi']))
		{
			$hit->doi = $result->fields['doi'];
		}
		if (isset($result->fields['handle']))
		{
			$hit->handle = $result->fields['handle'];
		}		
		if (isset($result->fields['jstor']))
		{
			$hit->jstor = $result->fields['jstor'];
		}
		if (isset($result->fields['pdf']))
		{
			$hit->pdf = $result->fields['pdf'];
		}
		
		if (isset($result->fields['cinii']))
		{
			$hit->cinii = $result->fields['cinii'];
		}
		
		
		if ($obj->issn == '1673-5102')
		{
			if (isset($result->fields['guid']))
			{
				$hit->url = $result->fields['guid'];
			}
		
		}
		else
		{
			if (isset($result->fields['url']))
			{
				$hit->url = $result->fields['url'];
			}
		
			if (isset($result->fields['guid']))
			{
				$hit->guid = $result->fields['guid'];
			}
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
	
	$handled = false;
	
	//if (isset($_GET['title'])) { echo $_GET['title']; }
	
	if (!$handled)
	{
		if (isset($_GET['title']) && ($_GET['title'] != ''))
		{
		
			$filters = null;
						
			if (isset($_GET['issn']) || isset($_GET['year']))
			{
				$filters = new stdclass;				
			
				if (isset($_GET['issn']))
				{
					$filters->issn = $_GET['issn'];
				}
				if (isset($_GET['year']))
				{
					$filters->year = $_GET['year'];
				}
			}
		
			$obj = find_from_title($_GET['title'], $filters);
			

			header("Content-Type:text/plain");
			echo json_format(json_encode($obj));
		
			$handled = true;
	
		}
	}
		
	
	if (!$handled)
	{
		if (isset($_GET['issn']) 
			&& (isset($_GET['volume']) || isset($_GET['year']) || isset($_GET['issue']))
			&& (isset($_GET['page']) || isset($_GET['article_number']) || isset($_GET['authors']))
			)
		{
			$issn = '';
			$volume = '';
			$issue = '';
			$page = '';
			$year = '';
			$series = '';
			$title = '';
			$article_number = '';
			$authors = '';

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
			if (isset($_GET['issue']))
			{
				$issue = $_GET['issue'];
			}
			if (isset($_GET['article_number']))
			{
				$article_number = $_GET['article_number'];
			}
			if (isset($_GET['authors']))
			{
				$authors = $_GET['authors'];
			}
					
		
			$obj = find($issn, $volume, $issue, $page, $series, $year, $article_number, $authors);

			header("Content-Type:text/plain");
			echo json_format(json_encode($obj));
		
			$handled = true;
		}
	}	
	
	
	if (!$handled)
	{
		display_form();
	}
}
	
main();


?>
