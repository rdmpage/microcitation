<?php

// fetch from CrossRef using new API
require_once(dirname(__FILE__) . '/lib.php');



$issn = '0031-5850';
$issn = '0093-4666';

$issns = array(
//'0003-6072',
//'2589-3823'
"1560-2745",
"2077-7019",
"0236-6495",
"1466-5026",
"0082-0598",
"0093-4666",
"0027-5514",
"1617-416X",
"1340-3540",
"1314-4057",
"1179-3155",
"2210-6340",
"2229-2225",
"0031-5850",
"1878-6146",
"1055-7903",
"1932-6203",
"1095-5674",
"0029-5035",
"0007-2745",
"0001-3765",
"1328-4401",
"0024-2829",
"0394-9486",
"0166-0616",
"1916-2804",
"0181-1584",
"0077-1813",
"0107-055X",
"0008-7475",
"1941-7519",
"1436-2317",
"0974-7893",
);

$issns=array(
"2077-7019",
"0082-0598",
"1617-416X",
"1340-3540",
"1314-4057",
"1179-3155",
"2210-6340",
"2229-2225",
"1878-6146",
"0093-4666",
"1055-7903",
"1466-5026",
"1932-6203",
"1095-5674",
"0027-5514",
"0031-5850",
"0029-5035",
"0007-2745",
"0001-3765",
"1328-4401",
"0024-2829",
"0394-9486",
"0166-0616",
"1916-2804",
"0181-1584",
"0077-1813",
"0107-055X",
"0008-7475",
"1941-7519",
"1436-2317",
"0974-7893",
);

$issns=array(
'0007-2745'
);

$start 	= 2000;
$end 	= 2019;

$start 	= 1990;
$end 	= 1999;


$limit = 1000;

foreach ($issns as $issn)
{


	for ($year = $start; $year <= $end; $year++)
	{

		$url = 'https://api.crossref.org/works?filter=issn:' . $issn . ',from-pub-date:' . $year  . ',until-pub-date:' . ($year + 1);
		
		$url .= '&rows=' . $limit;

		//echo $url . "\n";
		echo "-- $url\n";

		$json = get($url);

		//echo $json;

		$obj = json_decode($json);

		//print_r($obj);
	
		foreach ($obj->message->items as $item)
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

				
					default:
						break;
				}
			}
				
			// print_r($keys);
			// print_r($values);
		
			if (count($keys) > 2)
			{
				$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
	
				echo $sql . "\n";
			}
	
		}

		
		$rand = rand(1000000, 3000000);
		echo '-- sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
		usleep($rand);
	
	}
}

?>