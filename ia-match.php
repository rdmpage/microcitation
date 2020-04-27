<?php

// Process and match IA search results to publications

require_once(dirname(__FILE__) . '/www/fingerprint.php');
require_once(dirname(__FILE__) . '/www/lcs.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'microcitation');
	
// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$json = '{
                "backup_location": "ia903703_31",
                "btih": "4b091375c04be5c189fc3585f37a5e83bd8a775b",
                "collection": [
                    "citebank"
                ],
                "date": "1994-01-01T00:00:00Z",
                "downloads": 121,
                "format": [
                    "Abbyy GZ",
                    "Animated GIF",
                    "Archive BitTorrent",
                    "DjVu",
                    "DjVuTXT",
                    "Djvu XML",
                    "Item Tile",
                    "Metadata",
                    "Scandata",
                    "Single Page Processed JP2 ZIP",
                    "Text PDF"
                ],
                "identifier": "cbarchive_119093_theremarkableeggofanophelesper1994",
                "indexflag": [
                    "index",
                    "nonoindex"
                ],
                "item_size": 2943516,
                "language": "eng",
                "mediatype": "texts",
                "month": 0,
                "oai_updatedate": [
                    "2011-10-09T07:40:38Z",
                    "2011-10-09T07:40:38Z",
                    "2018-07-07T22:07:00Z"
                ],
                "publicdate": "2011-10-09T07:40:38Z",
                "title": "The remarkable egg of Anopheles peryassui (Diptera: Culicidae).",
                "volume": "26",
                "week": 0,
                "year": "1994"
            }';
            
 
$json = file_get_contents('mosquito_systematics.json');
     
$doc = json_decode($json);

foreach ($doc->response->docs as $obj)
{
	$title = $obj->title;
	$title = preg_replace('/\.$/u', '', $title);
	
	echo "-- $title\n";
	
	$volume = $obj->volume;
	$year = $obj->year;

	$sql = 'SELECT guid, title, volume, MATCH (title) AGAINST ("'
	. addcslashes($title, '"')
	. '") AS score FROM publications '
	. 'WHERE MATCH (title) AGAINST ("'
	. addcslashes($title, '"') . '") '
	. ' AND volume=' . $volume
	. ' AND issn="0091-3669"';
				
	$sql .= ' ORDER BY score DESC LIMIT 5;';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	$max_score = 0;

	$results = array();

	while (!$result->EOF) 
	{
		$hit = new stdclass;
		
		$hit->query = $title;
	
		if (isset($result->fields['title']))
		{
			$hit->title = $result->fields['title'];
			$hit->title = strip_tags($hit->title);
			$hit->title = preg_replace('/\s\s+/u', ' ', $hit->title);
		}			
	
		if (isset($result->fields['volume']))
		{
			$hit->volume = $result->fields['volume'];
		}

		if (isset($result->fields['spage']))
		{
			$hit->spage = $result->fields['spage'];
		}
	
		$hit->guid = $result->fields['guid'];
	
		//print_r($hit);
	
		// check
	
		$v1 = $title;
		$v2 = $hit->title;
	
		$lcs = new LongestCommonSequence($v1, $v2);
		$d = $lcs->score();
	
		//echo $d;
	
		$hit->score = min($d / strlen($v1), $d / strlen($v2));
		
		if ($hit->score > 0.8)
		{
			if ($hit->score > $max_score)
			{
				$max_score = $hit->score;
						
				$results = array();
				$results[] = $hit;
			}
		}	
	
	
		$result->MoveNext();
	}	

	//print_r($results);

	if (count($results) == 1)
	{
		echo 'UPDATE publications SET internetarchive="' . $obj->identifier . '" WHERE guid="' . $results[0]->guid . '";' . "\n";
	}

}


?>
