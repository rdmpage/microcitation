<?php

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');

require_once(dirname(__FILE__) . '/CiteProc.php');


$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';
$guid = '10.3724/SP.J.1141.2011.02204';

//$guid = '';

if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
}

$callback = '';
if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

//--------------------------------------------------------------------------------------------------

$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

// If GUID is not a DOI then look for JSTOR or URL
if (!preg_match('/^10\./', $guid))
{	
	if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>\d+)/', $guid, $m))
	{
		$sql .= ' OR jstor=' . $m['id'];
	}
	else
	{
		if (preg_match('/^http:\/\//', $guid))
		{
			$sql .= ' OR url="' . $guid . '"';
		}
	}
}


$sql .= ' LIMIT 1;';

//echo $sql;
//file_put_contents(dirname(__FILE__) . '/tmp/pub.log', $sql);

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$primary_identifer = '';

if ($result->NumRows() == 1)
{

	//print_r($result);

	$reference = new stdclass;
	
	$reference->type == 'article';
	$reference->title = $result->fields['title'];
	
	// clean
	$reference->title = strip_tags($reference->title);
	
	$reference->journal = new stdclass;
	$reference->journal->name = $result->fields['journal'];
	
	if ($result->fields['volume'] != '')
	{
		$reference->journal->volume = $result->fields['volume'];
	}
	if ($result->fields['issue'] != '')
	{
		$reference->journal->issue = $result->fields['issue'];
	}
	if ($result->fields['spage'] != '')
	{
		$reference->journal->pages = $result->fields['spage'];
	}
	if ($result->fields['epage'] != '')
	{
		$reference->journal->pages .= '--' . $result->fields['epage'];
	}
	
	if ($result->fields['issn'] != '')
	{
		if (!isset($reference->journal->identifier))
		{
			$reference->journal->identifier = array();
		}
		$identifier = new stdclass;
		$identifier->type = 'issn';
		$identifier->id = $result->fields['issn'];
		$reference->journal->identifier[] = $identifier;
	}
	if ($result->fields['eissn'] != '')
	{
		if (!isset($reference->journal->identifier))
		{
			$reference->journal->identifier = array();
		}
		$identifier = new stdclass;
		$identifier->type = 'eissn';
		$identifier->id = $result->fields['issn'];
		$reference->journal->identifier[] = $identifier;
	}
	
	// Date to do: handle dates not just year
	if ($result->fields['year'] != '')
	{
		$reference->year = $result->fields['year'];
	}	
	
	// authors
	if ($result->fields['authors'] != '')
	{
		$authors = explode(";", $result->fields['authors']);
		
		foreach ($authors as $a)
		{
			$a = mb_convert_case($a, MB_CASE_TITLE, 'UTF-8');
		
			$author = new stdclass;
			
			$parts = explode(",", $a);
			if (count($parts) == 2)
			{
				$author->lastname = trim($parts[0]);
				$author->firstname = trim($parts[1]);
			}
			else
			{
				$parts = explode(" ", $a);
				$n = count($parts);
				if ($n > 1)
				{
					$author->lastname = array_pop($parts);
					$author->firstname = join(' ', $parts);
				}			
			}
			$author->name = $a;
			
			$reference->author[] = $author;
		}
	}	
	
	// identifiers and links
	
	if ($result->fields['doi'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'doi';
		$identifier->id = $result->fields['doi'];
		$reference->identifier[] = $identifier;
		
		$primary_identifer = 'DOI:' . $identifier->id;
	}
	if ($result->fields['jstor'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'jstor';
		$identifier->id = $result->fields['jstor'];
		$reference->identifier[] = $identifier;
		
		if ($primary_identifer == '')
		{
			$primary_identifer =  'http://www.jstor.org/stable/' . $identifier->id;
		}
	}
	if ($result->fields['handle'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'handle';
		$identifier->id = $result->fields['handle'];
		$reference->identifier[] = $identifier;
		
		if ($primary_identifer == '')
		{
			$primary_identifer =  'http://hdl.handle.net/' . $identifier->id;
		}
		
	}
		
	if ($result->fields['url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = $result->fields['url'];
		$reference->link[] = $link;
		
		if ($primary_identifer == '')
		{
			$primary_identifer =  $link->url;
		}
		
	}
	if ($result->fields['pdf'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'PDF';
		$link->url = $result->fields['pdf'];
		$reference->link[] = $link;
	}
	
	if (0)
	{
		echo '<pre>';
		print_r($reference);
		echo '</pre>';
	}
	
	if (0)
	{
		echo '<pre>';
		$c = reference_to_citeprocjs($reference);
		print_r($c);
		echo '</pre>';
	}
	
	$citeproc_obj = reference_to_citeprocjs($reference);
	//echo json_encode($citeproc_obj);
	
	$cj = json_encode($citeproc_obj);
	$c = json_decode($cj);


	$csl = file_get_contents(dirname(__FILE__) . '/style/apa.csl');

	$citeproc = new citeproc($csl);
	
	$data = new stdclass;
	$data->html = $citeproc->render($c, 'bibliography');
	
	if ($callback != '')
	{
		echo $callback . '(';
	}
	echo json_encode($data);
	if ($callback != '')
	{
		echo ')';
	}
	
	//echo $data->html;
	

	
}


?>

