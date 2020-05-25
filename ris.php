<?php

/**
 * @file ris.php
 *
 */

// Parse RIS file and try and find first page of article in BHL

require_once (dirname(__FILE__) . '/nameparse.php');

$debug = false;
//$debug = true;

$logfile;

$key_map = array(
	'ID' => 'publisher_id',
	'T1' => 'title',
	'TI' => 'title',
	'SN' => 'issn',
	'JO' => 'secondary_title',
	'JF' => 'secondary_title',
	'BT' => 'secondary_title', // To handle TROPICS fuckup
	'VL' => 'volume',
	'IS' => 'issue',
	'SP' => 'spage',
	'EP' => 'epage',
	
	'N2' => 'abstract',
	'AB' => 'abstract',
	
	'UR' => 'url',
	'AV' => 'availability',
	
	'PB' => 'publisher',
	'CY' => 'city',
	
	'Y1' => 'year',
	'KW' => 'keyword',
	'L1' => 'pdf', 
	'N1' => 'notes',
	'L2' => 'fulltext', // check this, we want to have a link to the PDF...
	'DO' => 'doi', // Mendeley 0.9.9.2
	
	'XM' => 'xml', // I made this key up!
	);
	
//--------------------------------------------------------------------------------------------------
function process_ris_key($key, $value, &$obj)
{
	global $debug;
	
	//echo "key=$key\n";
	
	switch ($key)
	{
		case 'PB':
			if (!isset($obj->publisher))
			{
				$obj->publisher = new stdclass;
			}
			$obj->publisher->name = $value;
			break;

		case 'CY':
			if (!isset($obj->publisher))
			{
				$obj->publisher = new stdclass;
			}
			$obj->publisher->address = $value;
			break;
	
		case 'AU':
		case 'A1':					
			// Interpret author names
			
			// Trim trailing periods and other junk
			//$value = preg_replace("/\.$/", "", $value);
			$value = preg_replace("/&nbsp;$/", "", $value);
			$value = preg_replace("/,([^\s])/", ", $1", $value);
			
			// Handle case where initials aren't spaced
			$value = preg_replace("/, ([A-Z])([A-Z])$/", ", $1 $2", $value);

			// Clean Ingenta crap						
			$value = preg_replace("/\[[0-9]\]/", "", $value);
			
			// Space initials nicely
			$value = preg_replace("/\.([A-Z])/", ". $1", $value);
			
			// Make nice
			$value = mb_convert_case($value, 
				MB_CASE_TITLE, mb_detect_encoding($value));
				
			if (1)
			{
							
				// Get parts of name
				$parts = parse_name($value);
				
				$author = new stdClass();
				
				if (isset($parts['last']))
				{
					$author->lastname = $parts['last'];
				}
				if (isset($parts['suffix']))
				{
					$author->suffix = $parts['suffix'];
				}
				if (isset($parts['first']))
				{
					$author->firstname = $parts['first'];
					
					if (array_key_exists('middle', $parts))
					{
						$author->firstname .= ' ' . $parts['middle'];
					}
				}
				$author->firstname = preg_replace('/\./Uu', '', $author->firstname);
				$author->name = $author->firstname . ' ' . $author->lastname;
			}
			else
			{
				$author = $value;
			}
			$obj->author[] = $author;
			break;	
	
		case 'JF':
		case 'JO':
			$value = mb_convert_case($value, 
				MB_CASE_TITLE, mb_detect_encoding($value));
				
			$value = preg_replace('/ Of /', ' of ', $value);	
			$value = preg_replace('/ the /', ' the ', $value);	
			$value = preg_replace('/ and /', ' and ', $value);	
			$value = preg_replace('/ De /', ' de ', $value);	
			$value = preg_replace('/ Du /', ' du ', $value);	
			$value = preg_replace('/ La /', ' la ', $value);	
			
			if (!isset($obj->journal))
			{
				$obj->journal = new stdclass;
			}
			$obj->journal->name = $value;
			break;
			
		case 'VL':
			if ($obj->type == 'thesis')
			{
				$obj->degree = $value;
			}
			else
			{
				if (!isset($obj->journal))
				{
					$obj->journal = new stdclass;
				}
				$obj->journal->volume = $value;
			}
			break;
			
		case 'T2':
			if ($obj->type == 'thesis')
			{
				$obj->department = $value;
			}
			if ($obj->type == 'article')
			{
				if (!isset($obj->journal))
				{
					$obj->journal = new stdclass;
				}

				$obj->journal->name = $value;
			}
			break;
			
		case 'PB':
			if ($obj->type == 'thesis')
			{
				$obj->university = $value;
			}
			break;
			

		case 'IS':
			if (!isset($obj->journal))
			{
				$obj->journal = new stdclass;
			}
			$obj->journal->issue = $value;
			break;
			
		case 'SN':
			$identifier = new stdclass;			
			$identifier->id = $value;
			if ($obj->type == 'book')
			{
				$identifier->type = 'isbn';
				$identifier->id = str_replace('-', '', $identifier->id);
				$obj->identifier[] = $identifier;	
			}
			else
			{
				$identifier->type = 'issn';
				
				if (!isset($obj->journal))
				{
					$obj->journal = new stdclass;
				}
				$obj->journal->identifier[] = $identifier;	
			}	
			break;

		case 'N2':
		case 'AB':
			$obj->abstract = $value;			
			break;
			
			
		case 'T1':
		case 'TI':
			$value = preg_replace('/([^\s])\(/', '$1 (', $value);	
			$value = str_replace("\ü", "ü", $value);
			$value = str_replace("\ö", "ö", $value);

			$value = str_replace("“", "\"", $value);
			$value = str_replace("”", "\"", $value);
						
			$obj->title = $value;
			break;
				
		// Handle cases where both pages SP and EP are in this field
		case 'SP':
			if (preg_match('/^(?<spage>[0-9]+)\s*[-|–|—]\s*(?<epage>[0-9]+)$/u', trim($value), $matches))
			{
				if (isset($obj->journal))
				{
					$obj->journal->pages = $matches['spage'] . '--' . $matches['epage'];
				}
				else
				{
					$obj->pages = $matches['spage'] . '--' . $matches['epage'];
				}				
			}
			else
			{
				if (isset($obj->journal))
				{
					$obj->journal->pages = $value;
				}
				else
				{
					$obj->pages = $value;
				}
			}				
			break;

		case 'EP':
			if (preg_match('/^(?<spage>[0-9]+)\s*[-|–|—]\s*(?<epage>[0-9]+)$/u', trim($value), $matches))
			{
				if (isset($obj->journal))
				{
					$obj->journal->pages = $matches['spage'] . '--' . $matches['epage'];
				}
				else
				{
					$obj->pages = $matches['spage'] . '--' . $matches['epage'];
				}				
			}
			else
			{
				if (isset($obj->journal->pages))
				{
					$obj->journal->pages .= '--' . $value;
				}
				else
				{
					$obj->pages .= '--' . $value;
				}				
			}				
			break;
			
		case 'PY': // used by Ingenta, and others
		case 'Y1':
		   $date = $value; 
		   
		   //echo $value . "\n";
		   
		   // PY  - 2002-02-01T00:00:00///
		   if (preg_match("/(?<year>[0-9]{4})-(?<month>[0-9]{1,2})-(?<day>[0-9]{1,2})/", $date, $matches))
		   {                       
			   $obj->year = $matches['year'];
			   $obj->date = sprintf("%d-%02d-%02d", $matches['year'], $matches['month'], $matches['day']);			   
		   }

		   
		   if (preg_match("/(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})\/(?<day>[0-9]{1,2})/", $date, $matches))
		   {                       
			   $obj->year = $matches['year'];
			   $obj->date = sprintf("%d-%02d-%02d", $matches['year'], $matches['month'], $matches['day']);			   
		   }
		   

		   if (preg_match("/^(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})\/(\/)?$/", $date, $matches))
		   {                       
				   $obj->year = $matches['year'];
		   }

		   if (preg_match("/^(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})$/", $date, $matches))
		   {                       
				   $obj->year = $matches['year'];
		   }

		   if (preg_match("/[0-9]{4}\/\/\//", $date))
		   {                       
			   $year = trim(preg_replace("/\/\/\//", "", $date));
			   if ($year != '')
			   {
					   $obj->year = $year;
			   }
		   }

		   if (preg_match("/^[0-9]{4}$/", $date))
		   {                       
				  $obj->year = $date;
		   }
		   
		   
		   if (preg_match("/^(?<year>[0-9]{4})\-[0-9]{2}\-[0-9]{2}$/", $date, $matches))
		   {
		   		$obj->year = $matches['year'];
				$obj->date = $date;
		   }
		   
		   if (!isset($obj->year))
		   {
		   		$obj->year = $value;
		   		$obj->year = str_replace('/', '', $obj->year);
		   }
		   
		   break;
		   
		case 'KW':
			$obj->keyword[] = $value;
			break;
	
		// Mendeley 0.9.9.2
		case 'DO':
			$identifier = new stdclass;
			$identifier->type = 'doi';
			$identifier->id = $value;
			$obj->identifier[] = $identifier;			
			break;
			
			
		case 'L1':
			$link = new stdclass;
			$link->url = $value;
			$link->anchor = 'PDF';
			$obj->link[] = $link;
			break;
			

		case 'UR':
			
			// WOS/ZOOREC
			if (preg_match('/\<Go to ISI\>:\/\/(?<id>.*)/', $value, $m))
			{
				$identifier = new stdclass;
				$identifier->type = 'wos';
				$identifier->id = $m['id'];
				
				$obj->identifier[] = $identifier;	
			}
			else
			{
				$link = new stdclass;
				$link->url = $value;
			
				if (preg_match('/\.pdf$/', $value))
				{
					$link->anchor = 'PDF';
				}
				else
				{
					$link->anchor = 'LINK';
				}
			
				$obj->link[] = $link;
			
				// extract...
				if (preg_match('/https?:\/\/hdl.handle.net\/(?<id>.*)/', $value, $m))
				{
					$identifier = new stdclass;
					$identifier->type = 'handle';
					$identifier->id = $m['id'];
				
					$obj->identifier[] = $identifier;				
				}
			
				if (preg_match('/https:\/\/digital.csic.es\/handle\/(?<id>.*)/', $value, $m))
				{
					$identifier = new stdclass;
					$identifier->type = 'handle';
					$identifier->id = $m['id'];
				
					$obj->identifier[] = $identifier;				
				}
			

				if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>.*)/', $value, $m))
				{
					$identifier = new stdclass;
					$identifier->type = 'jstor';
					$identifier->id = $m['id'];
				
					$obj->identifier[] = $identifier;				
				}
			
			}			
			break;			

		case 'ID':
			$obj->publisher_id = $value;
			break;	
			
		case 'XM':
			$link = new stdclass;
			$link->url = $value;
			$link->anchor = 'XML';
			$obj->link[] = $link;
			break;
			
		default:
			break;
	}
}



