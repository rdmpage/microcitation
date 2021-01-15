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

$issns=array(
//'2156-0382',
//'0013-8738',
//'0038-3872',
//'1175-5326',
'2050-9928',
);

$start 	= 2000;
$end 	= 2019;

$start 	= 1990;
$end 	= 1999;

$start 	= 1877;
$end 	= 1909;

$start 	= 2005;
$end 	= 2019;


$start 	= 2008;
$end 	= 2010;

// Arachnology, British Arach
$start 	= 2007;
$end 	= 2020;

// Bijdragen tot de Dierkunde
$issns=array('2666-0644');
$start 	= 1848;
$end 	= 1996;

// Rodriguésia
$issns=array('2175-7860');
$start 	= 1980;
$end 	= 2020;
$start 	= 2010;
$end 	= 2016;

// Acta Chiropterologica
$issns=array('1508-1109');
$start 	= 2002;
$end 	= 2020;

// J. Mammalogy
$issns = array('0022-2372');
$start 	= 1919;
$end 	= 2020;


// Rec West Austr Mus
$issns = array('0312-3162');
$start 	= 1998;
$end 	= 2020;


$issns = array('0067-2238');
$start 	= 1982;
$end 	= 2020;


$issns = array('0015-0754');
$start 	= 2005;
$end 	= 2009;

// Fieldiana Life and Earth Sciences
$issns = array('2158-5520');
$start 	= 2010;
$end 	= 2017;

$issns = array('0366-5232');
$start 	= 2013;
$end 	= 2020;

$issns = array('0370-2774');
$start 	= 1937;
$end 	= 1937;

$issns = array('0079-8835');
$start 	= 2018;
$end 	= 2019;

// Syst Bot
$issns = array('0363-6445');
$start 	= 2001;
$end 	= 2003;

// South American journal of herpetology
$issns = array('1808-9798');
$start 	= 2006;
$end 	= 2020;

// Copeia
$issns = array('0045-8511');
$start 	= 1913;
$end 	= 2020;

// Tropical zoology
$issns = array('0394-6975');
$start 	= 1988;
$end 	= 2020;

//Herpetological Monographs
$issns = array('0733-1347');
$start 	= 2002;
$end 	= 2020;

// Journal of the Society for the Bibliography of Natural History 
$issns = array('0037-9778');
$start 	= 1936;
$end 	= 1980;

// Archives of Natural History 
$issns = array('0260-9541');
$start 	= 1981;
$end 	= 2020;

// Archives of Natural History 
$issns = array('0260-9541');
$start 	= 1981;
$end 	= 2020;

// Proc Zool Soc (bad dates)
$issns = array('0370-2774');
$start 	= 2011;
$end 	= 2012;

$issns = array('0211-1322');
$start 	= 1996;
$start 	= 2013;
$end 	= 2014;


// Zootaxa
$issns = array('1175-5326');
$start 	= 2001;
$end 	= 2006;

// Records of the Australian Museum
$issns = array('0067-1975');
$start 	= 2019;
$end 	= 2020;

$start 	= 2013;
$end 	= 2015;


$issns = array('0372-1426');
$start 	= 2006;
$end 	= 2020;

$issns = array('0001-5202');
$start 	= 1936;
$end 	= 2020;

//  1791-1874

$issns = array('1945-9440');
$start 	= 1875;
$end 	= 1936;

// 0003-0082
$issns = array('0003-0082');
$start 	= 2000;
$end 	= 2020;

// 0037-9271
$issns = array('0037-9271');
$start 	= 2002;
$end 	= 2020;

// 2095-0357
$issns = array('2095-0357');
$start 	= 2010;
$end 	= 2021;

// 2200-4025 Telopea
$issns = array('2200-4025');
$start 	= 2016;
$end 	= 2021;

// 0027-4100
$issns = array('0027-4100');
$start 	= 2003;
$end 	= 2021;


// 00384909
$issns = array('0038-4909');
$start 	= 2003;
$end 	= 2021;

// 0366-1326
$issns = array('0366-1326');
$start 	= 2013;
$end 	= 2021;

// 00044-5096
$issns = array('0044-5096');
$start 	= 1974;
$end 	= 1974;

// 0003-4983
$issns = array('0003-4983');
$start 	= 1907;
$end 	= 2011;


// EntomoBrasilis
// 1983-0572
$issns = array('1983-0572');
$start 	= 2008;
$end 	= 2020;

// Anais da Escola Superior de Agricultura Luiz de Queiroz
// 1983-0572
$issns = array('0071-1276');
$start 	= 1944;
$end 	= 1991;


// Annales de parasitologie humaine et comparée
// 1983-0572
$issns = array('0003-4150');
$start 	= 1923;
$end 	= 1993;

$start 	= 1957;
$end 	= 1957;

// ВОПРОСЫ ИХТИОЛОГИИ 
$issns = array('0042-8752');
$start 	= 2012;
$end 	= 2020;

$issns = array('1097-993X');
$start 	= 1998;
$end 	= 2020;


$issns = array('0004-2625');
$start 	= 1925;
$end 	= 1990;

// Contributions to Zoology
$issns = array('1875-9866');
$issns = array('1383-4517');
$start 	= 1997;
$end 	= 2020;
//$end 	= 1998;

$issns = array('2465-423X');
$start 	= 2015;
$end 	= 2020;

// Acta Botanica Malacitana
$issns = array('0210-9506');
$start 	= 1993;
$end 	= 2020;

$issns = array('2095-0845');
$start 	= 2011;
$end 	= 2016;



$issns = array('0556-3321');
$start 	= 2016;
$end 	= 2020;


// Journal of Herpetology (BioOne)
$issns = array('0022-1511');
$start 	= 2002;
$end 	= 2020;

// Checklist
$issns = array('1809-127X');
$start 	= 2005;
$end 	= 2020;


// Amphibia-Reptilia
$issns = array('0173-5373');
$start 	= 1980;
$end 	= 2020;


// ZOOS' PRINT JOURNAL
$issns = array('0973-2535');
$start 	= 1995;
$end 	= 2020;

// Rheedea
$issns = array('0971-2313');
$start 	= 2015;
$end 	= 2020;


// Gardens' Bulletin Singapore
$issns = array('2382-5812');
$start 	= 2010;
$end 	= 2020;


// Darwiniana
$issns = array('0011-6793');
$start 	= 2012;
$end 	= 2020;

// Bot. Zhurn. (Moscow & Leningrad)
$issns = array('0006-8136');
$start 	= 2010;
$end 	= 2020;

// Mycological Progress
$issns = array('1617-416X');
$start 	= 2002;
$end 	= 2020;





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
					$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
	
					echo $sql . "\n";
				}
			}
		}

		
		$rand = rand(1000000, 3000000);
		echo '-- sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
		usleep($rand);
	
	}
}

?>