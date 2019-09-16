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

$issn = '0312-9764'; // Telopea 

$issn = '0373-2967'; // Candollea

$issn = '2175-7860'; // Rodriguésia

$issn = '1179-3155'; // Phytotaxa

$issn = '0006-808X'; 

$issn = '1617-416X'; // Mycological Progress
$issn = '0031-9465'; // Phytopathologia Mediterranea -- fail, not CrossRef

$issn = '1808-2688';

$issn = '0366-5232'; // Caldasia (need to fix parsing meta)

$issn = '0037-8941';

$issn = '0024-9637'; // Madroño
$issn = '0006-5196'; // Blumea

$issn = '0363-6445'; // Systematic Botany
$issn = '1809-5348'; // Neodiversity
$issn = '0006-8241'; // Bothalia

$issn = '0040-0262'; // Taxon (need to merge with JSTOR)

$issn = '0373-2967';

$issn = '1355-4905';

// Harvard Papers in Botany
$issn = '1043-4534';

// Willdenowia
$issn = '0511-9618';

$issn = '0305-7364';

// webbia
$issn ='0083-7792';

$issn = '0003-3847';

$issn = '0010-0730'; // Collectanea Botanica OJS vol 33 onwards articles have XML

$issn = '0067-1924';

$issn = '0312-9764';

$issn = '0372-1426';

$issn = '1179-3155'; // Phytotaxa, redo to get pagination

$issn = '0030-6525'; // ostrich

$issn = '0374-6607';

$issn = '0258-1485'; // Notizbl. Bot. Gart. Berlin-Dahlem

$issn = '0016-5301'; // Gayana Botanica
$issn = '0717-6643'; // Gayana Botanica eISSN	

$issn = '1280-8571'; // Adansonia series 3

// Phytokeys
$issn = '1314-2011';

$issn = '1179-3155'; // Phytotaxa
$issn = '1560-7259'; // Turczaninowia
$issn = '2032-3913'; // Pl. Ecol. Evol.  to do
$issn = '1253-8078';

$issn = '1055-3177'; // Novon

$issn = '0075-5974'; // Kew Bulletin

$issn = '0254-6299';
//$issn = '2156-0382';

$issn='1070-0048';

$issn = '2236-1472';
$issn = '0301-2123';

$issn = '0003-455X';

$issn = '1999-3110';

$issn = '0211-1322';

$issn = '0008-8692';
$issn = '0006-8101';
//----------------------------------------------------------------------------------------

// BHL DOIs
$issn = '0370-047X'; // Proc Linn Soc NSW
$issn = '0035-418X'; // Revue Suisse de Zoologie

//----------------------------------------------------------------------------------------

$issn = '0082-0598'; // Sydowia mEDRA, not in search.crossref.org

//$issn = '0529-1526'; // Acta Phytotaxonomica Sinica DOIs resolve but detsination down :(
$issn = '1674-4918';
$issn = '0014-8962';

$issn = '1409-3871';

$issn = '2084-4352';
$issn = '1808-2688';

$issn = '0302-2439';

$issn = '0029-8948';
$issn = '1179-3155';

$issn = '0001-5709';
$issn = '2300-1887';

$issn = '0015-5551';
$issn = '0960-4286';

$issn = '0026-6493';
$issn = '0006-8241';
$issn = '0007-196X';
$issn = '0075-5974';
$issn = '0044-5983';

$issn = '0007-2745';
//$issn = '0029-5035';

$issn = '2327-2929';

$issn = '0097-3157';

$issn = '1179-3155';

$issn = '2084-4352';
$issn = '1897-2810';

$issn = '0181-1797';
$issn = '0511-9618';

$issn = '1179-3155'; // Phytotaxa
$issn = '1314-2011'; // Phytokeys
$issn = '0960-4286'; // Edinburgh
$issn = '0373-2967'; // Candollea
$issn = '0075-5974'; // Kew Bull
$issn = '0131-1379'; // Arctoa
$issn = '1018-4171';
$issn = '2118-9773'; // European Journal of Taxonomy