//--------------------------------------------------------------------------------------------------
function import_ris($ris, $callback_func = '')
{
	global $debug;
	
	$volumes = array();
	
	$rows = explode("\n", $ris);
	
	$state = 1;	
		
	foreach ($rows as $r)
	{
		$parts = explode ("  - ", $r);
		
		$key = '';
		if (isset($parts[1]))
		{
			$key = trim($parts[0]);
			$value = trim($parts[1]); // clean up any leading and trailing spaces
		}
				
		if (isset($key) && ($key == 'TY'))
		{
			$state = 1;
			$obj = new stdClass();
			$obj->authors = array();
			
			if ('JOUR' == $value)
			{
				$obj->genre = 'article';
			}
			if ('BOOK' == $value)
			{
				$obj->genre = 'book';
			}
			if ('ABST' == $value)
			{
				$obj->genre = 'article';
			}
			if ('THES' == $value)
			{
				$obj->genre = 'thesis';
			}
		}
		if (isset($key) && ($key == 'ER'))
		{
			$state = 0;
			
						
			// Cleaning...						
			if ($debug)
			{
				print_r($obj);
			}	
			
			if ($callback_func != '')
			{
				$callback_func($obj);
			}
			
		}
		
		if ($state == 1)
		{
			if (isset($value))
			{
				process_ris_key($key, $value, $obj);
			}
		}
	}
	
	
}


