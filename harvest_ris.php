<?php

// import from RIS

require_once(dirname(__FILE__) . '/ris.php');


//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
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
			$guid = $link->url;
			
			if (preg_match('/http:\/\/dx.doi.org\//', $link->url))
			{
				// ignore DOIs
			}
			else
			{			
				$keys[] = 'url';
				$values[] = '"' . $link->url . '"';
				
				if (0)
				{			
					if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>\d+)$/', $link->url, $m))
					{
						$guid = '10.2307/' . $m['id'];
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
	
	//echo $reference->journal->volume . "\n";
	
	// populate from scratch
	if (1) // in_array($reference->journal->volume, array(26,27))) 
	{
		$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
			. join(',', $values) . ');';
		echo $sql . "\n";
	}

	if (0)
	{
		// JSTOR-derived data enhance
		$count = 0;
		foreach ($keys as $k)
		{
			if ($k == 'epage')
			{
				$sql = 'UPDATE `publications` SET epage=' . $values[$count] . ' WHERE `guid`="' . $guid . '";';	
				echo $sql . "\n";		
			}
			$count++;
		}
	}
	
	

	
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