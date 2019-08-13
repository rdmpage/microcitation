<?php

// fetch from CrossRef
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/ris.php');
require_once (dirname(__FILE__) . '/simplehtmldom_1_5/simple_html_dom.php');


//--------------------------------------------------------------------------------------------------
// meta
function get_meta($doi, &$keys, &$values)
{
	$url = 'http://dx.doi.org/' . $doi;
	
	// echo $url . "\n";
	
	$html = get($url, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/600.2.5 (KHTML, like Gecko) Version/8.0.2 Safari/600.2.5');
	
	
	if (1 && ($html != ''))
	{
		$dom = str_get_html($html);
		
		if ($dom) {

			$metas = $dom->find('meta');

			
			foreach ($metas as $meta)
			{
				//echo $meta->name . " " . $meta->content . "\n";
			}
			

			foreach ($metas as $meta)
			{
				switch ($meta->name)
				{
					/*
					case 'citation_volume':
						$keys[] = 'volume';
						$values[] = "'" . addcslashes($meta->content, "'") . "'";	
						break;		
					*/	
			
					
					case 'citation_firstpage':
						$keys[] = 'spage';
						$values[] = "'" . addcslashes($meta->content, "'") . "'";	
						break;
					
					
					
					case 'citation_lastpage':
						$keys[] = 'epage';
						$values[] = "'" . addcslashes($meta->content, "'") . "'";	
						break;
						
					/*
					case 'citation_volume':
						$keys[] = 'volume';
						$values[] = "'" . addcslashes($meta->content, "'") . "'";	
						break;
					*/
					/*					
					case 'citation_abstract_html_url':
						$keys[] = 'url';
						$values[] = "'" . addcslashes($meta->content, "'") . "'";	
						break;
			
			
					case 'citation_pdf_url':
						$keys[] = 'pdf';
						$values[] = "'" . addcslashes($meta->content, "'") . "'";	
						break;
					*/	
					
					default:
						break;
				}
			}
		}
	}
	else
	{	
	
	//echo $html;	
	
	$html = str_replace("\n", "", $html);
	
	if (preg_match('/<meta\s+name="DC.Source.ISSN"\s+content="(?<content>.*)"\s*\/>/Uu', $html, $m))
	{
		//print_r($m);
		
		if (!in_array('issn', $keys))
		{
			$keys[] = 'issn';
			$values[] = "'" . addcslashes($m['content'], "'") . "'";
		}
	}
	
	if (preg_match('/<meta\s+name="citation_pdf_url"\s+content="(?<content>.*)"\s*\/>/Uu', $html, $m))
	{
		//print_r($m);
		
		if (!in_array('pdf', $keys))
		{
			$keys[] = 'pdf';
			$values[] = "'" . addcslashes($m['content'], "'") . "'";
		}
	}

	if (preg_match('/<meta\s+name="citation_pdf_url"\s+content="(?<content>.*)"\s*\/>/Uu', $html, $m))
	{
		//print_r($m);
		
		if (!in_array('pdf', $keys))
		{
			$keys[] = 'pdf';
			$values[] = "'" . addcslashes($m['content'], "'") . "'";
		}
	}


	if (preg_match('/<meta\s+name="DC.Identifier.URI"\s+content="(?<content>.*)"\s*\/>/Uu', $html, $m))
	{
		//print_r($m);
		
		if (!in_array('url', $keys))
		{
			$keys[] = 'url';
			$values[] = "'" . addcslashes($m['content'], "'") . "'";
		}
	}

	if (preg_match('/<meta\s+name="DC.Source.Volume"\s+content="(?<content>.*)"\s*\/>/Uu', $html, $m))
	{
		//print_r($m);
		
		if (!in_array('volume', $keys))
		{
			$keys[] = 'volume';
			$values[] = "'" . addcslashes($m['content'], "'") . "'";
		}
	}
	
	if (preg_match('/<meta\s+name="citation_volume"\s+content="(?<content>.*)"\s*\/>/Uu', $html, $m))
	{
		//print_r($m);
		
		if (!in_array('volume', $keys))
		{
			$keys[] = 'volume';
			$values[] = "'" . addcslashes($m['content'], "'") . "'";
		}
	}
	
	
	if (preg_match('/<meta\s+name="citation_firstpage"\s+content="p\.\s+(?<spage>\d+)[-|-](?<epage>\d+)"\s*\/>/Uu', $html, $m))
	{
		$keys[] = 'spage';
		$values[] = "'" . addcslashes($m['spage'], "'") . "'";		
		$keys[] = 'epage';
		$values[] = "'" . addcslashes($m['epage'], "'") . "'";		
	}
	
	}


}

//--------------------------------------------------------------------------------------------------
function get_title($doi)
{
	$title = '';
	
	$url = 'http://data.crossref.org/' . $doi;
//	$url = 'http://dx.doi.org/' . $doi;
	$json = get($url, '', "application/citeproc+json;q=1.0");
	
	if ($json != '')
	{
		$citeproc = json_decode($json);
		
		//print_r($citeproc);exit();
	
		$title = $citeproc->title;
	}
	
	return $title;
}
	
	
	
//--------------------------------------------------------------------------------------------------
// Use content negotian and citeproc, may fail with some DOIs
// e.g. [14/12/2012 12:52] curl --proxy wwwcache.gla.ac.uk:8080 -D - -L -H   "Accept: application/citeproc+json;q=1.0" "http://dx.doi.org/10.1080/03946975.2000.10531130" 

function get_doi_metadata($doi, &$json)
{
	$reference = null;
	
	$url = 'http://data.crossref.org/' . $doi;
//	$url = 'http://dx.doi.org/' . $doi;
	$json = get($url, '', "application/citeproc+json;q=1.0");
	
	echo $url;
	echo $json;
	//exit();
	
	if ($json == '')
	{
		return $reference;
	}
	
	$citeproc = json_decode($json);

	if ($citeproc == null)
	{
		return $reference;
	}
		
	$reference = new stdclass;
	$reference->type = 'generic';
	
	$crossref = new stdclass;
	$crossref->time = date(DATE_ISO8601, time());
	$crossref->url = 'http://dx.doi.org/' . $doi;
	$reference->provenance['crossref'] = $crossref;
		
	$reference->title = $citeproc->title;
	if ($reference->title != '')
	{
		// clean
		$reference->title = strip_tags($reference->title);
		
		$reference->title = preg_replace('/\s\s+/u', ' ', $reference->title);
		$reference->title = preg_replace('/^\s+/u', '', $reference->title);
		$reference->title = preg_replace('/\s+$/u', '', $reference->title);
		
	}
	
	$reference->identifier = array();
	$identifier = new stdclass;
	$identifier->type = 'doi';
	$identifier->id = $citeproc->DOI;
	$reference->identifier[] = $identifier;
	
	if ($citeproc->type == 'article-journal')
	{
		$reference->type = 'article';
		$reference->journal = new stdclass;
		$reference->journal->name = $citeproc->{'container-title'};
		$reference->journal->volume = $citeproc->volume;
		if ($citeproc->issue)
		{
			$reference->journal->issue = $citeproc->issue;
		}
		$reference->journal->pages = $citeproc->page;
		
		if (preg_match('/--/', $reference->journal->pages))
		{
		}
		else
		{
			$reference->journal->pages = str_replace('-', '--', $reference->journal->pages);
		}
		
	}

	if ($citeproc->issued->{'date-parts'})
	{
		$reference->year = $citeproc->issued->{'date-parts'}[0][0];
	}
	else
	{
		if (isset($citeproc->issued->raw))
		{
			if (preg_match('/^[0-9]{4}$/', $citeproc->issued->raw))
			{
				$reference->year = $citeproc->issued->raw;
			}
		}
	}
	$reference->issued = $citeproc->issued;
	
	if (isset($citeproc->publisher))
	{
		$reference->publisher = $citeproc->publisher;
	}
	
	if (isset($citeproc->author))
	{
		$reference->author = array();
		foreach ($citeproc->author as $a)
		{
			// clean up name
			$author = new stdclass;
			
			if (isset($a->literal))
			{
				if (preg_match('/^(?<lastname>.*),\s+(?<firstname>.*)$/Uu', $a->literal, $m))
				{
					$author->firstname = $m['firstname'];
					$author->lastname = $m['lastname'];
					$author->name = $author->firstname . ' ' . $author->lastname;
				}
				else
				{
					$author->name = $a->literal;
				}
			}
			else
			{
				if (isset($a->given))
				{			
					$author->firstname = $a->given;
					
					// Initials without space (try and catch long names that are all capitals by ignoring names > 3 characters)
					if (preg_match('/^[A-Z]+$/', $a->given) && (strlen($a->given) < 3))
					{
						$initials = str_split($a->given);
						$author->firstname = join(' ', $initials);						
					}					
					
					$author->firstname = preg_replace('/\.([A-Z])/Uu', ' $1', $author->firstname);
					$author->firstname = preg_replace('/\./Uu', '', $author->firstname);
					$author->firstname = mb_convert_case($author->firstname, MB_CASE_TITLE, 'UTF-8');
					$author->lastname = mb_convert_case($a->family, MB_CASE_TITLE, 'UTF-8');
					$author->name = $author->firstname . ' ' . $author->lastname;
				}
			}
			$reference->author[] = $author;
		}
	}
	//print_r($reference);exit();
	return $reference;
}
	
	
//--------------------------------------------------------------------------------------------------
// Extra bits from XML
function get_doi_metadata_unixref($doi)
{
	global $config;
	
	$data = array();
	
	$url = 'http://www.crossref.org/openurl?pid=r.page@bio.gla.ac.uk&rft_id=info:doi/' . $doi . '&noredirect=true&format=unixref';
	
	$xml = get($url);
	
	//echo $xml;
	
	//exit();
	
			
	if (preg_match('/<doi_record/', $xml))
	{		
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);

		// Springer Kew Bulletin has article numbers
		$xpath_query = '//publisher_item/item_number[@item_number_type="article-number"]';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			$data['article_number'] = $node->firstChild->nodeValue;
		}
	
	}
	
	return $data;
}	

