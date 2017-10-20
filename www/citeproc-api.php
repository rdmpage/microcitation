<?php

// Export reference(s) in RIS format

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');


$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';

$guid = '';

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

	//print_r($result);

	$reference = new stdclass;
	
	$reference->guid = $result->fields['guid'];
	
	$reference->type == 'article';
	$reference->title = $result->fields['title'];
	$reference->title = strip_tags($reference->title);
	
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
		
		$reference->journal->identifier[] = $identifier;
	}
	if ($result->fields['eissn'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'issn';
		$identifier->id = $result->fields['eissn'];

		$reference->journal->identifier[] = $identifier;
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
		$authors = explode(";", trim($result->fields['authors']));
		
		foreach ($authors as $a)
		{
			if ($a != '')
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
	}	
	
	// identifiers and links
	
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
	
	
	// zootaxa
	if ($result->fields['zootaxa_url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = $result->fields['zootaxa_url'];
		$reference->link[] = $link;
	}	
	if ($result->fields['zootaxa_pdf_url'] != '')
	{
		$link = new stdclass;
		$link->anchor = 'PDF';
		$link->url = $result->fields['zootaxa_pdf_url'];
		$reference->link[] = $link;
	}	
	
	
	
	// abstract
	if ($result->fields['abstract'] != '')
	{
		$reference->abstract = trim($result->fields['abstract']);
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
				$authors = explode(";", trim($value));
				
				
				
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
					$reference->author[$i]->multi->_key->literal->{$language} = $authors[$i];								
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
	
	if (0)
	{	
		echo '<pre>';
		$c = reference_to_citeprocjs($reference);
	
		unset($c['id']);
	
		print_r($c);
		echo '</pre>';
	}
	
	
	$c = reference_to_citeprocjs($reference);
	
	if (0)
	{
		echo '<pre>';
		print_r($c);
		echo '</pre>';
	}
	
	$link_index = -1;
	
	if (isset($c['link']))
	{
		$n = count($c['link']);
		for ($i = 0; $i < $n; $i++)
		{
			if ($c['link'][$i]->{'content-type'} == 'application/pdf')
			{
				$link_index = $i;
			}
		}
	}
		
	
	
	if ($link_index > -1)
	{
		$pdf = $c['link'][$link_index]->URL;
		$sql = 'SELECT * FROM sha1 WHERE pdf="' . addcslashes($pdf, '"') . '" LIMIT 1';
		
		// echo $sql;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 1) 
		{
			$sha1 = $result->fields['sha1'];
			
			$c['link'][$link_index]->sha1 = $sha1;
			
			// get details
			$json = get('http://bionames.org/bionames-archive/documentcloud/' . $sha1 . '.json');
			if ($json)
			{
				$obj = json_decode($json);
				
				$c['pdf_pages'] = array();
				for ($i = 1; $i <= $obj->pages; $i++)
				{
					$c['pdf_pages'][$i] = 'http://bionames.org/bionames-archive/documentcloud/pages/' . $sha1 . '/' . $i . '-small';
				}	

				$c['text_pages'] = array();
				for ($i = 1; $i <= $obj->pages; $i++)
				{
					$text = get('http://bionames.org/bionames-archive/documentcloud/pages/' . $sha1 . '/' . $i);
					if ($text != '')
					{
						$c['text_pages'][] = json_decode($text);
					}
				}	
				if (count($c['text_pages']) == 0)
				{
					unset($c['text_pages']);
				}
				
				
							
			}
						
			// OCR text?
			
			
		}
		
	
	
	}
	
	
	
	echo json_encode($c);
}

?>