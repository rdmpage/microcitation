<?php

// bibliographic reference

require_once('sici.php');

//--------------------------------------------------------------------------------------------------
/**
 * @brief Get identifiers for a reference
 * *
 * @param reference Reference object
 *
 * @return Array of key-value pairs where the key is the identifier type 
 * and the value is the identifier
 */
function reference_identifiers($reference)
{
	$identifiers = array();
	
	//print_r($reference->identifier);
	
	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			$identifiers[$identifier->type] = $identifier->id;
		}
	}
	
	return $identifiers;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a citation string for indexing
 * *
 * @param reference Reference object to be encoded
 *
 * @return OpenURL
 */
function reference_to_citation_string($reference)
{
	$citation = '';
	

	//echo "citation=$citation\n";
	if (isset($reference->author))
	{
		$authors = array();
		foreach ($reference->author as $author)
		{
			if (isset($author->forename) && isset($author->lastname))
			{
				$authors[] = $author->lastname . ' ' . $author->forename;
			}
			else
			{
				$authors[] = $author->name;
			}
		
		}
		$citation .= join(', ', $authors);
		$citation .= ' ';
	}
	//echo "citation=$citation\n";
	
	
	if (isset($reference->year))
	{
		$citation .= '(' . $reference->year . ')';
	}
	
	if (isset($reference->title))
	{
		$citation .= ' ' . $reference->title . '.';
	}
	//echo "citation=$citation\n";
	
	
	if (isset($reference->journal))
	{
		$citation .= ' ' . $reference->journal->name;
		if (isset($reference->journal->volume))
		{
			$citation .= ', ' . $reference->journal->volume;
		}
		if (isset($reference->journal->issue))
		{
			$citation .= '(' . $reference->journal->issue . ')';
		}		
		if (isset($reference->journal->pages))
		{
			$citation .= ': ' . str_replace('--', '-', $reference->journal->pages);
		}
	}
	else
	{
		// not a journal...
		$citation .= ': ' . $reference->pages;		
	}
	
	//echo "citation=$citation\n";

	return $citation;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Convert BibJSON object to citeproc-js object
 *
 * @param reference Reference object to be converted
 * @param id Local id for citeproc-js object 
 *
 * @return citeproc-js object
 */
function reference_to_citeprocjs($reference, $id = 'ITEM-1')
{
	$citeproc_obj = array();
	$citeproc_obj['id'] = $id;

	$citeproc_obj['unstructured'] = reference_to_citation_string($reference);
	
	$citeproc_obj['title'] = $reference->title;
	
	if (isset($reference->abstract))
	{
		$citeproc_obj['abstract'] = $reference->abstract;
	}
	
	// multi
	if (isset($reference->multi))
	{
		$citeproc_obj['multi'] = $reference->multi;
	}
	
	
	if (isset($reference->journal))
	{	
		$citeproc_obj['type'] = 'article-journal';
	}
	
	if (isset($reference->date))
	{
		$citeproc_obj['issued'] = new stdclass;
		$citeproc_obj['issued']->{'date-parts'} = array();
		$citeproc_obj['issued']->{'date-parts'}[0] = array();
		$parts = explode('-', $reference->date);
		
		$citeproc_obj['issued']->{'date-parts'}[0][] = (Integer)$parts[0];

		if ($parts[1] != '00')
		{		
			$citeproc_obj['issued']->{'date-parts'}[0][] = (Integer)$parts[1];
		}

		if ($parts[2] != '00')
		{		
			$citeproc_obj['issued']->{'date-parts'}[0][] = (Integer)$parts[2];
		}
	
	}
	else
	{
		if (isset($reference->year))
		{
			$citeproc_obj['issued'] = new stdclass;
			$citeproc_obj['issued']->{'date-parts'} = array();
			$citeproc_obj['issued']->{'date-parts'}[] = array((Integer)$reference->year);
		}
	}
	
	if (isset($reference->publisher))
	{
		$citeproc_obj['publisher'] = $reference->publisher;
	}
	if (isset($reference->publoc))
	{
		$citeproc_obj['publisher-place'] = $reference->publoc;
	}
	
	
	if (isset($reference->author))
	{
		$citeproc_obj['author'] = array();

		foreach ($reference->author as $author)
		{
			$a = array();
			if (isset($author->firstname))
			{
				$a['given'] = $author->firstname;
				$a['family'] = $author->lastname;
			}
			else
			{
				$a['literal'] = $author->name;
				//$a['family'] = $author->name;
			}

			if (isset($author->multi))
			{
				$a['multi']= $author->multi;
			}
			
			$citeproc_obj['author'][] = $a;
		}
		
	}
	
	if (isset($reference->journal))
	{
		$citeproc_obj['container-title'] = $reference->journal->name;
		
		if (isset($reference->journal->series))
		{
			$citeproc_obj['collection-title'] = $reference->journal->series;
		}
		
		if (isset($reference->journal->volume))
		{
			$citeproc_obj['volume'] = $reference->journal->volume;
		}
		
		if (isset($reference->journal->issue))
		{
			$citeproc_obj['issue'] = $reference->journal->issue;
		}
		if (isset($reference->journal->pages))
		{	
			$citeproc_obj['page'] = str_replace('--', '-', $reference->journal->pages);
			
			if (preg_match('/^[a-z]?\d+$/', $reference->journal->pages, $m))
			{
				$citeproc_obj['page-first'] = $reference->journal->pages;
			}

			if (preg_match('/(?<spage>\d+)--(?<epage>\d+)/', $reference->journal->pages, $m))
			{
				$citeproc_obj['page-first'] = $m['spage'];
			}
			
		}
		
		
		
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$citeproc_obj['ISSN'][] = $identifier->id;
						break;
					default:
						break;
				}
			}
		}
		
	// multi
	if (isset($reference->journal->multi))
	{
		if (isset($citeproc_obj['multi']))
		{
			$citeproc_obj['multi']->_key->{'container-title'} = $reference->journal->multi->_key->name;
		}
		else
		{
			$citeproc_obj['multi']= $reference->journal->multi;
		}
	}
		
		
	}
	
	if (isset($reference->identifier))
	{
		$citeproc_obj['alternative-id'] = array(); 
		foreach ($reference->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'bhlpart':
					$citeproc_obj['BHLPART'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'BHLPART:' . $identifier->id;
					break;			
			
				case 'cinii':
					$citeproc_obj['CINII'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'CINII:' . $identifier->id;
					break;

				case 'cnki':
					$citeproc_obj['CNKI'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'CNKI:' . $identifier->id;
					break;
			
				case 'doi':
					$citeproc_obj['DOI'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'DOI:' . $identifier->id;
					break;

				case 'handle':
					$citeproc_obj['HANDLE'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = $identifier->id;
					break;
					
				case 'internetarchive':
					$citeproc_obj['ARCHIVE'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = $identifier->id;
										
					$citeproc_obj['thumbnailUrl'] = 'https://archive.org/services/img/' . $identifier->id;
					break;
					
				case 'isbn':
				case 'isbn10':
				case 'isbn13':
					$citeproc_obj['ISBN'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = $identifier->id;
					break;

				case 'jstor':
					$citeproc_obj['JSTOR'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'JSTOR:' . $identifier->id;
					break;

				case 'pmid':
					$citeproc_obj['PMID'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'PMID:' . $identifier->id;
					break;

				case 'pmc':
					$citeproc_obj['PMC'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'PMC:' . $identifier->id;
					break;
					
				case 'pii':
					$citeproc_obj['alternative-id'][] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'PII:' . $identifier->id;
					break;

				case 'oai':
					$citeproc_obj['alternative-id'][] = $identifier->id;
					break;
					
				case 'sici':
					$citeproc_obj['SICI'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'SICI:' . $identifier->id;
					break;
					
				case 'wayback':
					$citeproc_obj['WAYBACK'] = $identifier->id;
					break;					
										
				case 'zoobank':
					$citeproc_obj['ZOOBANK'] = $identifier->id;
					break;

				case 'zenodo':
					$citeproc_obj['ZENODO'] = $identifier->id;
					break;
					
				default:
					break;
			}
		}
	}
	
	if (isset($reference->link))
	{
		$links = array();
	
		$citeproc_obj['link'] = array();
		
		foreach ($reference->link as $link)
		{
			if (!in_array($link->url, $links))
			{
				$links[] = $link->url;
				switch ($link->anchor)
				{
					case 'LINK':
						$citeproc_obj['URL'] = $link->url;
					
					
						$citeproc_obj['alternative-id'][] = $link->url;
						break;

					case 'PDF':
						// model after crossref
						/*
						"link": [{
				"URL": "https:\/\/zookeys.pensoft.net\/lib\/ajax_srv\/article_elements_srv.php?action=download_pdf&item_id=11711",
				"content-type": "application\/pdf",
				"content-version": "vor",
				"intended-application": "text-mining"
			}, {
				"URL": "https:\/\/zookeys.pensoft.net\/lib\/ajax_srv\/article_elements_srv.php?action=download_xml&item_id=11711",
				"content-type": "application\/xml",
				"content-version": "vor",
				"intended-application": "text-mining"
			}],*/
					
					
						$pdf = new stdclass;
						$pdf->URL = $link->url;
						$pdf->{'content-type'} = "application/pdf";
					
						$citeproc_obj['link'][] = $pdf;
						
						$citeproc_obj['alternative-id'][] = $link->url;
						break;
					
					case 'XML':
					
						// model after crossref
						/*
						"link": [{
				"URL": "https:\/\/zookeys.pensoft.net\/lib\/ajax_srv\/article_elements_srv.php?action=download_pdf&item_id=11711",
				"content-type": "application\/pdf",
				"content-version": "vor",
				"intended-application": "text-mining"
			}, {
				"URL": "https:\/\/zookeys.pensoft.net\/lib\/ajax_srv\/article_elements_srv.php?action=download_xml&item_id=11711",
				"content-type": "application\/xml",
				"content-version": "vor",
				"intended-application": "text-mining"
			}],*/
						$xml = new stdclass;
						$xml->URL = $link->url;
						$xml->{'content-type'} = "application/xml";
					
						$citeproc_obj['link'][] = $xml;
						break;
					
					
					default:
						break;
				}
			}
		}
		if (count($citeproc_obj['link']) == 0)
		{
			unset($citeproc_obj['link']);
		}
	}
	
	if (isset($citeproc_obj['alternative-id']))
	{
		$citeproc_obj['alternative-id'] = array_unique($citeproc_obj['alternative-id']);
	}


	if (count($citeproc_obj['alternative-id']) == 0)
	{
		unset($citeproc_obj['alternative-id']);
	}
	
	
	if (isset($reference->thumbnail))
	{	
		$citeproc_obj['thumbnail'] = $reference->thumbnail;
	}

	if (isset($reference->license))
	{	
		$license = new stdclass;
		$license->URL = $reference->license;
	
		$citeproc_obj['license'][] = $license;
	}
	
	
	
	return $citeproc_obj;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a COinS (ContextObjects in Spans) for a reference
 *
 * COinS encodes an OpenURL in a <span> tag. See http://ocoins.info/.
 *
 * @param reference Reference object to be encoded
 *
 * @return HTML <span> tag containing a COinS
 */
function reference_to_coins($reference)
{
	global $config;
	
	$coins = '<span class="Z3988" title="' 
		. reference_to_openurl($reference) 
//		. '&amp;webhook=' . urlencode($config['web_server'] . $config['web_root'] . 'webhook.php')
		. '"></span>';
	return $coins;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create an OpenURL for a reference
 * *
 * @param reference Reference object to be encoded
 *
 * @return OpenURL
 */
function reference_to_openurl($reference)
{
	$openurl = '';
	$openurl .= 'ctx_ver=Z39.88-2004';

	// Local publication identifier
	$openurl .= '&amp;rfe_id=' . urlencode($reference->id);
	
	//print_r($reference);
	
	if (isset($reference->journal) || $reference->type == 'article')
	{
		$openurl .= '&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal';
		$openurl .= '&amp;genre=article';
		$openurl .= '&amp;rft.atitle=' . urlencode($reference->title);
		$openurl .= '&amp;rft.jtitle=' . urlencode($reference->journal->name);
	
		if (isset($reference->journal->series))
		{
			$openurl .= '&amp;rft.series=' . urlencode($reference->journal->series);
		}
		
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$openurl .= '&amp;rft.issn=' . $identifier->id;
						break;
						
					default:
						break;
				}
			}
		}
		
		if (isset($reference->journal->volume))
		{
			$openurl .= '&amp;rft.volume=' . $reference->journal->volume;
		}
		if (isset($reference->journal->issue))
		{
			$openurl .= '&amp;rft.issue=' . $reference->journal->issue;
		}		
		if (isset($reference->journal->pages))
		{
			if (preg_match('/^(?<spage>.*)--(?<epage>.*)/', $reference->journal->pages, $m))
			{
				$openurl .= '&amp;rft.spage=' . $m['spage'];
				$openurl .= '&amp;rft.epage=' . $m['epage'];
			}
			else
			{
				$openurl .= '&amp;rft.pages=' . $reference->journal->pages;
			}
		}
	}
	else
	{
		if ($reference->type == 'book')
		{
			$openurl .= '&amp;rft.btitle=' . urlencode($reference->title);
		}
		else
		{
			$openurl .= '&amp;rft.title=' . urlencode($reference->title);		
		}
		
		$openurl .= '&amp;rft.pages=' . $reference->journal->pages;
		
	}
	
	// generic stuff
	
	// authors
	if (count($reference->author) > 0)
	{
		$openurl .= '&amp;rft.aulast=' . urlencode($reference->author[0]->lastname);
		$openurl .= '&amp;rft.aufirst=' . urlencode($reference->author[0]->firstname);
	}
	foreach ($reference->author as $author)
	{
		$openurl .= '&amp;rft.au=' . urlencode($author->name);
	}	
	
	// date
	$openurl .= '&amp;rft.date=' . $reference->year;
	
	// identifiers
	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'doi':
					$openurl .= '&amp;rft_id=info:doi/' . urlencode($identifier->id);
					break;
					
				case 'handle':
					$openurl .= '&amp;rft_id=info:hdl/' . urlencode($identifier->id);
					break;

				case 'pmid':
					$openurl .= '&amp;rft_id=info:pmid/' . urlencode($identifier->id);
					break;
					
				case 'zoobank':
					$openurl .= '&amp;rft_id=http://zoobank.org/' . urlencode($identifier->id);
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
				$openurl .= '&amp;rft_id='. urlencode($link->url);
			}
		}
	}
	
	
	return $openurl;
}

//--------------------------------------------------------------------------------------------------
function reference_to_ris($reference)
{
	$ris = '';
	
	if (isset($reference->journal))
	{
		$ris .= "TY  - JOUR\n";
	}
	else
	{
		$ris .= "TY  - GEN\n";
	}

	
	if (isset($reference->id))
	{
		$ris .=  "ID  - " . $reference->id . "\n";
	}
	
	if (isset($reference->author))
	{
		foreach ($reference->author as $a)
		{
			if (is_object($a))
			{
				$ris .= "AU  - " . $a->name . "\n";
			}
		}
	}
	
	if (isset($reference->title))
	{
		$ris .=  "TI  - " . strip_tags($reference->title) . "\n";
	}
	
	if (isset($reference->journal)) 
	{
		$ris .=  "JF  - " . $reference->journal->name . "\n";
		if (isset($reference->journal->volume))
		{
			$ris .=  "VL  - " . $reference->journal->volume . "\n";
		}
		if (isset($reference->journal->issue))
		{
			$ris .=  "IS  - " . $reference->journal->issue . "\n";
		}
		
		foreach ($reference->journal->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'issn':
					$ris .=  "SN  - " . $identifier->id . "\n";
					break;
					
				default:
					break;
			}
		}
		
		if (isset($reference->journal->pages))
		{
			if (preg_match('/^(?<spage>.*)--(?<epage>.*)/', $reference->journal->pages, $m))
			{
				$ris .=  "SP  - " . $m['spage'] . "\n";
				$ris .=  "EP  - " . $m['epage'] . "\n";
			}
			else
			{
				$ris .=  "SP  - " . $reference->journal->pages . "\n";
			}
		}
		
		
	}
	
	if (isset($reference->year))
	{
		$ris .=  "Y1  - " . $reference->year . "\n";
	}
	
	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'doi':
					$ris .=  "DO  - " . $identifier->id . "\n";
					break;

				case 'handle':
					$ris .=  "UR  - http://hdl.handle.net/" . $identifier->id . "\n";
					break;
					
				default:
					break;
			}
		}
	}	
	
	$ris .=  "ER  - \n";
	$ris .=  "\n";
	
	return $ris;
}

//--------------------------------------------------------------------------------------------------
// Generate a SICI for a reference
function reference_to_sici($reference)
{
	$sici_string = '';
	
	$sici = array();
	
	$ok = true;
	
	//------------------------------------------------------------------------------------
	if (!isset($reference->journal))
	{
		$ok = false;
	}
	else
	{
		$journal_id = $subject_id . '#container';
		
		$issns = array();
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$issns[] = $identifier->id;
						break;
						
					default:
						break;
				}
			}
		}
		
		if (count($issns) == 0)
		{
			$ok = false;
		}
		else
		{
			$journal_id = 'http://worldcat.org/issn/' . $issns[0];
			
			$sici[] = $issns[0];

			if (isset($reference->year))
			{
				$sici[] = '(' . $reference->year . ')';
			}
		}
		
		if ($ok && isset($reference->journal->volume))
		{
			$sici[] = $reference->journal->volume;
		}
		else
		{
			$ok = false;
		}
		
		/*
		if (isset($reference->journal->issue))
		{
		}
		*/
		
		if ($ok && isset($reference->journal->pages))
		{
			if (preg_match('/(?<spage>[a-z]?\d+)/', $reference->journal->pages, $m))
			{
				$sici[] = '<';
				$sici[] =  $m['spage'];
			}
			else
			{
				$ok = false;
			}
			
			if ($ok && isset($reference->title))
			{
				$title = $reference->title;
				
				//echo $title . "\n";
				
				$title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
				
				//echo $title . "\n";
							
				// Convert accented characters
				$title = strtr(utf8_decode($title), 
					utf8_decode("ÀÁÂÃÄÅàáâãäåĀāĂăĄąÇçĆćĈĉĊċČčÐðĎďĐđÈÉÊËèéêëĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħÌÍÎÏìíîïĨĩĪīĬĭĮįİıĴĵĶķĸĹĺĻļĽľĿŀŁłÑñŃńŅņŇňŉŊŋÒÓÔÕÖØòóôõöøŌōŎŏŐőŔŕŖŗŘřŚśŜŝŞşŠšſŢţŤťŦŧÙÚÛÜùúûüŨũŪūŬŭŮůŰűŲųŴŵÝýÿŶŷŸŹźŻżŽž"),
					"aaaaaaaaaaaaaaaaaaccccccccccddddddeeeeeeeeeeeeeeeeeegggggggghhhhiiiiiiiiiiiiiiiiiijjkkkllllllllllnnnnnnnnnnnoooooooooooooooooorrrrrrsssssssssttttttuuuuuuuuuuuuuuuuuuuuwwyyyyyyzzzzzz");
				
				$title = strtoupper($title);
				
				//echo $title . "\n";
				
  				$title = preg_replace('/[^A-Z\s]/', '', $title); 
  				
  				//echo $title . "\n";
  				
  				//echo "\n\n";
				
				$words = explode(' ', $title);
				$num_words = min(6, count($words));
				$initials = '';
				for ($i = 0; $i < $num_words; $i++)
				{
					$initials .= $words[$i][0];
				}
				
				$sici[] = ':' . $initials;
				
			}
			
			$sici[] = '>';
		}
		
	}
	
	if ($ok)
	{
	
		$sici_string = join('', $sici);
	
		$sici_string .= '2.0.CO;2';
		$sici_string .= '-' . checksum($sici_string);
	}	
	
	return $sici_string;
				
}


