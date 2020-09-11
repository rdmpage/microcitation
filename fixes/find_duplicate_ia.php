<?php

// Fix some things

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$sql = 'SELECT * from publications where issn="0374-6429" and internetarchive IS NOT NULL';


$ia = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$guid = $result->fields['guid'];
	$internetarchive = $result->fields['internetarchive'];
	
	if (!isset($ia[$internetarchive]))
	{
		$ia[$internetarchive] = array();	
	}
	$ia[$internetarchive][] = $guid;

	
	$result->MoveNext();
}
	
	
print_r($ia);

$g = array();

foreach ($ia as $k => $v)
{
	$c = count($v);
	if ($c > 1)
	{
		echo "*** $k $c ***\n";
		
		$g = array_merge($g, $v);
	}
}

print_r($g);

echo "\n\n";
echo join('","', $g);
echo "\n\n";



?>