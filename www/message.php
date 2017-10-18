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
				for ($i = 0; $i < $n; $i++)
				{
					if (!isset($reference->author[$i]->multi))
					{
						$reference->author[$i]->multi = new stdclass;
						$reference->author[$i]->multi->_key = new stdclass;
					}
					if (!isset($reference->author[$i]->multi->_key->name))
					{
						$reference->author[$i]->multi->_key->name  = new stdclass;
					}
					$reference->author[$i]->multi->_key->name->{$language} = $authors[$i];								
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
	
	
	$doc = new stdclass;
		
	$doc->_id = $reference->guid;
	$doc->cluster_id = $reference->guid;
	
	$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';
	$doc->{'message-timestamp'} = date("c", time());
	$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
	
	
	$doc->message = reference_to_citeprocjs($reference);
	
	
	if (preg_match('/^10\./', $reference->guid))
	{
		$doc->_id = 'http://dx.doi.org/' . $reference->guid;
	}
	
	echo json_encode($doc);
	
	//print_r($doc);

	/*	
		
	echo '<pre>';
	$c = reference_to_citeprocjs($reference);
	
	unset($c['id']);
	
	print_r($c);
	echo '</pre>';
	
	
	$c = reference_to_citeprocjs($reference);
	echo json_encode($c);
	*/
}

?>