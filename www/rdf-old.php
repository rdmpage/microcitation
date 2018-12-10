<?php

// Export reference in simple RDF

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/reference.php');

require_once('php-json-ld/jsonld.php');


$guid = 'http://www.jstor.org/stable/3668632';
$guid = '10.3767/000651906x622210';


if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
}


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

//$db->EXECUTE("set publications 'utf8'"); 
$db->EXECUTE("set names 'utf8'"); 


//--------------------------------------------------------------------------------------------------

$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

if (!preg_match('/^10\./', $guid))
{	
	if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<id>\d+)/', $guid, $m))
	{
		$sql .= ' OR jstor=' . $m['id'];
	}
	else
	{
	
		if (preg_match('/^http:\/\//', $guid))
		{
			$sql .= ' OR url="' . $guid . '"';
		}
	}
}
$sql .= ' LIMIT 1;';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{

	
	$triples = array();
	
	$guid = $result->fields['guid'];
	
	if (preg_match('/^10./', $guid))
	{
		$guid = 'https://doi.org/' . $guid;
	}

	$subject_id = $guid; // fix this


	$s = '<' . $subject_id . '>';
	
	$triples[] = $s . ' <http://schema.org/identifier> "' . $guid . '" .';

	
	$type = 'ScholarlyArticle';
	
	if ($result->fields['type'] == 'book')
	{
		$type = 'Book';
	}
	
	$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/' . $type . '> .';
	
	// title
	$title = $result->fields['title'];	
	$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($title, '"\\') . '" .';
	
	// authors
	// authors
	if ($result->fields['authors'] != '')
	{
		$delimiter = ';';
	
		$authors = explode($delimiter, trim($result->fields['authors']));
		
		$n = count($authors);
		
		for ($i = 0; $i < $n; $i++)
		{
			$author_id = $subject_id . '#creator/' . ($i + 1);
			
			$triples[] = $s . ' <http://schema.org/creator> ' . '<' . $author_id . '> .';
			$triples[] = '<' . $author_id . '> <http://schema.org/creator> ' . '"' . $authors[$i] . '" .';
			
		}
	}
	
	// date
	$year = $result->fields['year'];	
	$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . $year . '" .';
	
	
	$t = join("\n", $triples);
	
	//echo $t . "\n";

	// triples or JSON-LD?
	if (0)
	{
		header("Content-type: text/plain\n\n");
		echo $t . "\n";
	}
	else
	{

		$doc = jsonld_from_rdf($t, array('format' => 'application/nquads'));
	

		$context = (object)array(
			'@vocab' => 'http://schema.org/'
		);

		$compacted = jsonld_compact($doc, $context);
	
		//print_r($compacted);

		echo json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		echo "\n";
	}

		
		
		
				
	$result->MoveNext();
}	
	


?>