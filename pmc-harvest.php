<?php

// fetch from CrossRef using new API
require_once(dirname(__FILE__) . '/lib.php');


$pmcs=array(
'PMC8473676',
'PMC8473674',
'PMC8473672',
'PMC8315928',
'PMC8315927',
'PMC8292844',
'PMC8292842',
'PMC8231403',
'PMC8181156',
'PMC8181171',
'PMC8181159',
'PMC8181153',
'PMC8181160',
'PMC8181164',
'PMC8181165',
'PMC8181161',
'PMC8181155',
'PMC7807172',
'PMC7807174',
'PMC7738940',
'PMC7736994',
'PMC7736775',
'PMC7736772',
'PMC7736770',
'PMC7689053',
'PMC7689051',
'PMC7689335',
'PMC7691830',
'PMC7688400',
'PMC7688405',
'PMC7688399',
'PMC7688402',
'PMC7688403',
'PMC7688397',
'PMC7688404',
'PMC7691835',
'PMC7807173',
'PMC7396921',
'PMC7396930',
'PMC7396923',
'PMC7396920',
'PMC7262541',
'PMC7096696',
'PMC7089852',
'PMC7028430',
'PMC6971529',
'PMC6920519',
'PMC6971535',
'PMC6971534',
'PMC6971531',
'PMC6971530',
'PMC6920520',
'PMC6917559',
'PMC6917564',
'PMC6917565',
'PMC6917566',
'PMC6890779',
'PMC6838347',
'PMC6838190',
'PMC6875684',
'PMC6759870',
'PMC6763701',
'PMC6759868',
'PMC6920518',
'PMC6759859',
'PMC6409442',
'PMC6409914',
'PMC6409446',
'PMC6409640',
'PMC6409488',
'PMC6409443',
'PMC6517774',
'PMC6517783',
'PMC6517775',
'PMC6517706',
'PMC6517732',
'PMC6517734',
'PMC6517738',
'PMC6517750',
'PMC6517756',
'PMC6517765',
'PMC6517730',
'PMC6517709',
'PMC6517716',
'PMC6517776',
'PMC6517704',
'PMC6517829',
'PMC6517782',
'PMC6624643',
'PMC6517745',
'PMC6517764',
'PMC6517717',
'PMC6517741',
'PMC6517760',
'PMC6517807',
'PMC6517742',
'PMC6517700',
'PMC6517806',
'PMC6517725',
'PMC6517721',
'PMC6517755',
'PMC6517766',
'PMC6517754',
'PMC6517769',
'PMC6517719',
'PMC6517727',
'PMC6517731',
'PMC6409490',
'PMC6517705',
'PMC6517736',
'PMC6517699',
'PMC6517752',
'PMC6517728',
'PMC6517724',
'PMC6517751',
'PMC6511909',
'PMC6511971',
'PMC6511905',
'PMC6511911',
'PMC6511910',
'PMC6511904',
'PMC6511969',
'PMC6511896',
'PMC6511897',
'PMC6511893',
'PMC6511907',
'PMC6511826',
'PMC6511816',
'PMC6409790',
'PMC6409503',
'PMC6409445',
'PMC6409506',
'PMC6409487',
'PMC6511823',
'PMC6511818',
'PMC6511824',
'PMC6511829',
'PMC6511815',
'PMC6686113',
'PMC6661508',
'PMC6661506',
'PMC6661511',
'PMC6661512',
'PMC6661444',
'PMC6661441',
'PMC6661437',
'PMC6661509',
'PMC6661443',
'PMC6661507',
'PMC6661433',
'PMC6686114',
'PMC6661435',
'PMC6661442',
'PMC6661429',
'PMC6661440',
'PMC6661432',
'PMC6661446',
'PMC6747628',
'PMC6661425',
'PMC6661370',
'PMC6661294',
'PMC6661288',
'PMC6661369',
'PMC6661366',
'PMC6661367',
'PMC6661298',
'PMC6661365',
'PMC6661296',
'PMC6661292',
'PMC8473675',
'PMC8473673',
'PMC8319620',
'PMC8315926',
'PMC8315924',
'PMC8292846',
'PMC8292845',
'PMC8292843',
'PMC8181154',
'PMC8181157',
'PMC8181166',
'PMC8181158',
'PMC8181168',
'PMC8181167',
'PMC7753241',
'PMC7753242',
'PMC7736774',
'PMC7746975',
'PMC7736776',
'PMC7736773',
'PMC7736771',
'PMC7736769',
'PMC7700947',
'PMC7689054',
'PMC7689052',
'PMC7689050',
'PMC7688398',
'PMC7688423',
'PMC7688401',
'PMC7689055',
'PMC7807176',
'PMC7691842',
'PMC7688406',
'PMC7396922',
'PMC7396926',
'PMC7396925',
'PMC7396924',
'PMC7399383',
'PMC7184263',
'PMC6971532',
'PMC6971533',
'PMC6971528',
'PMC6917557',
'PMC6917563',
'PMC6943202',
'PMC6917567',
'PMC6917562',
'PMC6917558',
'PMC6920521',
'PMC6917560',
'PMC6917561',
'PMC6838184',
'PMC6875685',
'PMC6838183',
'PMC6759935',
'PMC6759918',
'PMC6778772',
'PMC6759924',
'PMC6760491',
'PMC6759861',
'PMC6759864',
'PMC6759916',
'PMC6763702',
'PMC6409908',
'PMC6409733',
'PMC6409502',
'PMC6517737',
'PMC6517748',
'PMC6517761',
'PMC6624640',
'PMC6517743',
'PMC6517763',
'PMC6517712',
'PMC6517735',
'PMC6517715',
'PMC6517747',
'PMC6517828',
'PMC6517708',
'PMC6517805',
'PMC6517768',
'PMC6517771',
'PMC6517702',
'PMC6517762',
'PMC6517714',
'PMC6517753',
'PMC6517773',
'PMC6517777',
'PMC6517733',
'PMC6517720',
'PMC6517778',
'PMC6517723',
'PMC6624641',
'PMC6517746',
'PMC6517729',
'PMC6517703',
'PMC6517710',
'PMC6517744',
'PMC6517701',
'PMC6517758',
'PMC6517772',
'PMC6517759',
'PMC6517711',
'PMC6517726',
'PMC6517740',
'PMC6517722',
'PMC6517767',
'PMC6517713',
'PMC6409505',
'PMC6409441',
'PMC6517770',
'PMC6517718',
'PMC6517707',
'PMC6517757',
'PMC6517739',
'PMC6517698',
'PMC6511903',
'PMC6511912',
'PMC6511970',
'PMC6511895',
'PMC6511899',
'PMC6511901',
'PMC6511898',
'PMC6511894',
'PMC6511902',
'PMC6624642',
'PMC6511900',
'PMC6511819',
'PMC6511817',
'PMC6511906',
'PMC6511821',
'PMC6511814',
'PMC6511830',
'PMC6410070',
'PMC6409504',
'PMC6409484',
'PMC6409440',
'PMC6409489',
'PMC6409444',
'PMC6409507',
'PMC6511913',
'PMC6511820',
'PMC6511825',
'PMC6511822',
'PMC6511908',
'PMC6511827',
'PMC6511828',
'PMC6661510',
'PMC6661427',
'PMC6661426',
'PMC6661448',
'PMC6747627',
'PMC6661431',
'PMC6661428',
'PMC6661514',
'PMC6686112',
'PMC6661434',
'PMC6661430',
'PMC6661438',
'PMC6661445',
'PMC6661439',
'PMC6661447',
'PMC6661436',
'PMC6661513',
'PMC6661290',
'PMC6661371',
'PMC6661297',
'PMC6661289',
'PMC6661300',
'PMC6661291',
'PMC6661299',
'PMC6661293',
'PMC6661368',
'PMC6661295',

);


