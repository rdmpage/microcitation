<?php

// Export reference(s) in RIS format

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');


$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';

if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
}

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

//--------------------------------------------------------------------------------------------------
$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>\d+)/', $guid, $m))
{
	$sql .= ' OR jstor=' . $m['id'];
}
$sql .= ' LIMIT 1;';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
	$reference = new stdclass;
	
	$reference->type == 'article';
	$reference->title = utf8_encode($result->fields['title']);
	
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
	
	// Date to do: handle dates not just year
	if ($result->fields['year'] != '')
	{
		$reference->year = $result->fields['year'];
	}	
	
	
	// authors
	if ($result->fields['authors'] != '')
	{
		$authors = explode(";", utf8_encode($result->fields['authors']));
		
		foreach ($authors as $a)
		{
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
	}
	if ($result->fields['jstor'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'jstor';
		$identifier->id = $result->fields['jstor'];
		$reference->identifier[] = $identifier;
	}
	if ($result->fields['url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = $result->fields['url'];
		$reference->link[] = $link;
	}

	/*
	echo '<pre>';
	print_r($reference);
	echo '</pre>';
	
	echo '<pre>';
	$c = reference_to_citeprocjs($reference);
	print_r($c);
	echo '</pre>';*/
	$c = reference_to_citeprocjs($reference);
	echo json_encode($c);
}

?>