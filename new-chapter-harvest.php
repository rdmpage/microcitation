<?php

// fetch chapetrs from CrossRef using new API
require_once(dirname(__FILE__) . '/lib.php');

$container = 'Flora Australiensis';
$container = 'Icones Plantarum';
$limit = 1000;

$url = 'https://api.crossref.org/works?filter=container-title:' . urlencode($container );

$url .= '&rows=' . $limit;

//echo $url . "\n";
echo "-- $url\n";

$json = get($url);

//echo $json;

$obj = json_decode($json);

foreach ($obj->message->items as $item)
{
	// print_r($item);
	
	$keys = array();
	$values = array();

	$keys[] = 'guid';
	$values[] = '"' . $item->DOI . '"';

	$keys[] = 'doi';
	$values[] = '"' . $item->DOI . '"';

	foreach ($item as $k => $v)
	{
		switch ($k)
		{
		
			case 'type':
			case 'volume':
			case 'issue':
			case 'publisher':
				$keys[] = $k;
				$values[] = '"' . $v . '"';	
				break;	
				
			case 'publisher-location':
				$keys[] = 'publoc';
				$values[] = '"' . $v . '"';	
				break;	
		
			case 'container-title':
				if (is_array($v))
				{
					$keys[] = 'journal';
					$values[] = '"' . addcslashes($v[0], '"') . '"';					
				}
				else 
				{
					$keys[] = 'journal';
					$values[] = '"' . addcslashes($v, '"') . '"';					
				}
				break;

			case 'title':
				if (is_array($v))
				{
					$keys[] = 'title';
					$values[] = '"' . addcslashes($v[0], '"') . '"';					
				}
				else 
				{
					$keys[] = 'title';
					$values[] = '"' . addcslashes($v, '"') . '"';					
				}
				break;

			case 'ISSN':
				if (is_array($v))
				{
					$keys[] = 'issn';
					$values[] = '"' . addcslashes($v[0], '"') . '"';					
				}
				else 
				{
					$keys[] = 'issn';
					$values[] = '"' . addcslashes($v, '"') . '"';					
				}
				break;
		
			case 'issued':
				if ($v->{'date-parts'}[0][0])
				{
					$keys[] = 'year';
					$values[] = '"' . $v->{'date-parts'}[0][0] . '"';					
				}
				break;
				
			case 'created':
				if (isset($v->{'date-time'}))
				{
					$keys[] = 'date';
					$values[] = '"' . substr($v->{'date-time'}, 0, 10) . '"';					
				}
				break;
				
		
			case 'page':
				if (preg_match('/(?<spage>\d+)-(?<epage>\d+)/', $v, $m))
				{
					$keys[] = 'spage';
					$values[] = '"' . $m['spage'] . '"';					

					$keys[] = 'epage';
					$values[] = '"' . $m['epage'] . '"';					
	
				}
				else
				{
					$keys[] = 'spage';
					$values[] = '"' . $v . '"';					
			
				}
				break;
				
			case 'article-number':
				$keys[] = 'article_number';
				$values[] = '"' . $v . '"';							
				break;
				
		
			case 'author':
				$authors = array();
		
				foreach ($v as $author)
				{
					$authors[] = $author->given . ' ' . $author->family;
				}
		
				$keys[] = 'authors';
				$values[] = '"' . join(';', $authors) . '"';					
		
				break;
			
			case 'link':
				foreach ($v as $link)
				{
					if (($link->{'content-type'} == 'application/pdf') && ($pdf == ''))
					{
						$keys[] = 'pdf';
						$values[] = '"' . $link->URL . '"';		
						
						$pdf = $link->URL;	
					}
				}					
				break;
				
			case 'DOI':
				if (preg_match('/cbo(?<isbn>.*)\./', $v, $m))
				{
					switch (strlen($m['isbn']))
					{
						case 10:
							$keys[] = 'isbn13';
							$values[] = '"' . $m['isbn'] . '"';															
							break;
							
						case 13:
						default:
							$keys[] = 'isbn13';
							$values[] = '"' . $m['isbn'] . '"';															
							break;
					
					}
				}
				break;

	
			default:
				break;
		}
	}
	
	//print_r($keys);
	//print_r($values);
	
	//exit();



	if (count($keys) > 2)
	{
		$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
		
		//$sql = 'REPLACE INTO publications_tmp(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';

		echo $sql . "\n";
	}



}

?>