$count = 1;

foreach ($pmcs as $pmc)
{
	$parameters = array(
		'query' => 'PMCID:' . $pmc,
		'resulttype'	=> 'core',
		'format'		=> 'json'
	);

	$url = 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?' . http_build_query($parameters);

	$json = get($url);
					
	$obj = json_decode($json);
	//print_r($obj);
	
	
	$csl = new stdclass;
	$csl->type = 'article-journal';
	
	foreach ($obj->resultList->result[0] as $k => $v)
	{
		switch ($k)
		{
			case 'pmcid':
				$csl->PMC = $v;
				break;

			case 'pmid':
				$csl->PMID = $v;
				break;

			case 'doi':
				$csl->DOI = $v;
				break;

			case 'title':
				$csl->title =strip_tags($v);
				break;

			case 'firstPublicationDate':
				$csl->issued = new stdclass;
				$csl->issued->{'date-parts'} = array();
				$csl->issued->{'date-parts'}[0] = array();
				$csl->issued->{'date-parts'}[0] = explode('-', $v);
				break;
				
			case 'pageInfo':
				$csl->page = $v;
				break;
				
			case 'journalInfo':
				if (isset($v->journal->title))
				{
					$csl->{'container-title'} = $v->journal->title;
				}
				if (isset($v->journal->issn))
				{
					$csl->ISSN = array($v->journal->issn);
				}
				if (isset($v->volume))
				{
					$csl->volume = $v->volume;
				}
				if (isset($v->issue))
				{
					$csl->issue = $v->issue;
				}
				break;
				
			case 'authorList':
				foreach ($v->author as $author)
				{
					$a = new stdclass;
					$a->given = $author->firstName;
					$a->family = $author->lastName;
					
					if (isset($author->authorId))
					{
						if ($author->authorId->type == 'ORCID')
						{
							$a->ORCID = 'https://orcid.org/' . $author->authorId->value;
						}
					}
					
					if (isset($author->authorAffiliationDetailsList))
					{
						foreach ($author->authorAffiliationDetailsList->authorAffiliation as $affiliation)
						{
							$aff = new stdclass;
							$aff->name = $affiliation->affiliation;
							$a->affiliation[] = $aff;
						}
					}
					
				
					$csl->author[] = $a;
				
				}
				break;

			default:
				break;
		}
	
	
	}
	
	// convert CSL to SQL		

	$keys = array();
	$values = array();

	$keys[] = 'guid';
	$values[] = '"' . $csl->DOI . '"';

	$keys[] = 'doi';
	$values[] = '"' . $csl->DOI . '"';

	$keys[] = 'pmid';
	$values[] = '"' . $csl->PMID . '"';

	$keys[] = 'pmc';
	$values[] = '"' . $csl->PMC . '"';

	foreach ($csl as $k => $v)
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
					if (($link->{'content-type'} == 'application/pdf') && ($pdf == ''))
					{
						$keys[] = 'pdf';
						$values[] = '"' . $link->URL . '"';		
						
						$pdf = $link->URL;	
					}
				}					
				break;

	
			default:
				break;
		}
	}
	
	//$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';
	$sql = 'REPLACE INTO publications_tmp(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');';

	echo $sql . "\n";
	
	
	// Give server a break every 10 items
	if (($count++ % 10) == 0)
	{
		$rand = rand(1000000, 3000000);
		usleep($rand);
	}
	
	
}



?>