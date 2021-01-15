<?php

// Make backup of journal article metadata
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

//----------------------------------------------------------------------------------------

$string = 'Ë À Ì Â Í Ã Î Ä Ï Ç Ò È Ó É Ô Ê Õ Ö ê Ù ë Ú î Û ï Ü ô Ý õ â û ã ÿ ç';

$normalizeChars = array(
    'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
    'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
    'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
    'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
    'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
    'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
    'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
    'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
);


//----------------------------------------------------------------------------------------


$obj = new stdclass;
$obj->journal = 'Acta Biológica Paranaense';
$obj->issn = '0301-2123';


$filename = $obj->journal;

$filename = strtr($filename, $normalizeChars);
$filename = strtolower($filename);
$filename = preg_replace('/[:|\(|\)|\']/', '', $filename);
$filename = preg_replace('/\s\s*/', '-', $filename);

echo "Filename=$filename\n";


$basedir = 'journal-backups';

$jsonl_filename = $basedir . '/' . $filename . '.jsonl';
$ris_filename = $basedir . '/' . $filename . '.ris';


// create files 
$jsonl_handle = fopen($jsonl_filename, "w");
$ris_handle = fopen($ris_filename, "w");


$jsonl_handle = fopen($jsonl_filename, "w");
$ris_handle = fopen($ris_filename, "w");

$sql = '';

if (isset($obj->issn))
{
	$table = 'publications';
	
	$sql = 'SELECT guid FROM `' . $table . '` WHERE issn="' . $obj->issn . '"';
	
	$sql .= ' ORDER BY CAST(series as SIGNED), CAST(volume as SIGNED), issue, CAST(spage as SIGNED)';
}


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	
	// CSL
	$url = 'http://localhost/~rpage/microcitation/www/citeproc-api.php?guid=' . urlencode($guid);

	$json = get($url);
	
	$reference = json_decode($json);
	
	
	
	echo json_encode($reference, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";
	
	fwrite($jsonl_handle, json_encode($reference, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
	
	
	// RIS
	$url = 'http://localhost/~rpage/microcitation/www/ris-api.php?guid=' . urlencode($guid);

	$ris = get($url);
	
	fwrite($ris_handle, $ris . "\n");
	
	
	echo $ris;
	
	
	$result->MoveNext();

}

fclose($jsonl_handle); 
fclose($ris_handle );


?>