$issn = '0960-4286'; // Edinburgh
//$issn = '0373-2967'; // Candollea
//$issn = '1043-4534';

//$issn = '1055-3177'; // Novon
//$issn = '1225-8318';

$issn = '0511-9618'; // Willdenowia

$issn = '0342-7536';

$issn = '2156-0382';
$issn = '0035-919X';
$issn = '0028-7199';

$issn = '0033-2615';
$issn = '0370-3681';
$issn = '0370-5412';
$issn = '0040-0262';
$issn = '0008-4026'; // Can J Bot
$issn = '0378-2697';

$issn = '0006-324X';
$issn = '0254-6299';

$issn = '1561-0837';

$issn = '0374-7859';

$issn = '0013-8746';
$issn = '1869-0963';

$issn = '0002-8320';

$issn = '0374-7859';

$issn = '0323-6145';

$issn = '0021-8375';

$issn = '0065-1710';

$issn = '0366-1326'; // Bulletin mensuel de la Société linnéenne de Lyon
//$issn = '0072-9027'; // Gulf research reports
//$issn = '0026-6493'; 


$issn = '0161-8202';

$issn = '0012-0073';
$issn = '0083-7792';
$issn = '0953-7562';

$issn = '1447-2554';
$issn = '1409-3871';

$issn = '0024-2829'; // Lichenologist
$issn = '0181-1584'; // Cryptogamie Mycologie
$issn = '0093-4666'; // Mycotaxon
$issn = '0031-5850'; // Persoonia
$issn = '0044-5967'; // Acta Amazonica

//$issn = '1559-4491';

$issn = '0003-4541';

$issn = '2077-7019'; // Mycosphere
$issn = '2353-074X'; // Acta Mycologica
$issn = '1018-4171';
$issn = '2327-2929';

$issn = '8756-971X';
$issn = '1617-416X';
$issn = '0740-2783';

$issn = '0341-0145';

$issn = '0312-3162';

$issn = '1437-4323';

$issn = '1560-2745';
$issn = '0002-9122';

$issn = '0030-8870';
$issn = '0006-8055';

$issn = '0101-3580';
$issn = '0066-7870';


$issn = '0378-2697'; // '0029-8948'; // 1858-1973 Österreichische Botanische Zeitschrift

$issn = '0312-9764';

$issn = '1464-3766';

//$issn = '2200-4025';

$issn = '2327-2929';
$issn = '0960-4286';
$issn = '0960-4286';
$issn = '0181-1584';
$issn = '0022-8567';
$issn = '1217-8837';

$issn = '1851-8044';
$issn = '0010-065X';
$issn = '0311-9548';
$issn = '1447-2546';
$issn = '0814-1827';
$issn = '0083-5986';
$issn = '0022-3360';
$issn = '0187-7151';

$issn = '1447-2546';
$issn = '0814-1827';
$issn = '0083-5986';

$issn ='0373-9465';

$issn ='0749-8004';
$issn ='0187-7151';

$issn ='0265-086X';

$issn = '0031-1820';

$issn = '1405-3322';

$issn = '0044-5967';

$issn = '1280-9659';

$issn = '0374-5481';

$issn = '1833-0290';

$issn = '1534-6188';

$issn = '0003-0082';
$issn = '0037-2870';
$issn = '0003-0090';
$issn = '0067-1975';
$issn = '0007-4977';
$issn = '0081-0282';
$issn = '0079-8835';
$issn = '1280-9551';
$issn = '0018-0831';

$issn = '0374-7859';
$issn = '0301-2123';

$issn = '0008-7475';
$issn = '1097-993X';

$issn = '0187-7151';

$issn = '0006-9698';

$issn = '1814-3326';

$issn ='0096-6134';
$issn ='0105-0761';
$issn ='1280-8571';

$issn ='1179-3155'; // phytotaxa

