<?php

// Export reference(s) in RIS format

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');

function fix_latin1_mangled_with_utf8_maybe_hopefully_most_of_the_time($str)
{
    return preg_replace_callback('#[\\xA1-\\xFF](?![\\x80-\\xBF]{2,})#', 'utf8_encode_callback', $str);
}

function utf8_encode_callback($m)
{
    return utf8_encode($m[0]);
}

//----------------------------------------------------------------------------------------
// JSTOR thumbnail
function get_jstor_thumbnail(&$reference, $jstor)
{
	$thumbnail_dir = dirname(dirname(dirname(dirname(__FILE__)))) . '/Development/jstor-thumbnails-o';
	
	// GIF
	$filename = $thumbnail_dir . '/' . $jstor . '.gif';
	
	// if no GIF try JPEG
	if (!file_exists($filename))
	{
		$filename = $thumbnail_dir . '/' . $jstor . '.jpg';
	}

	if (!file_exists($filename))
	{
		$filename = $thumbnail_dir . '/' . $jstor . '.jpeg';
	}
			
	if (file_exists($filename))
	{		
		$image_type = exif_imagetype($filename);
		switch ($image_type)
		{
			case IMAGETYPE_GIF:
				$mime_type = 'image/gif';
				break;
			case IMAGETYPE_JPEG:
				$mime_type = 'image/jpg';
				break;
			case IMAGETYPE_PNG:
				$mime_type = 'image/png';
				break;
			case IMAGETYPE_TIFF_II:
			case IMAGETYPE_TIFF_MM:
				$mime_type = 'image/tif';
				break;
			default:
				$mime_type = 'image/gif';
				break;
		}
		
		$image = file_get_contents($filename);
		$base64 = chunk_split(base64_encode($image));
		$reference->thumbnail = 'data:' . $mime_type . ';base64,' . $base64;				
	}
}

//----------------------------------------------------------------------------------------
// PDF thumbnail 
function get_pdf_thumbnail(&$reference, $pdf)
{
	
	$url = 'http://direct.bionames.org/bionames-archive/pdfstore?url=' . urlencode($pdf) . '&noredirect&format=json';
	$json = get($url);
	
	//echo $url;
	
	$obj = json_decode($json);
	
	//print_r($obj);
	
	if ($obj->http_code == 200)
	{		
		$url = 'http://direct.bionames.org/bionames-archive/documentcloud/pages/' . $obj->sha1 . '/1-small';
//		$url = 'http://bionames.org/bionames-archive/documentcloud/pages/a5228371107ec0685cbe82f863c01dc097f3af94/1-small';
		//$url = 'http://direct.bionames.org/bionames-archive/pdf/34/bc/89/34bc89cd65c9e80a4d5f9bc8b9c6c97ce2e02287/images/thumbnails/page-0.png';

		//$url = 'http://direct.bionames.org/bionames-archive/pdf/44/90/63/449063c9652780cd06a1b0a2f5dee76f775521a9/images/thumbnails/page-0.png';
		//echo $url;
		//exit();
		$image = get($url);
		
		if ($image != '')
		{				
			$mime_type = 'image/png';
			$base64 = chunk_split(base64_encode($image));
			$reference->thumbnail = 'data:' . $mime_type . ';base64,' . $base64;		
		}	
		//print_r($reference);exit();	
	}
}


$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';

//$guid = '';

if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
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
	
	if ($result->fields['internetarchive'] != '')
	{
		$identifier = new stdclass;
		$identifier->type = 'internetarchive';
		$identifier->id = $result->fields['internetarchive'];
		$reference->identifier[] = $identifier;
	}
	
	
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
		get_jstor_thumbnail($reference, $result->fields['jstor']);
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
	
	// Add PDF images, etc.
	if (0)
	{
	
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
				
					$c['page-images'] = array();
					$c['page-thumbnails'] = array();
					for ($i = 1; $i <= $obj->pages; $i++)
					{
						$c['page-images'][$i] = 'http://bionames.org/bionames-archive/documentcloud/pages/' . $sha1 . '/' . $i . '-large';
						$c['page-thumbnails'][$i] = 'http://bionames.org/bionames-archive/documentcloud/pages/' . $sha1 . '/' . $i . '-small';
					}	

					$c['page-text'] = array();
					for ($i = 1; $i <= $obj->pages; $i++)
					{
						$text = get('http://bionames.org/bionames-archive/documentcloud/pages/' . $sha1 . '/' . $i);
						if ($text != '')
						{
							$c['page-text'][] = json_decode($text);
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
	}	
	
	
	echo json_encode($c);
}

?>