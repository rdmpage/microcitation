<?php

// Export reference(s) in RIS format

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');

//----------------------------------------------------------------------------------------
function fix_latin1_mangled_with_utf8_maybe_hopefully_most_of_the_time($str)
{
    return preg_replace_callback('#[\\xA1-\\xFF](?![\\x80-\\xBF]{2,})#', 'utf8_encode_callback', $str);
}

//----------------------------------------------------------------------------------------
function utf8_encode_callback($m)
{
    return utf8_encode($m[0]);
}


$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';

$guid = '';

if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
}

$debug = false;
if (isset($_GET['debug']))
{
	$debug = true;
}




//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

//$db->EXECUTE("set publications 'utf8'"); 
$db->EXECUTE("set names 'utf8'"); 


//--------------------------------------------------------------------------------------------------

$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

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

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{

	$issn = ''; // use this as a flag for some post processing
	
	//print_r($result);
	
	$thumbnail_url = '';

	$reference = new stdclass;
	
	$reference->guid = $result->fields['guid'];
	
	$reference->type = 'article';
	
	if ($result->fields['type'] == 'book')
	{
		$reference->type = 'book';
	}
	
	$reference->title = $result->fields['title'];
	$reference->title = strip_tags($reference->title);
	
	if ($reference->type == 'article')
	{
	
		$reference->journal = new stdclass;
		$reference->journal->name = $result->fields['journal'];
	
		if ($result->fields['series'] != '')
		{
			$reference->journal->series = $result->fields['series'];
		}
	
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
			$identifier = new stdclass;
			$identifier->type = 'issn';
			$identifier->id = $result->fields['issn'];
			
			$issn = $result->fields['issn'];
		
			$reference->journal->identifier[] = $identifier;
		}
		if ($result->fields['eissn'] != '')
		{
			$identifier = new stdclass;
			$identifier->type = 'issn';
			$identifier->id = $result->fields['eissn'];

			$reference->journal->identifier[] = $identifier;
		}
	}	
	
	
	// Date to do: handle dates not just year
	if ($result->fields['year'] != '')
	{
		$reference->year = $result->fields['year'];
	}	

	if ($result->fields['date'] != '')
	{
		$reference->date = $result->fields['date'];
	}	
	
	
	// authors
	if ($result->fields['authors'] != '')
	{
		$delimiter = ';';
		
		if ($issn == '1000-470X')
		{
			$delimiter = ',';
		}
	
	
		$authors = explode($delimiter, trim($result->fields['authors']));
		
		foreach ($authors as $a)
		{
			if ($a != '')
			{
				$author = new stdclass;
				
				
				if (preg_match('/\p{Han}+/u', $a))
				{
					$author->name = trim($a);
				}
				else
				{
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
				}
			
				$reference->author[] = $author;
			}
		}
	}	
	
	// identifiers and links
	
	if ($result->fields['cinii'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'cinii';
		$identifier->id = $result->fields['cinii'];
		$reference->identifier[] = $identifier;
	}
	
	
	if ($result->fields['doi'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'doi';
		$identifier->id = $result->fields['doi'];
		$reference->identifier[] = $identifier;
	}
	if ($result->fields['handle'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'handle';
		$identifier->id = $result->fields['handle'];
		$reference->identifier[] = $identifier;
	}
	
	if ($result->fields['isbn10'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'isbn';
		$identifier->id = $result->fields['isbn10'];
		$reference->identifier[] = $identifier;
	}

	if ($result->fields['isbn13'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'isbn';
		$identifier->id = $result->fields['isbn13'];
		$reference->identifier[] = $identifier;
	}
	
	if ($result->fields['pmid'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'pmid';
		$identifier->id = $result->fields['pmid'];
		$reference->identifier[] = $identifier;
	}
	if ($result->fields['pmc'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'pmc';
		$identifier->id = $result->fields['pmc'];
		$reference->identifier[] = $identifier;
	}
	
	if ($result->fields['jstor'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'jstor';
		$identifier->id = $result->fields['jstor'];
		$reference->identifier[] = $identifier;
	}

	if ($result->fields['cinii'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'cinii';
		$identifier->id = $result->fields['cinii'];
		$reference->identifier[] = $identifier;
	}
	
	
	
	// zootaxa
	if ($result->fields['zootaxa_url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = $result->fields['zootaxa_url'];
		$reference->link[] = $link;
		
		if ($thumbnail_url == '')
		{
			$thumbnail_url = $link->url;
		}
	}	
	if ($result->fields['zootaxa_pdf_url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'PDF';
		$link->url = $result->fields['zootaxa_pdf_url'];
		$reference->link[] = $link;
		
		if ($thumbnail_url == '')
		{
			$thumbnail_url = $link->url;
		}
		
	}	
	
	
	
	if ($result->fields['url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = $result->fields['url'];
		$reference->link[] = $link;
	}	
	if ($result->fields['pdf'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'PDF';
		$link->url = $result->fields['pdf'];
		$reference->link[] = $link;
		
		if ($thumbnail_url == '')
		{
			$thumbnail_url = $link->url;
		}
		
	}	
	
	
	// Zenodo, may be DOI or URL
	if ($result->fields['zenodo'] != '')
	{
		$zenodo = $result->fields['zenodo'];
		if (preg_match('/^10.5281\/zenodo/', $zenodo))
		{
			$identifier = new stdclass;
			$identifier->type = 'doi';
			$identifier->id = $zenodo;
			$reference->identifier[] = $identifier;
			
			$zenodo = str_replace('10.5281/zenodo.', 'https://zenodo.org/record/', $zenodo);
		}

		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = $zenodo;
		$reference->link[] = $link;		
		
		$zenodo = str_replace('https://zenodo.org/record/', '', $zenodo);
		
		$identifier = new stdclass;
		$identifier->type = 'zenodo';
		$identifier->id = $zenodo;
		$reference->identifier[] = $identifier;
	
	
	}
	
	
	

	if ($result->fields['xml'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'XML';
		$link->url = $result->fields['xml'];
		$reference->link[] = $link;
	}	
	
	// alternative identifiers
	if ($result->fields['pii'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'pii';
		$identifier->id = $result->fields['pii'];
		$reference->identifier[] = $identifier;
	}
	if ($result->fields['oai'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'oai';
		$identifier->id = $result->fields['oai'];
		$reference->identifier[] = $identifier;
	}
	

	if ($result->fields['publisher'] != '')
	{
		$reference->publisher = $result->fields['publisher'];
	}
	if ($result->fields['publoc'] != '')
	{
		$reference->publoc = $result->fields['publoc'];
	}
	
	
	
	// abstract
	if ($result->fields['abstract'] != '')
	{
		$reference->abstract = trim($result->fields['abstract']);
	}
	
	// thumbnails
	if ($thumbnail_url != '')
	{
		//echo $thumbnail_url . '<br/>';
		if (0)
		{
			get_pdf_thumbnail($reference, $thumbnail_url);
		}
	}
		

	if ($result->fields['jstor'] != '')
	{
		//get_jstor_thumbnail($reference, $result->fields['jstor']);
	}

	if ($result->fields['thumbnailUrl'] != '')
	{
		$reference->thumbnailUrl = $result->fields['thumbnailUrl'];
	}

	// multilingual data
	
	$sql = 'SELECT * FROM multilingual WHERE guid="' . $guid . '"';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$key = $result->fields['key'];
		$language = $result->fields['language'];
		$value = $result->fields['value'];
		
		//echo $value . '<br/>';
	
		switch ($key)
		{
			case 'title':
			case 'abstract':
				if (!isset($reference->multi))
				{
					$reference->multi = new stdclass;
					$reference->multi->_key = new stdclass;
				}
				if (!isset($reference->multi->_key->{$key}))
				{
					$reference->multi->_key->{$key}  = new stdclass;
				}
				$reference->multi->_key->{$key}->{$language} = $value;
				break;
				
			
			case 'journal':
				if (!isset($reference->journal->multi))
				{
					$reference->journal->multi = new stdclass;
					$reference->journal->multi->_key = new stdclass;
				}
				if (!isset($reference->journal->multi->_key->name))
				{
					$reference->journal->multi->_key->name  = new stdclass;
				}
				$reference->journal->multi->_key->name->{$language} = $value;			
				break;
			
				
			case 'authors':
				// big assumption, we've parsed author names OK
				
				$delimiter = ';';
				
				if ($issn == '1000-470X')
				{
					$delimiter = ',';
				}
				
				$authors = explode($delimiter, trim($value));
				
				$n = count($authors);
				
				if ($n > 0)
				{
					if (!isset($reference->author))
					{
						$reference->author = array();
					}
					
				}
				for ($i = 0; $i < $n; $i++)
				{
					if (!isset($reference->author[$i]))
					{
						$reference->author[$i] = new stdclass;
					}
					if (!isset($reference->author[$i]->multi))
					{
						$reference->author[$i]->multi = new stdclass;
						$reference->author[$i]->multi->_key = new stdclass;
					}
					if (!isset($reference->author[$i]->multi->_key->literal))
					{
						$reference->author[$i]->multi->_key->literal  = new stdclass;
					}
					
					// sanity check, there may be cases where names are not in the 
					// correct language (e.g., English names in CiNii)
					
					$ok = true;
					
					if ($language == 'ja')
					{
						$ok = preg_match('/\p{Han}+/u', $authors[$i]);
					}
					/*
					if ($language == 'zh')
					{
						$ok = preg_match('/\p{Han}+/u', $authors[$i]);
					}
					*/
					
					if ($ok)
					{
						$reference->author[$i]->multi->_key->literal->{$language} = $authors[$i];								
					}
				}
				break;
				
				
			default:
				break;
		}
		
		
		
		
				
		$result->MoveNext();
	}	
	

	if (0)
	{
		echo '<pre>';
		print_r($reference);
		echo '</pre>';
	}
	
	if (1)
	{	
		/*
		echo '<pre>';
		$nt = reference_to_rdf($reference);
		echo htmlentities($nt);		
		echo '</pre>';
		*/
		
		$nt = reference_to_rdf($reference);
		echo $nt;
	}

	
}

?>