$issn ='0374-6607';


$issn ='0370-3681';
$issn ='0370-5412';
$issn ='1314-2011';
$issn ='0370-1646';
$issn ='0093-4666';

$issn = '0040-0262'; // taxon in Wiley 2000-onwards

$issn = '0003-9284';
$issn = '1869-0963';
$issn = '0033-2615';

$issn ='0008-347X';

$issn = '1514-5158';

$issn = '0037-928X';

$issn = '1323-5818';


//for ($y = 1890; $y <= 2018; $y++)
//for ($y = 2015; $y <= 2015; $y++)
for ($y = 2002; $y <= 2004; $y++)
{
	$count = 0;
	$page = '';
	$done = false;
	while (!$done)
	{
		$url = 'http://search.crossref.org/dois?q=' . $issn . '&header=true' . '&page=' . $page . '&year=' . $y;
	
	//	$url = 'http://search.crossref.org/dois?q=Journal+of+Ornithology&year=1922&publication=Journal+of+Ornithology' . '&header=true' . '&page=' . $page;
	
	
		//$url = 'http://search.crossref.org/dois?q=' . $issn . '&header=true' . '&page=' . $page . '&year=2011&volume=15';
	
		//$url = 'http://search.crossref.org/dois?q=' . urlencode('Bulletin de la Société entomologique de France') . '&header=true' . '&page=' . $page . '&year=' . $y; 
	
	
		//$url = 'http://search.crossref.org/dois?q=Telopea&year=1922&publication=Telopea' . '&header=true' . '&page=' . $page . '&year=' . $y; 
	
	
		//$url = 'http://search.crossref.org/dois?q=' . '10.5642/aliso.20123001.07' . '&header=true' . '&page=' . $page;
		//$url = 'http://search.crossref.org/dois?q=' . urlencode('10.3100/1043-4534(2005)10[53:bpsuaa]2.0.co;2') . '&header=true' . '&page=' . $page;
	
	
		//$url = 'http://search.crossref.org/dois?q=' . '10.2331/suisan.24.445' . '&header=true' . '&page=' . $page;
		//$url = 'http://search.crossref.org/dois?q=' . '10.11646/phytotaxa.75.1.5' . '&header=true' . '&page=' . $page;
	
		//$url = 'http://search.crossref.org/dois?q=' . $issn . '&year=2012' . '&header=true' . '&page=' . $page;
		//$url = 'http://search.crossref.org/dois?q=' . urlencode('A NEW VARIETY OF POTENTILLA GRACILIS (ROSACEAE) AND RE-EVALUATION OF THE POTENTILLA DRUMMONDII COMPLEX') . '&header=true' . '&page=' . $page;

		//echo $url . "\n";
		echo "-- $url\n";

		$json = get($url);
	
		//echo $json;

		$obj = json_decode($json);
	
		//print_r($obj);
		//exit();

	
		$count += $obj->itemsPerPage;
		$page++;
	
		$done = ($count >= $obj->totalResults);
		//$done = ($count >= 100); 
	
	
		$fetch_count = 0;
	
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
		
			if (1)
			{
				// Update
				
				if (($params['rft.volume'][0] != '') && ($params['rft.spage'][0] != ''))
				{
				
					$sql = "UPDATE publications SET guid='" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'"
						. ", doi='" . addcslashes(preg_replace('/info:doi\/(http:\/\/dx.doi.org\/)?/', '', $params['rft_id'][0]), "'") . "'"
						. " WHERE issn='$issn' AND volume='" . addcslashes($params['rft.volume'][0], "'") . "' AND spage='" . addcslashes($params['rft.spage'][0], "'") . "'"
						. ";";
					echo $sql . "\n";
				
				}
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

				/*
				$keys[] = 'volume';
				$values[] = "'" . addcslashes($params['rft.volume'][0], "'") . "'";
				*/
				
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
					
					case '1323-5818':
					
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
	
	}
}

?>