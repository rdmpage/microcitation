<?php

// fetch from CrossRef
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/ris.php');

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
	
	$html = str_replace("\n", "", $html);
		
	if (preg_match('/<meta name="citation_firstpage" content="(?<spage>\d+)" \/>/Uu', $html, $m))
	{
		$pages[] = $m['spage'];		
	}
	if (preg_match('/<meta name="citation_lastpage" content="(?<epage>\d+)" \/>/Uu', $html, $m))
	{
		$pages[] = $m['epage'];		
	}
		
	return $pages;
}

//--------------------------------------------------------------------------------------------------

$issn = '0021-8375'; // Journal für Ornithologie

$issn = '0019-1019'; // Ibis
$issn = '0004-8038'; // Auk
$issn = '0025-1461'; // Mammalia

$issn = '0370-2774'; // Proc Zool Soc London

// Fungi
$issn = '2210-6340'; // IMA Fungus http://api.ingentaconnect.com/content/ima/imafung/latest?format=rss
$issn = '0031-5850'; // Persoonia, Mol. Phyl. Evol. Fungi http://api.ingentaconnect.com/content/nhn/pimj/latest?format=rss
$issn = '0166-0616'; // Stud. Mycol. http://www.sciencedirect.com/science?_ob=RSSURL&_method=setup&_cid=308663&md5=8a66d3ffa5669ce734c700944799cc2b
$issn = '0027-5514'; // Mycologia http://www.mycologia.org/rss/current.xml

// Proceedings of the Academy of Natural Sciences of Philadelphia (mostly in JSTOR without DOIs)
$issn = '0097-3157';

// Fungi (to do/finish)
$issn = '0007-2745'; // The Bryologist http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=bryo
$issn = '0953-7562'; // Mycological Research 

$issn = '0007-1536'; //Trans. Br. mycol. Soc. Transactions of the British Mycological Society

$issn = '0021-8375'; // Journal fuer Ornithologie

$issn = '0158-4197'; // Emu

$issn = '0373-8493'; // Mit

$issn = '0181-1584'; // Cryptogamie, Mycologie (fails, need to use title)
$issn = '0953-7562'; // Mycological research

$issn = '0002-8444'; // American Fern Journal


$count = 0;
$page = 50;
$done = false;
while (!$done)
{
	$url = 'http://search.crossref.org/dois?q=' . $issn . '&header=true' . '&page=' . $page;
	//$url = 'http://search.crossref.org/dois?q=' . urlencode('Cryptogamie, Mycologie') . '&header=true' . '&page=' . $page;

	//echo $url . "\n";

	$json = get($url);

	$obj = json_decode($json);
	
	//print_r($obj);
	//exit();

	
	$count += $obj->itemsPerPage;
	$page++;
	
	$done = ($count >= $obj->totalResults);
	//$done = ($count >= 100); 
	
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
		
		//if ($debug)
		{
			//print_r($params);
	
	
			$keys = array();
			$values = array();
			
			$keys[] = 'guid';
			$values[] = "'" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'";
			
			$keys[] = 'doi';
			$values[] = "'" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'";
	
			$keys[] = 'title';
			$values[] = "'" . addcslashes($params['rft.atitle'][0], "'") . "'";
			
			$keys[] = 'journal';
			$values[] = "'" . addcslashes($params['rft.jtitle'][0], "'") . "'";
	
			$keys[] = 'issn';
			$values[] = "'" . addcslashes($issn, "'") . "'";
	
			
			switch ($issn)
			{
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
	
	
			if (isset($params['rft.spage']))
			{
				$keys[] = 'spage';
				$values[] = "'" . addcslashes($params['rft.spage'][0], "'") . "'";
			}
	
			if (isset($params['rft.epage']))
			{
				$keys[] = 'epage';
				$values[] = "'" . addcslashes($params['rft.epage'][0], "'") . "'";
			}
			
			if (!in_array('epage', $keys))
			{
				$pages = array();
				
				// JSTOR
				//$pages = get_pages_from_page(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]));
				
				// CrossRef
				//$pages = get_pages_from_page_meta(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]));
				
				//print_r($pages);
				
				if (count($pages) > 0)
				{
					if (!in_array('spage', $keys))
					{
						$keys[]   = 'spage';
						$values[] = $pages[0];				
					}				
				
					if (count($pages) == 2)
					{
						$keys[]   = 'epage';
						$values[] = $pages[1];				
					}
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
					$authors[] = $au;
				}
				$values[] = "'" . addcslashes(join(";", $authors), "'") . "'";
			}
					
			
			$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
			
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
			
		}
	}
	
}

?>