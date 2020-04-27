<?php

// Dump and process large MySQL database by paging through the data

error_reporting(E_ALL ^ E_DEPRECATED);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

function clean_string($str)
{
	// Convert accented characters
	$str = strtr(utf8_decode($str), 
			utf8_decode("ÀÁÂÃÄÅàáâãäåĀāĂăĄąÇçĆćĈĉĊċČčÐðĎďĐđÈÉÊËèéêëĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħÌÍÎÏìíîïĨĩĪīĬĭĮįİıĴĵĶķĸĹĺĻļĽľĿŀŁłÑñŃńŅņŇňŉŊŋÒÓÔÕÖØòóôõöøŌōŎŏŐőŔŕŖŗŘřŚśŜŝŞşŠšſŢţŤťŦŧÙÚÛÜùúûüŨũŪūŬŭŮůŰűŲųŴŵÝýÿŶŷŸŹźŻżŽž"),
			"aaaaaaaaaaaaaaaaaaccccccccccddddddeeeeeeeeeeeeeeeeeegggggggghhhhiiiiiiiiiiiiiiiiiijjkkkllllllllllnnnnnnnnnnnoooooooooooooooooorrrrrrsssssssssttttttuuuuuuuuuuuuuuuuuuuuwwyyyyyyzzzzzz");
	
	
	// strip punctuation
	$str = preg_replace('/[,|\.|\(|\)|-|\[|\]]/', '', $str);

	$str = preg_replace('/\s\s+/', ' ', $str);
	$str = preg_replace('/^\s+/', '', $str);
	$str = preg_replace('/\s+$/', '', $str);
	
	return $str;
}

// Connect
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' , 'microcitation');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$page = 1000;
$offset = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT guid, authors, year, title, journal, volume, issue, spage, epage FROM publications';

//	$sql .= ' WHERE guid="http://www.zobodat.at/publikation_articles.php?id=236585"';
	
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$guid = $result->fields['guid'];
		
		$keys = array(
 'authors',
 'year', 
 'title',
 'journal', 
 'volume', 
 'issue', 
 'spage', 
 'epage'		
		);
		
		$terms = array();
		foreach ($keys as $key)
		{
			switch ($key)
			{
				case 'authors':
					$term = $result->fields[$key];					
					$term = preg_replace('/(\s+\[[^]]+\])/u', '', $term);
					$terms[] = $term;				
					break;
			
				default:
					$terms[] = $result->fields[$key];
					break;
			}
		
			
		}
		
		//print_r($terms);
		
		
		$target = join(' ', $terms);
		
		$target = clean_string($target);
		
		echo $guid . "\t" . $target . "\n";

		$result->MoveNext();

	}
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		// If we want to bale out and check it worked
		//if ($offset > 1000) { $done = true; }
	}
	

}

?>
