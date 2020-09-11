<?php

// import from BioStor RIS and add to biostor table

require_once(dirname(__FILE__) . '/ris.php');

$use_publications = true;


//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
	
	global $use_publications;
	
	//print_r($reference);
	
	$guid = '';
	
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
			if (preg_match('/http:\/\/direct.biostor.org\/reference\/(?<id>\d+)$/', $link->url, $m))
			{
				if ($use_publications)
				{
					$guid = 'https://biostor.org/reference/' . $m['id'];
					
					$keys[] = 'biostor';
					$values[] = '"' . $m['id'] . '"';
					
				}
				else
				{
					$guid = $m['id'];
				}
			}	
			
			// http://www.biodiversitylibrary.org/page/		
			if (preg_match('/http:\/\/www.biodiversitylibrary.org\/page\/(?<id>\d+)$/', $link->url, $m))
			{
				if ($use_publications)
				{			
					$keys[] = 'pageid';
					$values[] = '"' . $m['id'] . '"';
				}
				else
				{
					$keys[] = 'bhl_pageid';
					$values[] = '"' . $m['id'] . '"';				
				}
			}	
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
	
	$keys[] = 'guid';
	$values[] = '"' . $guid . '"';
	
	if ($use_publications)
	{
		$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
			. join(',', $values) . ');';
	
	}
	else
	{
		$sql = 'REPLACE INTO biostor(' . join(',', $keys) . ') values('
			. join(',', $values) . ');';
	}
	
	echo $sql . "\n";

	
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