//--------------------------------------------------------------------------------------------------
// Use this function to handle very large RIS files
function import_ris_file($filename, $callback_func = '')
{
	global $debug;
	$debug = false;
	
	$file_handle = fopen($filename, "r");
			
	$state = 1;	
	
	while (!feof($file_handle)) 
	{
		$r = fgets($file_handle);
//		$parts = explode ("  - ", $r);
		$parts = preg_split ('/  -\s+/', $r);
		
		//print_r($parts);
		//echo $r . "\n";
		
		$key = '';
		if (isset($parts[1]))
		{
			$key = trim($parts[0]);
			$value = trim($parts[1]); // clean up any leading and trailing spaces
		}
				
		if (isset($key) && ($key == 'TY'))
		{
			$state = 1;
			$obj = new stdClass();
			
			if ('JOUR' == $value)
			{
				$obj->type = 'article';
			}
			// Ingenta
			if ('ABST' == $value)
			{
				$obj->type = 'article';
			}
			
			if ('BOOK' == $value)
			{
				$obj->type = 'book';
			}
			if ('THES' == $value)
			{
				$obj->type = 'thesis';
			}
		}
		if (isset($key) && ($key == 'ER'))
		{
			$state = 0;
						
			// Cleaning...						
			if ($debug)
			{
				print_r($obj);
			}	
			
			if ($callback_func != '')
			{
				
				$callback_func($obj);
			}
			
		}
		
		if ($state == 1)
		{
			if (isset($value))
			{
				process_ris_key($key, $value, $obj);
			}
		}
	}
	
	
}


?>