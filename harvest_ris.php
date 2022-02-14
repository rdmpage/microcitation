<?php

// import from RIS

require_once(dirname(__FILE__) . '/ris.php');
require_once(dirname(__FILE__) . '/reference.php');


//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
	// print_r($reference);
	
	if (isset($reference->journal) && isset($reference->pages))
	{
		$reference->journal->pages .= $reference->pages; 
		unset($reference->pages);
	}
	
	$reference->title = html_entity_decode($reference->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	
	
	
	// post processing
	if (preg_match('/s(?<series>\d+)-(?<volume>\d+)/', $reference->journal->volume, $m))
	{
		$reference->journal->series = $m['series'];
		$reference->journal->volume = $m['volume'];
	}
	
	$guid = '';
	
	$pdf = '';
	
	
	
	$keys = array();
	$values= array();
	
	$keys[] = 'title';
	$values[] = '"' . addcslashes(strip_tags($reference->title), '"') . '"';

	// journal
		
	$journal = $reference->journal->name;
	if (!isset($reference->journal->series ))
	{
		if (preg_match('/^(?<journal>.*),\s+[S|s]eries\s+(?<series>\d+)$/', $journal, $m))
		{
			$journal = $m['journal'];
			$reference->journal->series = $m['series'];
		}
	}
	
	// handle some messy journal names
	if ($journal == 'Berichte Der Schweizerischen Botanischen Gesellschaft = Bulletin de la Société Botanique Suisse')
	{
		$journal = 'Berichte Der Schweizerischen Botanischen Gesellschaft';
	}
		
	$keys[] = 'journal';
	$values[] = '"' . addcslashes($journal, '"') . '"';
	
	if (isset($reference->journal->series))
	{
		$keys[] = 'series';
		$values[] = '"' . addcslashes($reference->journal->series, '"') . '"';	
	}
	
	if (isset($reference->journal->volume))
	{
		$keys[] = 'volume';
		$values[] = '"' . addcslashes($reference->journal->volume, '"') . '"';
	}
	
	if (isset($reference->journal->issue))
	{
		$keys[] = 'issue';
		$values[] = '"' . addcslashes($reference->journal->issue, '"') . '"';	
	}	
	
	if (isset($reference->journal->pages))
	{
		if (preg_match('/(?<spage>(\d+|\w+))--(?<epage>(\d+|\w+))/', $reference->journal->pages, $m))
		{
			$keys[] = 'spage';
			$values[] = '"' . addcslashes($m['spage'], '"') . '"';
			$keys[] = 'epage';
			$values[] = '"' . addcslashes($m['epage'], '"') . '"';	
		}
		else
		{
			$keys[] = 'spage';
			$values[] = '"' . addcslashes($reference->journal->pages, '"') . '"';
		}
	}
	
	if (isset($reference->journal->identifier))
	{
		$issn = '';
		foreach ($reference->journal->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'issn':
					if ($issn == '')
					{
						$keys[] = 'issn';
						$values[] = '"' . $identifier->id . '"';
						
						$issn = $identifier->id;
					}
					else
					{
						$keys[] = 'eissn';
						$values[] = '"' . $identifier->id . '"';
					}					
					break;
								
				default:
					break;
			}
		}
	}

	$keys[] = 'year';
	$values[] = '"' . addcslashes($reference->year, '"') . '"';
	
	if (isset($reference->date))
	{
		$keys[] = 'date';
		$values[] = '"' . $reference->date . '"';
	}
	
	if (isset($reference->link))
	{
		foreach ($reference->link as $link)
		{
			if ($link->anchor == 'LINK')
			{
				$guid = $link->url;
				
				
				$add = true;
				
				
				// www.documentation.ird.fr
				if (preg_match('/www.documentation.ird.fr/', $link->url))
				{
					$guid = $link->url;
				}
				
			
				if (preg_match('/http:\/\/dx.doi.org\//', $link->url))
				{
					// ignore DOIs
					$add = false;
				}
				
				if (preg_match('/https?:\/\/hdl.handle.net\//', $link->url))
				{
					// ignore handles
					$add = false;
				}

				if (preg_match('/https?:\/\/www.jstor.org\//', $link->url))
				{
					// ignore jstor
					$add = false;
					$add = true;
				}
				
				if (preg_match('/http:\/\/direct.biostor.org\//', $link->url))
				{
					$add = false;
				}
				
				
				
				if (preg_match('/https?:\/\/www.cnki.com.cn\/Article\/CJFDTOTAL-(?<id>.*)\.htm/', $link->url, $m))
				{
						$keys[] = 'cnki';
						$values[] = '"' . $m['id']. '"';

				}
				
				/*
				if (preg_match('/handle\/(?<id>.*)/', $link->url, $m))
				{
						$keys[] = 'handle';
						$values[] = '"' . $m['id']. '"';					
				}
				*/
				
				if ($add)
				{		
					if (1)	
					{
					$keys[] = 'url';
					$values[] = '"' . $link->url . '"';
					}
					
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
				
				if ($guid == '')
				{
					$guid = $link->url;
				}
			
				if (preg_match('/wenjianming=(?<pdf>.*)&/Uu', $pdf, $m))
				{
					$pdf = 'http://www.plantsystematics.com/qikan/manage/wenzhang/' . $m['pdf'] . '.pdf';
				}
			
				$values[] = '"' . $pdf . '"';
			}
			
			if ($link->anchor == 'XML')
			{
				$keys[] = 'xml';
				$values[] = '"' . $link->url . '"';
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
					$use_doi = true;
				
					if ($guid != '')
					{
						if (preg_match('/www.documentation.ird.fr/', $guid))
						{
							$use_doi = false;
						}
					}
				
					if ($use_doi)
					{
						$guid = $identifier->id;
					}
				
					$keys[] = 'doi';
					$values[] = '"' . $identifier->id . '"';
					break;
				
				case 'handle':
					//if ($guid == '')
					{				
						$guid = $identifier->id;
					}
				
					$keys[] = 'handle';
					$values[] = '"' . $identifier->id . '"';
					break;
					
				case 'isbn':
					if (strlen($identifier->id) == 13)
					{
						$keys[] = 'isbn13';
					}
					else
					{
						$keys[] = 'isbn10';
					}
					$values[] = '"' . $identifier->id . '"';
					break;
					

				case 'jstor':
					$keys[] = 'jstor';
					$values[] = '"' . $identifier->id . '"';
					break;
					
				case 'biostor':
					$keys[] = 'biostor';
					$values[] = '"' . $identifier->id . '"';
					$guid = 'https://biostor.org/reference/' . $identifier->id;
					break;
					

				case 'wos':
					if ($guid == '')
					{
						$guid = $identifier->id;
					}
					$keys[] = 'wos';
					$values[] = '"' . $identifier->id . '"';
					break;
				
				default:
					break;
			}
		}
	}	
	
	
	
	//print_r($reference);
		
	$authors =  array();
	if (isset($reference->author))
	{
		foreach ($reference->author as $author)
		{
			if (isset($author->lastname))
			{
				$authors[] = addcslashes($author->lastname . ', ' . $author->firstname, '"');
			}
			else
			{
				$authors[] = addcslashes($author->name, '"');
			}
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
	
	/*
	if (isset($reference->keyword))
	{
		$keys[] = 'keywords';
		$values[] = '"' . join(';', $reference->keyword) . '"';
	}
	*/
	
	
	// SICI
	
	$get_sici = true;
	$get_sici = false;
	
	if (isset($issn))
	{
		switch ($issn)
		{
			// Russian
			case '0367-1445':
			case '0044-5134':
			case '0726-9609':
			case '0080-4274':
				$get_sici = false;
				break;
				
			default:
				break;
		}
	
	}
	
	if ($get_sici)
	{
		$sici = reference_to_sici($reference);
		if ($sici != '')
		{
			$keys[] = 'sici';
			$values[] = '"' . addcslashes($sici, '"') . '"';
		
			if ($guid == '')	
			{
				$guid = $sici;
			}		
		}	
	}
	
		
	if (isset($reference->publisher_id))
	{
		if (preg_match('/oai:/', $reference->publisher_id))
		{
			$keys[] = 'oai';
			$values[] = '"' . addcslashes($reference->publisher_id, '"') . '"';	
		}
		
		if (preg_match('/urn:ISBN:/', $reference->publisher_id))
		{
			$isbn = str_replace('urn:ISBN:', '', $reference->publisher_id);
			
			if (strlen($isbn) == 10)
			{
				$keys[] = 'isbn10';
				$values[] = '"' . addcslashes($isbn, '"') . '"';
			}
			if (strlen($isbn) == 13)
			{
				$keys[] = 'isbn13';
				$values[] = '"' . addcslashes($isbn, '"') . '"';
			}
				
		}
		
		
		if ($guid == '')
		{
			$guid = $reference->publisher_id;
		}
	}
	
	
	if ($guid == '')
	{	
		$guid = md5(join('', $values));
	}
	
	
	if ($issn == '0368-0177')
	{
		$guid = $issn . '-' . basename($guid);
	}

	// Geodiversitas has obscenely long URLs
	if (($issn == '1280-9659') && (strlen($guid) >= 255))
	{
		$guid = md5(join('', $values));
	}
	
	// Zoosystema has obscenely long URLs	
	if (($issn == '1280-9551') && (strlen($guid) >= 255))
	{
		$guid = md5(join('', $values));
	}

	// Adansonia has obscenely long URLs	
	if (($issn == '1280-8571') && (strlen($guid) >= 255))
	{
		$guid = md5(join('', $values));
	}
	
	if (strlen($guid) >= 255)
	{
		$guid = md5(join('', $values));
	}
	
	
	// Force guid (e.g., journal ha sno guid, or same PDF for multiple papers such as short notes)
	if (0)
	{
		$guid = md5(join('', $values));
	}
	
	$keys[] = 'guid';
	$values[] = '"' . $guid . '"';
	
	//echo $reference->journal->volume . "\n";
	
	// populate from scratch (default)
	if (1)
	{
		$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
			. join(',', $values) . ');';
		echo $sql . "\n";
	}
	if (0)
	{
		$sql = 'REPLACE INTO publications_biostor(' . join(',', $keys) . ') values('
			. join(',', $values) . ');';
		echo $sql . "\n";
	}

	
	// Only add articles from a given journal
	// 
	if (0)
	{
		$issn = '0366-3094'; // Berichte der Schweizerischen Botanischen Gesellschaft
		$issn = '0253-1453'; // Botanica Helvetica
		
		$add = false;
		if (isset($reference->journal) && isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						if ($identifier->id == $issn)
						{
							$add = true;
						}
						break;
						
					default:
						break;
				}
			}
		}
		
		if ($add)
		{
			$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
				. join(',', $values) . ');';
			echo $sql . "\n";		
		}
	}
		
	// Import prior to a given date
	if (0) 
	{
		// && in_array($reference->year, array(2009,2010,2011, 2012)))
//		if (isset($reference->year)  && in_array($reference->year, array(2009,2010,2011, 2012)))
		
//		if (isset($reference->year)  && in_array($reference->year, array(2005)))
		
		if (isset($reference->journal->volume)  && ($reference->journal->volume < 48))
		{
			$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
				. join(',', $values) . ');';
			echo $sql . "\n";
		}
	}
	
	
	// Import JSTOR if it has a DOI
	if (0) 
	{
		if (isset($reference->year)  && ($reference->year == 2007))
		{
			if (preg_match('/^10\./', $guid))
			{
				$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
					. join(',', $values) . ');';
				echo $sql . "\n";
			}
		}
	}
	
	// Add data to existing record
	
	
	if (0) 
	{
		/*
		if (isset($reference->journal->pages))
		{
			echo 'UPDATE publications_eperiodica SET spage="' . $reference->journal->pages . '" WHERE guid="' . $guid . '";' . "\n";
		}
		*/
		if (isset($reference->journal->volume))
		{
			echo 'UPDATE publications_eperiodica SET volume="' . $reference->journal->volume . '" WHERE guid="' . $guid . '";' . "\n";
		}

	}		
	
	
	if (0) 	
	{
		
		if (0)
		{
		
			$qualifiers = array();
			
			$count = 0;
			foreach ($keys as $k)
			{
				switch ($k)
				{
					case 'issn':
						$qualifiers[] = 'issn=' . $values[$count];
						break;
					case 'volume':
						$qualifiers[] = 'volume=' . $values[$count];
						break;
					case 'spage':
						$qualifiers[] = 'spage=' . $values[$count];
						break;
						
					default:
						break;
				}
				$count++;
			}
			
			//print_r($qualifiers);
			//print_r($reference);
			
			if (count($qualifiers) == 3)
			{
			
				if (isset($reference->identifier))
				{
					foreach ($reference->identifier as $identifier)
					{
						switch($identifier->type)
						{
							case 'handle':
								$sql = 'UPDATE publications SET handle="' . $identifier->id . '"'
								. ' WHERE ' . join(" AND ", $qualifiers) . ' AND handle IS NULL;';
								
								echo $sql . "\n";
								break;
						
							default:
								break;
						}
					}
				}
			
				
				if (isset($reference->link))
				{
					foreach ($reference->link as $link)
					{
						if ($link->anchor == 'LINK')
						{
							
							$sql = 'UPDATE publications SET url="' . $link->url . '"'
								. ' WHERE ' . join(" AND ", $qualifiers) . ';'; // ' AND doi IS NOT NULL;';

							echo $sql . "\n";
						}
						if ($link->anchor == 'PDF')
						{
							$keys[] = 'pdf';
			
							$pdf = $link->url ;

							$sql = 'UPDATE publications SET pdf="' . $link->url . '"'
								. ' WHERE ' . join(" AND ", $qualifiers) . ';'; // ' AND doi IS NOT NULL;';

							echo $sql . "\n";
						}
					}
				}
				
			
			
			}
		}
		
	}
	
	
	// Add JSTOR to existing record
	if (0) 
	{
		if (isset($reference->journal->volume)  && ($reference->journal->volume >= 48))
		//if (1)
		{
		
			$epage = '';
		
			$qualifiers = array();
			
			$count = 0;
			foreach ($keys as $k)
			{
				switch ($k)
				{
					case 'issn':
						$qualifiers[] = 'issn=' . $values[$count];
						break;
					case 'volume':
						$qualifiers[] = 'volume=' . $values[$count];
						break;
					case 'spage':
						$qualifiers[] = 'spage=' . $values[$count];
						break;
						
					/*
					case 'jstor':
						$qualifiers[] = 'doi=' . '"10.2307/' . str_replace('"', '', $values[$count]) . '"';
						break;
					*/

					/*
					case 'epage':
						$epage = $values[$count];
						break;
					*/
				
						
					default:
						break;
				}
				$count++;
			}
			
			//print_r($qualifiers);
			
			//print_r($reference);
			
			if (count($qualifiers) == 3)
			{
				$jstor = $guid;
				$jstor = str_replace('http://www.jstor.org/stable/', '', $jstor);
				$jstor = str_replace('10.2307/', '', $jstor);
				
				$sql = 'UPDATE publications SET jstor="' . $jstor
					. '" WHERE ' . join(" AND ", $qualifiers) . ';';
					
				echo $sql . "\n";
			}
			
			
			if (count($qualifiers) == 4)
			{
/*				$sql = 'UPDATE publications SET jstor=' . str_replace('http://www.jstor.org/stable/', '', $guid)
					. ' WHERE ' . join(" AND ", $qualifiers) . ';';
					
				if ($epage != '')
				{
					$sql .= "\n" . 'UPDATE publications SET epage=' . $epage 
						. ' WHERE ' . join(" AND ", $qualifiers) . ';';
				}
			*/					
					
				$sql = 'UPDATE publications SET jstor="' .str_replace('http://www.jstor.org/stable/', '', $guid)
					. '" WHERE ' . join(" AND ", $qualifiers) . ';';

				
				if ($epage != '')
				{
					$sql .= "\n" . 'UPDATE publications SET epage=' . $epage 
						. ' WHERE ' . join(" AND ", $qualifiers) . ';';
				}
				


				echo $sql . "\n";
			}
		}
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
	
	
	// Add PDF to existing record
	if (0) 
	{
		if ($pdf != '')
		{
			$sql = 'UPDATE publications SET pdf="' . $pdf . '" WHERE guid="' . $guid . '" AND pdf IS NULL;';
			echo $sql . "\n";
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