//--------------------------------------------------------------------------------------------------
function reference_to_rdf($reference)
{
	$triples = array();
	
	$sameAs = array();
	
	$guid = $reference->guid;
	
	// DOI
	if (preg_match('/^10\./', $guid))
	{
		$guid = 'https://doi.org/' . strtolower($guid);
		
		$sameAs[] = $guid;
	}

	// jstor
	if (preg_match('/http:\/\/www.jstor.org/', $guid))
	{
		$guid = str_replace('http', 'https', $guid);
		
		$sameAs[] = $guid;
	}
	
	// handle
	if (preg_match('/^\d+\/[a-z0-9]+$/', $guid))
	{
		$guid = 'https://hdl.handle.net/' . strtolower($guid);
		
		$sameAs[] = $guid;
	}
	
	
	$subject_id = $guid; // fix this

	$s = '<' . $subject_id . '>';
	
	$type = 'ScholarlyArticle';
	
	if ($reference->type == 'book')
	{
		$type = 'Book';
	}
	
	$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/' . $type . '> .';
	
	
	$have_title = false;
	
	if (isset($reference->multi))
	{
		if (isset($reference->multi->{'_key'}->title))
		{
			$have_title = true;
			
			foreach ($reference->multi->{'_key'}->title as $language => $value)
			{
				$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
			}
		}	
	}
	
	if (!$have_title)
	{
		if (isset($reference->title))
		{
			$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($reference->title, '"') . '" .';		
		}
	}
	
	//-----------------------------------------
	// Abstract
	$have_abstract = false;
	
	if (isset($reference->multi))
	{
		if (isset($reference->multi->{'_key'}->abstract))
		{
			$have_abstract = true;
			
			foreach ($reference->multi->{'_key'}->abstract as $language => $value)
			{
				$triples[] = $s . ' <http://schema.org/description> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
			}
		}	
	}
	
	if (!$have_abstract)
	{
		if (isset($reference->abstract))
		{
			$triples[] = $s . ' <http://schema.org/description> ' . '"' . addcslashes($reference->abstract, '"') . '" .';		
		}
	}

	//-----------------------------------------
	// Authors
	
	if (isset($reference->author))
	{
	
		$n = count($reference->author);
		for ($i = 0; $i < $n; $i++)
		{
			$index = $i + 1;
		
			// Author
			$author_id = '<' . $subject_id . '#creator/' . $index . '>';
			
			if (isset($reference->author[$i]->multi))
			{
				if (isset($reference->author[$i]->multi->{'_key'}->literal))
				{
					foreach ($reference->author[$i]->multi->{'_key'}->literal as $language => $value)
					{
						$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
					}
				}	
			}
			else
			{
				$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($reference->author[$i]->name, '"') . '" .';					
				
				if (isset($reference->author[$i]->firstname))
				{
					$triples[] = $author_id . ' <http://schema.org/givenName> ' . '"' . addcslashes($reference->author[$i]->firstname, '"') . '" .';					
				}
				if (isset($reference->author[$i]->lastname))
				{
					$triples[] = $author_id . ' <http://schema.org/familyName> ' . '"' . addcslashes($reference->author[$i]->lastname, '"') . '" .';					
				}
			}
			
			// assume is a person, need to handle cases where this is not true
			$triples[] = $author_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/Person>' . ' .';			
		
			$use_role = true;
							
			if ($use_role)
			{
				// Role to hold author position
				$role_id = '<' . $subject_id . '#role/' . $index . '>';
				
				$triples[] = $role_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/Role>' . ' .';			
				$triples[] = $role_id . ' <http://schema.org/roleName> "' . $index . '" .';			
			
				$triples[] = $s . ' <http://schema.org/creator> ' .  $role_id . ' .';
				$triples[] = $role_id . ' <http://schema.org/creator> ' .  $author_id . ' .';
			}
			else
			{
				// Author is creator
				$triples[] = $s . ' <http://schema.org/creator> ' .  $author_id . ' .';						
			}
			
		}
	}	


	//------------------------------------------------------------------------------------
	if (isset($reference->journal))
	{
		$journal_id = $subject_id . '#container';
		
		$sici = array();
		
		
		$issns = array();
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$issns[] = $identifier->id;
						break;
						
					default:
						break;
				}
			}
		}
		
		if (count($issns) > 0)
		{
			$journal_id = 'http://worldcat.org/issn/' . $issns[0];
			
			$sici[] = $issns[0];

			if (isset($reference->year))
			{
				$sici[] = '(' . $reference->year . ')';
			}
		}
				
		$triples[] = $s . ' <http://schema.org/isPartOf> ' . '<' . $journal_id . '> .';
		
		foreach ($issns as $issn)
		{
			$triples[] = '<' . $journal_id . '> <http://schema.org/issn> ' . '"' . $issn. '"' . ' .';		
		}
		
		switch ($reference->type)
		{
			case 'article':
				$triples[] = '<' . $journal_id . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/Periodical> .';	
				break;
		
			default:
				break;
		}
		
				
		if (isset($reference->journal->multi))
		{
			if (isset($reference->journal->multi->{'_key'}->name))
			{
				foreach ($reference->journal->multi->{'_key'}->name as $language => $value)
				{
					$triples[] = '<' . $journal_id . '> <http://schema.org/name> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
				}
			}		
		}
		else
		{
			if (isset($reference->journal->name))
			{
				$triples[] = '<' . $journal_id . '> <http://schema.org/name> ' . '"' . addcslashes($reference->journal->name, '"') . '"' . ' .';		
			}
		}
	
		if (isset($reference->journal->volume))
		{
			$triples[] = $s . ' <http://schema.org/volumeNumber> ' . '"' . addcslashes($reference->journal->volume, '"') . '" .';
			
			$sici[] = $reference->journal->volume;
		}
		if (isset($reference->journal->issue))
		{
			$triples[] = $s . ' <http://schema.org/issueNumber> ' . '"' . addcslashes($reference->journal->issue, '"') . '" .';
		}
		if (isset($reference->journal->pages))
		{
			$triples[] = $s . ' <http://schema.org/pagination> ' . '"' . addcslashes(str_replace('--', '-', $reference->journal->pages), '"') . '" .';

			if (preg_match('/(?<spage>[a-z]?\d+)/', $reference->journal->pages, $m))
			{
				$sici[] = '<' . $m['spage'] . '>';
			}
		}
		
		
		// sici to help reference linking
		if (count($sici) == 4)
		{
			$identifier_id = '<' . $subject_id . '#sici' . '>';

			$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
			$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';
			$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"sici"' . '.';
			$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . addcslashes(join('', $sici), '"') . '"' . '.';		
		}
		
		
		
	}
	
	//------------------------------------------------------------------------------------
	if (isset($reference->link))
	{
		foreach ($reference->link as $link)
		{
			switch ($link->anchor)
			{
				case 'LINK':
					$triples[] = $s . ' <http://schema.org/url> ' . '"' . $link->url . '" .';				
					$sameAs[] = $link->url;
					break;

				// eventually handle this difefrently, cf Ozymandias
				case 'PDF':
					$sameAs[] = $link->url;
					break;
			
				default:
					break;
			}
		}
	}
	
	//------------------------------------------------------------------------------------
	// Identifiers
	
	
	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			$identifier_id = '';
		
			switch ($identifier->type)
			{
			
				case 'cinii':
					$identifier_id = '<' . $subject_id . '#cinii' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"cinii"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					// Consistent with CiNii RDF
					$sameAs[]  = 'https://ci.nii.ac.jp/naid/' . $identifier->id . '#article';
					break;
			
			
				case 'doi':
					$identifier_id = '<' . $subject_id . '#doi' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"doi"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					$sameAs[]  = 'https://doi.org/' . $identifier->id;
					break;
					
				case 'handle':
					$identifier_id = '<' . $subject_id . '#handle' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"handle"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					$sameAs[]  = 'https://hdl.handle.net/' . $identifier->id;
					break;

				case 'jstor':
					$identifier_id = '<' . $subject_id . '#jstor' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"jstor"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					$sameAs[]  = 'https://www.jstor.org/stable/' . $identifier->id;
					break;
			
			
				default:
					break;
			}
			
			if ($identifier_id != '')
			{
				$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
				$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';			
			}
		
		}
	
	}	
		
	
	//------------------------------------------------------------------------------------
	// Links to other versions/instances/representations
	$sameAs = array_unique($sameAs);
	foreach ($sameAs as $link)
	{
		$triples[] = $s . ' <http://schema.org/sameAs> ' . '"' . addcslashes($link, '"') . '" .';		
	}
	
	//------------------------------------------------------------------------------------
	if (isset($reference->date))
	{
		$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . addcslashes($reference->date, '"') . '" .';			
	}
	else
	{
		if (isset($reference->year))
		{
			$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . addcslashes($reference->year, '"') . '" .';					
		}
	}
	
	
	

