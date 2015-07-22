<?php

require('lib.php');

//--------------------------------------------------------------------------------------------------
// meta
function get_meta($html)
{
	$reference = new stdclass;
	
	if (preg_match('/<meta\s+name=\'citation_title\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$reference->title = $m['content'];		
	}
	
	if (preg_match_all('/<meta\s+name=\'citation_author\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$authors = array();
		foreach ($m['content'] as $author)
		{
			$authors[] = $author;
		}
		$reference->authors = join(';', $authors);		
	}
	

	if (preg_match('/<meta\s+name=\'citation_journal_title\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$reference->journal = mb_convert_case($m['content'], MB_CASE_TITLE);		
	}
	
	if (preg_match('/<meta\s+name=\'citation_issn\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$reference->issn = $m['content'];		
	}	

	if (preg_match('/<meta\s+name=\'citation_date\'\s+content=\'(?<content>(?<year>[0-9]{4}).*)\'\s*>/Uu', $html, $m))
	{
		$reference->year = $m['year'];
		$reference->date = $m['content'];	
		$reference->date = str_replace('/', '-', $reference->date);	
	}	

	if (preg_match('/<meta\s+name=\'citation_volume\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$reference->volume = $m['content'];		
	}	

	if (preg_match('/<meta\s+name=\'citation_issue\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$reference->issue = $m['content'];		
	}	
		
	if (preg_match('/<meta\s+name=\'citation_firstpage\'\s+content=\'(?<spage>\d+)\'\s*>/Uu', $html, $m))
	{
		$reference->spage = $m['spage'];		
	}
	
	if (preg_match('/<meta\s+name=\'citation_lastpage\'\s+content=\'(?<epage>\d+)\'\s*\>/Uu', $html, $m))
	{
		$reference->epage = $m['epage'];		
	}
	
	if (preg_match('/<meta\s+name=\'citation_abstract_html_url\'\s+content=\'(?<content>.*)\'\s*>/Uu', $html, $m))
	{
		$reference->url = $m['content'];		
	}	
	

	if (preg_match('/<meta\s+name="citation_abstract"\s+content="(?<content>.*)"\s*\>/Uu', $html, $m))
	{
		$reference->abstract = $m['content'];		
	}
	
		
	return $reference;
}

// 2000-2015

$pages_per_issue = 100;

$start_year = 1955;
$end_year = 1955;

for ($year = $start_year; $year <= $end_year; $year++)
{
	$volume = 6;//$year - 1955 ;
	
	//for ($issue = 1; $issue < 5; $issue++)
	$issue = 1;
	{
		$spage = ($issue - 1) * $pages_per_issue + 1;
		$epage = $spage + $pages_per_issue;


		while ($spage < $epage)
		{
			$doi = '10.6165%2ftai.' . $year . '.' . $volume;
			if (0)//($year > 1994) //($year < 2012)
			{			
				$doi .=  '(' . $issue . ')';
			} 
			$doi .= '.' . $spage;
	
			echo "-- $doi\n";
	
			$url = 'http://dx.doi.org/' . $doi;
	
			$html = get($url, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/600.2.5 (KHTML, like Gecko) Version/8.0.2 Safari/600.2.5');
	
			if ($html != '')
			{
	
				//echo $html;
	
				$html = str_replace("\n", "", $html);
	
				$reference = get_meta($html);
		
				$reference->doi = str_replace('%2f', '/', $doi);
				$reference->guid = $reference->doi;
	
				//print_r($reference);
		
				$keys = array();
				$values = array();
		
				foreach ($reference as $k => $v)
				{
					$keys[] = $k;
					$values[] = '"' . addcslashes($v, '"') . '"';
				}
		
				$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
					. join(',', $values) . ');';
			
				echo $sql . "\n";

	
				//$spage = $reference->epage;
				$spage++;
			}
			else
			{
				$spage++;
			}
		}
	}
}

?>