<?php

// fetch from CrossRef using new API
require_once(dirname(__FILE__) . '/lib.php');



// Zootaxa
$issns = array('1175-5326');
$start 	= 2001;
$end 	= 2008;

$start 	= 2010;
$end 	= 2012;

$start 	= 2012;
$end 	= 2014;

$start 	= 2014;
$end 	= 2016;

$start 	= 2016;
$end 	= 2018;

$start 	= 2018;
$end 	= 2021;


$limit = 1000;

foreach ($issns as $issn)
{
	for ($year = $start; $year < $end; $year++)
	{
		for ($month = 1; $month <= 12 ; $month++)
		{
			$date_from = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
			$date_until = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);
		

			$url = 'https://api.crossref.org/works?filter=issn:' . $issn . ',from-pub-date:' . $date_from  . ',until-pub-date:' . $date_until;
		
			$url .= '&rows=' . $limit;

			//echo $url . "\n";
			echo "-- $url\n";

			$json = get($url);

			//echo $json;

			$obj = json_decode($json);

			//print_r($obj);
	
			foreach ($obj->message->items as $item)
			{
				$go = true;
			
				if ($item->type == 'journal-issue')
				{
					$go = false;
				}
			
				if ($go)
				{
		
		
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
							case 'volume':
							case 'issue':
								$keys[] = $k;
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
								$keys[] = 'year';
								$values[] = '"' . $v->{'date-parts'}[0][0] . '"';					
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
									if ($link->{'content-type'} == 'application/pdf')
									{
										$keys[] = 'pdf';
										$values[] = '"' . $link->URL . '"';					
									}
								}					
								break;

				
							default:
								break;
						}
					}
				
					// print_r($keys);
					// print_r($values);
		
					if (count($keys) > 2)
					{
						$sql = 'REPLACE INTO `zootaxa-crossref`(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
	
						echo $sql . "\n";
					}
				}
			}
		}

		
		$rand = rand(1000000, 3000000);
		echo '-- sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
		usleep($rand);
	
	}
}

?>