/*

	$citeproc_obj = array();
	$citeproc_obj['id'] = $id;

	$citeproc_obj['unstructured'] = reference_to_citation_string($reference);
	
	$citeproc_obj['title'] = $reference->title;
	
	if (isset($reference->abstract))
	{
		$citeproc_obj['abstract'] = $reference->abstract;
	}
	
	// multi
	if (isset($reference->multi))
	{
		$citeproc_obj['multi'] = $reference->multi;
	}
	
	
	if (isset($reference->journal))
	{	
		$citeproc_obj['type'] = 'article-journal';
	}
	
	if (isset($reference->date))
	{
		$citeproc_obj['issued'] = new stdclass;
		$citeproc_obj['issued']->{'date-parts'} = array();
		$citeproc_obj['issued']->{'date-parts'}[0] = array();
		$parts = explode('-', $reference->date);
		
		$citeproc_obj['issued']->{'date-parts'}[0][] = (Integer)$parts[0];

		if ($parts[1] != '00')
		{		
			$citeproc_obj['issued']->{'date-parts'}[0][] = (Integer)$parts[1];
		}

		if ($parts[2] != '00')
		{		
			$citeproc_obj['issued']->{'date-parts'}[0][] = (Integer)$parts[2];
		}
	
	}
	else
	{
		if (isset($reference->year))
		{
			$citeproc_obj['issued'] = new stdclass;
			$citeproc_obj['issued']->{'date-parts'} = array();
			$citeproc_obj['issued']->{'date-parts'}[] = array((Integer)$reference->year);
		}
	}
	
	if (isset($reference->publisher))
	{
		$citeproc_obj['publisher'] = $reference->publisher;
	}
	if (isset($reference->publoc))
	{
		$citeproc_obj['publisher-place'] = $reference->publoc;
	}
	
	
	if (isset($reference->author))
	{
		$citeproc_obj['author'] = array();

		foreach ($reference->author as $author)
		{
			$a = array();
			if (isset($author->firstname))
			{
				$a['given'] = $author->firstname;
				$a['family'] = $author->lastname;
			}
			else
			{
				$a['literal'] = $author->name;
				//$a['family'] = $author->name;
			}

			if (isset($author->multi))
			{
				$a['multi']= $author->multi;
			}
			
			$citeproc_obj['author'][] = $a;
		}
		
	}
	
	if (isset($reference->journal))
	{
		$citeproc_obj['container-title'] = $reference->journal->name;
		
		if (isset($reference->journal->series))
		{
			$citeproc_obj['collection-title'] = $reference->journal->series;
		}
		
		if (isset($reference->journal->volume))
		{
			$citeproc_obj['volume'] = $reference->journal->volume;
		}
		
		if (isset($reference->journal->issue))
		{
			$citeproc_obj['issue'] = $reference->journal->issue;
		}
		if (isset($reference->journal->pages))
		{	
			$citeproc_obj['page'] = str_replace('--', '-', $reference->journal->pages);
			
			if (preg_match('/^[a-z]?\d+$/', $reference->journal->pages, $m))
			{
				$citeproc_obj['page-first'] = $reference->journal->pages;
			}

			if (preg_match('/(?<spage>\d+)-(?<epage>\d+)/', $reference->journal->pages, $m))
			{
				$citeproc_obj['page-first'] = $m['spage'];
			}
			
		}
		
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$citeproc_obj['ISSN'][] = $identifier->id;
						break;
					default:
						break;
				}
			}
		}
		
	// multi
	if (isset($reference->journal->multi))
	{
		if (isset($citeproc_obj['multi']))
		{
			$citeproc_obj['multi']->_key->{'container-title'} = $reference->journal->multi->_key->name;
		}
		else
		{
			$citeproc_obj['multi']= $reference->journal->multi;
		}
	}
		
		
	}
	
	if (isset($reference->identifier))
	{
		$citeproc_obj['alternative-id'] = array(); 
		foreach ($reference->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'cinii':
					$citeproc_obj['CINII'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'CINII:' . $identifier->id;
					break;
			
				case 'doi':
					$citeproc_obj['DOI'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'DOI:' . $identifier->id;
					break;

				case 'handle':
					$citeproc_obj['HANDLE'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = $identifier->id;
					break;

				case 'isbn':
				case 'isbn10':
				case 'isbn13':
					$citeproc_obj['ISBN'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = $identifier->id;
					break;

				case 'jstor':
					$citeproc_obj['JSTOR'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'JSTOR:' . $identifier->id;
					break;

				case 'pmid':
					$citeproc_obj['PMID'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'PMID:' . $identifier->id;
					break;

				case 'pmc':
					$citeproc_obj['PMC'] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'PMC:' . $identifier->id;
					break;
					
				case 'pii':
					$citeproc_obj['alternative-id'][] = $identifier->id;
					$citeproc_obj['alternative-id'][] = 'PII:' . $identifier->id;
					break;

				case 'oai':
					$citeproc_obj['alternative-id'][] = $identifier->id;
					break;
					
				case 'zenodo':
					$citeproc_obj['ZENODO'] = $identifier->id;
					break;
					
				default:
					break;
			}
		}
	}
*/
	
	$nt = join("\n", $triples);	
	return $nt;
}

?>