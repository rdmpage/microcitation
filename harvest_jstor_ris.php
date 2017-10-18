<?php

// import JSTOR from RIS and either add, or update records with existing DOI

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/ris.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

//--------------------------------------------------------------------------------------------------
function get_value_from_key($keys, $values, $k)
{
	$v = '';
	$count = 0;
	while ($count < count($keys))
	{
		if ($keys[$count] == $k)
		{
			break;
		}
		$count++;
	}
	if ($count < count($keys))
	{
		$v = $values[$count];
	}
	return $v;
}

//--------------------------------------------------------------------------------------------------
function have_guid($guid)
{
	global $db;
	
	$have = false;

	$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '" LIMIT 1;';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$have = true;
	}
	return $have;
}

//--------------------------------------------------------------------------------------------------
function have_jstor($jstor)
{
	global $db;
	
	$have = false;

	$sql = 'SELECT * FROM publications WHERE jstor="' . $jstor . '" LIMIT 1;';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$have = true;
	}
	return $have;
}

//--------------------------------------------------------------------------------------------------
function have_reference($keys, $values, $update_keys = array())
{
	global $db;
	
	$update_sql = '';
	
	$sql = '';
	
	$q = array();
	$count = 0;
	foreach ($keys as $k)
	{
		switch ($k)
		{
			case 'issn':
			case 'volume':
			case 'spage':
				$q[] = $k . '=' . $values[$count];
				break;
				
			default:
				break;
		}
		$count++;
	}
	
	//print_r($q);
	
	if (count($q) == 3)
	{
		$sql = 'SELECT * FROM publications WHERE ' . join(" AND ", $q) . ' LIMIT 1';
		
		echo "-- $sql\n";
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

		if ($result->NumRows() == 1)
		{
			$update_sql = 'UPDATE publications SET ';
			
			$u = array();
			foreach ($update_keys as $uk => $uv)
			{
				$u[] = $uk . '="' . addcslashes($uv, '"') . '"';
			}
			$update_sql .= join(",", $u);
			$update_sql .= ' WHERE ' . join(" AND ", $q) . ';';
		}
	}
	return $update_sql;
}



//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
	global $db;
	
	//print_r($reference);
	
	$guid = '';
	$doi = '';
	$jstor = 0;
	
	$keys = array();
	$values= array();
	
	$keys[] = 'title';
	$values[] = '"' . addcslashes(strip_tags($reference->title), '"') . '"';

	// journal
	
	$journal = $reference->journal->name;
	$series = '';
	if (preg_match('/^(?<journal>.*),\s+[S|s]eries\s+(?<series>\d+)$/', $journal, $m))
	{
		$journal = $m['journal'];
		$series = $m['series'];
	}
	
	$keys[] = 'journal';
	$values[] = '"' . addcslashes($journal, '"') . '"';
	
	if ($series != '')
	{
		$keys[] = 'series';
		$values[] = '"' . addcslashes($series, '"') . '"';	
	}

	$keys[] = 'volume';
	$values[] = '"' . addcslashes($reference->journal->volume, '"') . '"';
	
	if (isset($reference->journal->issue))
	{
		$keys[] = 'issue';
		$values[] = '"' . addcslashes($reference->journal->issue, '"') . '"';	
	}
	
	if (preg_match('/(?<spage>\d+)--(?<epage>\d+)/', $reference->journal->pages, $m))
	{
		$keys[] = 'spage';
		$values[] = '"' . addcslashes($m['spage'], '"') . '"';
		$keys[] = 'epage';
		$values[] = '"' . addcslashes($m['epage'], '"') . '"';	
	}
	
	if (isset($reference->journal->identifier))
	{
		foreach ($reference->journal->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'issn':
					$keys[] = 'issn';
					$values[] = '"' . $identifier->id . '"';
					break;
								
				default:
					break;
			}
		}
	}

	$keys[] = 'year';
	$values[] = '"' . addcslashes($reference->year, '"') . '"';
	
	foreach ($reference->link as $link)
	{
		if ($link->anchor == 'LINK')
		{
			$guid = $link->url;
			
			if (preg_match('/http:\/\/dx.doi.org\//', $link->url))
			{
				// ignore DOIs
			}
			else
			{			
				$keys[] = 'url';
				$values[] = '"' . $link->url . '"';
			
				if (1)
				{
					if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>\d+)$/', $link->url, $m))
					{
						if (0)
						{
							$guid = $link->url;
						}
						else
						{
							$guid = '10.2307/' . $m['id'];
						}
						
						$jstor = $m['id'];
					}
				}
			}			
		}
		if ($link->anchor == 'PDF')
		{
			$keys[] = 'pdf';
			
			$pdf = $link->url ;
			
			if (preg_match('/wenjianming=(?<pdf>.*)&/Uu', $pdf, $m))
			{
				$pdf = 'http://www.plantsystematics.com/qikan/manage/wenzhang/' . $m['pdf'] . '.pdf';
			}
			
			$values[] = '"' . $pdf . '"';
		}
	}
	
	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'doi':
					$guid = $identifier->id;
				
					$keys[] = 'doi';
					$values[] = '"' . $identifier->id . '"';
					break;
				
				case 'handle':
					$keys[] = 'handle';
					$values[] = '"' . $identifier->id . '"';
					break;

				case 'jstor':
					$keys[] = 'jstor';
					$values[] = '"' . $identifier->id . '"';
					
					$jstor = $identifier->id;
					break;
				
				default:
					break;
			}
		}
	}	
	
	//print_r($reference);exit();
	$authors =  array();
	if (isset($reference->author))
	{
		foreach ($reference->author as $author)
		{
			$authors[] = $author->lastname . ', ' . $author->firstname;
		}
		if (count($authors) > 0)
		{
			$keys[] = 'authors';
			$values[] = '"' . join(';', $authors) . '"';
		}
	}
	
	if (isset($reference->abstract))
	{
		$keys[] = 'abstract';
		$values[] = '"' . addcslashes($reference->abstract, '"') . '"';	
	}
	
	
	if ($guid == '')
	{	
		$guid = md5(join('', $values));
	}
	$keys[] = 'guid';
	$values[] = '"' . $guid . '"';
	
	
	// Exists?
	if (have_guid($guid))
	{
		// have already
		$sql = "-- have $guid already, skip...\n";	
		echo $sql;	
		
		$update_sql = 'UPDATE publications SET epage=' . get_value_from_key($keys, $values, 'epage') . ' WHERE guid="' . $guid . '";';
		echo $update_sql . "\n";
		
	}
	/*
	else
	{
		if ($jstor != 0)
		{
			if (have_jstor($jstor))
			{
				// have already this JSTOR id
				$sql = "-- have JSTOR $jstor already, skip...\n";
				echo $sql;
			}
			else
			{
				// don't have this JSTOR record, either we don't have reference, 
				// or reference has external DOI
				$update_sql = have_reference($keys, $values, array('jstor' => $jstor));
				if ($update_sql != '')
				{
					// add JSTOR id to record
					echo $update_sql . "\n";
				}
				else
				{
					// add reference
					$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
						. join(',', $values) . ');';
					echo $sql . "\n";
				}
			}
		}
		
	}
	*/

	
}




//--------------------------------------------------------------------------------------------------
$filename = '';
if ($argc < 2)
{
	echo "Usage: import_ris.php <RIS file> \n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'ris_import');


?>