//--------------------------------------------------------------------------------------------------
// JSTOR
function get_pages_from_page($doi)
{
	$pages = array();
	$url = 'http://dx.doi.org/' . $doi;
	
	//echo $url . "\n";
	
	$html = get($url, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/600.2.5 (KHTML, like Gecko) Version/8.0.2 Safari/600.2.5');
		
	if (preg_match('/Pages (?<spage>\d+)–(?<epage>\d+),/Uu', $html, $m))
	{
		//print_r($m);
		
		$pages[] = $m['spage'];
		$pages[] = $m['epage'];
	}
		
	return $pages;
}

//--------------------------------------------------------------------------------------------------
// meta
function get_pages_from_page_meta($doi)
{
	$pages = array();
	$url = 'http://dx.doi.org/' . $doi;
	
	//echo $url . "\n";
	
	$html = get($url, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/600.2.5 (KHTML, like Gecko) Version/8.0.2 Safari/600.2.5');
	
	//echo $html;	
	
	$html = str_replace("\n", "", $html);
	
	// Pensoft
	// <meta name="eprints.pagerange" content="13-17">
	if (count($pages) == 0)
	{
		if (preg_match('/<meta\s+name="eprints.pagerange"\s+content="(?<spage>\d+)(-(?<epage>\d+))?"\s*\/>/Uu', $html, $m))
		{
			//print_r($m);
			$pages[] = $m['spage'];	
			$pages[] = $m['epage'];	
		}
	}	
	
	// 10.5902/2358198013897
	// <meta name="citation_firstpage" content="p. 17-34"/>
	if (count($pages) == 0)
	{
		if (preg_match('/<meta\s+name="citation_firstpage"\s+content="p\.\s+(?<spage>\d+)[-|-](?<epage>\d+)"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['spage'];		
			$pages[] = $m['epage'];		
		}
	}	
	
	
	// default
	if (count($pages) == 0)
	{
		if (preg_match('/<meta\s+name="citation_firstpage"\s+content="(?<spage>\d+)"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['spage'];		
		}
		if (preg_match('/<meta\s+name="citation_lastpage"\s+content="(?<epage>\d+)"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['epage'];		
		}
	}	
	
	// Ingenta
	if (count($pages) == 0)
	{
		// <meta name="DCTERMS.bibliographicCitation" content="Systematic Botany, 37, 2, 307-319(13)"/>
		if (preg_match('/<meta\s+name="DCTERMS.bibliographicCitation"\s+content="(?<journal>.*),\s+(?<volume>\d+),\s+(?<issue>\d+),\s+(?<spage>\d+)-(?<epage>\d+)(\(.*\))?"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['spage'];	
			$pages[] = $m['epage'];	
		}
	}	
		
	// AOSIS, e.g. http://abcjournal.org/index.php/ABC/article/view/209
	if (count($pages) == 0)
	{
		// <meta name="DC.Identifier.pageNumber" content="172-174">
		if (preg_match('/<meta\s+name="DC.Identifier.pageNumber"\s+content="(?<spage>\d+)(-(?<epage>\d+))?"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['spage'];	
			$pages[] = $m['epage'];	
		}
	}	
	
	
	// BioOne 
	if (count($pages) == 0)
	{
	
		// <meta property="og:description" content="GEORGE K. ROGERS (2005) THE GENERA OF RUBIACEAE IN THE SOUTHEASTERN UNITED STATES, PART II. SUBFAMILY RUBIOIDEAE, AND SUBFAMILY CINCHONOIDEAE REVISITED (CHIOCOCCA, ERITHALIS, AND GUETTARDA) Harvard Papers in Botany: Vol. 10, No. 1, pp. 1-45. doi: http://dx.doi.org/10.3100/1043-4534(2005)10[1:TGORIT]2.0.CO;2">
		if (preg_match('/<meta\s+property="og:description"\s+content="(.*)\s+pp\.\s+(?<spage>\d+)-(?<epage>\d+)\.(.*)"\s+\/>/Uu', $html, $m))
	//	if (preg_match('/<meta\s+property="og:description"\s+content="(.*)"\s+\/>/Uu', $html, $m))
		{
			$pages[] = $m['spage'];	
			$pages[] = $m['epage'];		
		}
	}
	
	// Degruyter
	if (count($pages) == 0)
	{
		if (preg_match('/<meta\s+content="(?<spage>\d+)"\s+name="citation_firstpage"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['spage'];		
		}
		if (preg_match('/<meta\s+content="(?<epage>\d+)"\s+name="citation_lastpage"\s*\/>/Uu', $html, $m))
		{
			$pages[] = $m['epage'];		
		}
	}	

	echo "hello xxx\n"; exit();
		
	return $pages;
}

//--------------------------------------------------------------------------------------------------


$q = 'The Canadian Entomologist';
$issn = '0008-347X';

$q = 'Bulletin de la Société entomologique de France';
$issn = '0037-928X';


$done = false;
$page = 1;


$fetch_count = 0;

while (!$done)
{
	$url = 'http://search.crossref.org/dois?q=' . str_replace(' ', '+', $q) . '&header=true' . '&page=' . $page;

	//echo $url . "\n";
	echo "-- $url\n";

	$json = get($url);

	//echo $json;

	$obj = json_decode($json);

	//print_r($obj);
	//exit();


	$page++;


	

	foreach ($obj->items as $item)
	{
		// decode
		$query = explode('&', html_entity_decode($item->coins));
		$params = array();
		foreach( $query as $param )
		{
		  list($key, $value) = explode('=', $param);
	  
		  $key = preg_replace('/^\?/', '', urldecode($key));
		  $params[$key][] = trim(urldecode($value));
		}
	
		if (0)
		{
			// Update
			$sql = "UPDATE publications SET guid='" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'"
				. ", doi='" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'"
				. " WHERE issn='$issn' AND volume='" . addcslashes($params['rft.volume'][0], "'") . "' AND spage='" . addcslashes($params['rft.spage'][0], "'") . "'"
				. ";";
			echo $sql . "\n";
		}
		else
		{
			// Populate
			//print_r($params);
			//exit();

			$doi = '';

			$keys = array();
			$values = array();
		
			$keys[] = 'guid';
			$values[] = "'" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'";
		
			$keys[] = 'doi';
			$values[] = "'" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'";
		
			$doi = preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]);

			// title fix for Telopea
			if ($params['rft.atitle'][0] == 'English')
			{
				$doi = preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]);
				$title = get_title($doi);
				if ($title != '')
				{
					$keys[] = 'title';
					$values[] = "'" . addcslashes($title, "'") . "'";
			
				}
			}
			else
			{
				$keys[] = 'title';
				$values[] = "'" . addcslashes($params['rft.atitle'][0], "'") . "'";
			}
		
			$keys[] = 'journal';
			$values[] = "'" . addcslashes($params['rft.jtitle'][0], "'") . "'";

			$keys[] = 'issn';
			$values[] = "'" . addcslashes($issn, "'") . "'";

			
			$keys[] = 'volume';
			$values[] = "'" . addcslashes($params['rft.volume'][0], "'") . "'";
			
			
			// We may need to fetch all sorts of additional metadata (sigh)
			$enhance = true;
			//$enhance = false;

			switch ($issn)
			{
				case '0301-2123':
				case '2236-1472':
				case '1409-3871':
				case '1808-2688':
				case '0024-2829':
				case '0044-5967':
				case '0187-7151':
				case '0031-1820':
				//case '0301-2123':
				
				case '0008-347X':
				
					get_meta($doi, $keys, $values);
					
					//print_r($keys);
					
					$enhance = false;
					break;
		
				case '0081-024X':
				case '2331-7515':
					$keys[] = 'volume';
					$values[] = "'" . addcslashes($params['rft.issue'][0], "'") . "'";
					break;
			
				case '0076-3519':
					$keys[] = 'volume';
					if (isset($params['rft.volume']))
					{
						$values[] = "'" . addcslashes($params['rft.volume'][0], "'") . "'";
					}
					else
					{
						$values[] = "'" . addcslashes($params['rft.issue'][0], "'") . "'";
					}
					break;
							
				default:
					$keys[] = 'volume';
					$values[] = "'" . addcslashes($params['rft.volume'][0], "'") . "'";
					break;
			}

			if (isset($params['rft.issue']))
			{
				$keys[] = 'issue';
			
				$issue = $params['rft.issue'][0];
				$issue = preg_replace('/^0+/', '', $issue);
			
				$values[] = "'" . addcslashes($issue, "'") . "'";
			}

			if (isset($params['rft.spage']))
			{
				if (!in_array('spage', $keys))
				{
					$keys[] = 'spage';
					$values[] = "'" . addcslashes($params['rft.spage'][0], "'") . "'";
				}
			}
			

			if (isset($params['rft.epage']))
			{
				if (!in_array('epage', $keys))
				{
					$keys[] = 'epage';
					$values[] = "'" . addcslashes($params['rft.epage'][0], "'") . "'";
				}
			}
		
					
		
			if ($issn == '0529-1526') $enhance = false; // Acta Phytotaxonomica Sinica DOIs resolve but destination down :(
		
		
			if ($issn == '0366-1326') // get PDF
			{
				get_meta($doi, $keys, $values);
			}

			if ($issn == '0101-3580') // get PDF
			{
				get_meta($doi, $keys, $values);
			}

			if ($issn == '0066-7870') // get PDF
			{
				get_meta($doi, $keys, $values);
			}
			
			


		
			$enhance = false;
			if ($enhance)
			{
		
				if (!in_array('epage', $keys))
				{
					$pages = array();
			
					// JSTOR
					//$pages = get_pages_from_page(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]));
			
					// CrossRef
					$pages = get_pages_from_page_meta(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]));
			
					//print_r($pages);
			
					if (count($pages) > 0)
					{
						if (!in_array('spage', $keys))
						{
							$keys[]   = 'spage';
							$values[] = "'" . preg_replace('/^0+/', '', $pages[0]) . "'";				
						}				
			
						if (count($pages) == 2)
						{
							$keys[]   = 'epage';
							$values[] = "'" . preg_replace('/^0+/', '', $pages[1]) . "'";				
						}
					}
				}	
			}	
		
			// Springer special case, pagination is unique only within each article
			// so need article number (sigh)
		
			if ($issn == '0075-5974' && $item->year > 2013)
			{
				$data = get_doi_metadata_unixref($doi);
				//print_r($data);
			
				if (isset($data['article_number']))
				{
					$keys[] = 'article_number';
					$values[] = "'" . $data['article_number'] . "'";
				}
			}
		
			$keys[] = 'year';
			$values[] = "'" . $item->year . "'";
		
		
			if (isset($params['rft.au']))
			{
				$keys[] = 'authors';
				$authors = array();
				foreach ($params['rft.au'] as $au)
				{
					//echo $au . "\n";
					
					switch ($au)
					{
						case 'Museums Victoria':
						case 'La Trobe University':
						case 'Science Division':
						case 'Department of Environment and Conservation':
						case 'Australian Museum':
						case 'Centre for Microscopy and Microanalysis (CMM)':
						case 'The University of Queensland':
							break;
							
						default:
							$authors[] = $au;
							break;
					}
					
				}
				$values[] = "'" . addcslashes(join(";", $authors), "'") . "'";
			}
			
			
				
		
			$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
		
			/*
			$spage = '';
			$epage = '';
			$issue = '';
			while ($page = current($keys)) {
				if ($page == 'spage') {
					$spage = $values[key($keys)];
				}
				if ($page == 'epage') {
					$epage = $values[key($keys)];
				}
				if ($page == 'issue') {
					$issue = $values[key($keys)];
				}
				next($keys);
			}				

			if (($spage != '') && ($epage != ''))
			{
				$sql = 'UPDATE rdmp_reference SET epage=' . $epage . ' WHERE issn="' . $issn . '" and spage=' . $spage . ';';
			}
			*/
			echo $sql . "\n";
		
			// Dump for Google Spreadsheet
			if (0)
			{
				$n = count($keys);
				$cols = array();
				for ($i = 0; $i < $n; $i++)
				{
					switch ($keys[$i])
					{
						case 'authors':
							$cols[$keys[$i]] = str_replace(';', '&au=', $values[$i]);
						
							$cols[$keys[$i]] = preg_replace('/^\'/', '', $cols[$keys[$i]]);
							$cols[$keys[$i]] = preg_replace('/\'$/', '', $cols[$keys[$i]]);
							$cols[$keys[$i]] = preg_replace("/\\\'/", "'", $cols[$keys[$i]]);
							break;
						
						default:
							$cols[$keys[$i]] = $values[$i];
						
							$cols[$keys[$i]] = preg_replace('/^\'/', '', $cols[$keys[$i]]);
							$cols[$keys[$i]] = preg_replace('/\'$/', '', $cols[$keys[$i]]);
							$cols[$keys[$i]] = preg_replace("/\\\'/", "'", $cols[$keys[$i]]);
							break;
					}
				}
			
				echo $cols['authors'];
				echo "\t";
				echo $cols['title'];
				echo "\t";
				echo $cols['journal'];
				echo "\t";
				echo $cols['issn'];
				echo "\t";
				echo $cols['volume'];
				echo "\t";
				echo $cols['issue'];
				echo "\t";
				echo $cols['spage'];
				echo "\t";
				echo $cols['epage'];
				echo "\t";
				echo $cols['year'];
				echo "\t";			
				echo $cols['doi'];
				echo "\n";			
			
			}			
		
		
			//exit();
		
			if (($fetch_count++ % 5) == 0)
			{
				$rand = rand(1000000, 3000000);
				echo '-- sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
				usleep($rand);
			}
		
		
		}
	}
	
	
	//$done = ($fetch_count > 20);
	$done = (count($obj->items) > 10);

	
}

?>