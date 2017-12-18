<?php

// bibliographic reference

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
	
	$citeproc_obj['alternative-id'] = array_unique($citeproc_obj['alternative-id']);
	if (count($citeproc_obj['alternative-id']) == 0)
	{
		unset($citeproc_obj['alternative-id']);
	}
	
	
	if (isset($reference->thumbnail))
	{	
		$citeproc_obj['thumbnail'] = $reference->thumbnail;
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
function reference_to_sici($reference)
{
}


?>