<?php

// Merge duplicate references into a single record (e.g, from two different sources)
// and export as RIS

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');require_once(dirname(__FILE__) . '/config.inc.php');

$field_to_ris_key = array(
	'title' 	=> 'TI',
	'journal' 	=> 'JO',
	'issn' 		=> 'SN',
	'volume' 	=> 'VL',
	'issue' 	=> 'IS',
	'spage' 	=> 'SP',
	'epage' 	=> 'EP',
	'year' 		=> 'Y1',
	'abstract'	=> 'N2',
	'url'		=> 'UR',
	'pdf'		=> 'L1',
	'doi'		=> 'DO'
	);

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


// > 89

// Journal of The Royal Society of Western Australia
$sql = 'SELECT * FROM publications WHERE issn="0035-922X" AND volume = 96';
//$sql = 'SELECT * FROM publications WHERE issn="0035-922X" AND volume BETWEEN 78 AND 88';
$sql = 'SELECT * FROM publications WHERE issn="0035-922X" AND volume BETWEEN 97 AND 99';
//$sql = 'SELECT * FROM publications WHERE issn="0035-922X" AND volume > 89';

$sql .= ' ORDER BY CAST(series as SIGNED), CAST(volume as SIGNED), CAST(spage as SIGNED)';


$items = array();


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	
	$parts = array();
	
	if ($result->fields['volume'] != '')
	{
		$parts[] = $result->fields['volume'];
	}
	if ($result->fields['spage'] != '')
	{
		$parts[] = $result->fields['spage'];
	}
	
	$key = join('-', $parts);
	
	if (!isset($items[$key]))
	{
		$items[$key] = array();
	}
		
	foreach ($result->fields as $k => $v)
	{
		if ($v != '')
		{
	
			switch ($k)
			{
				case 'title':
				case 'journal':
				case 'authors':
				case 'issn':
				case 'volume':
				case 'issue':
				case 'spage':
				case 'epage':
				case 'pdf':
				case 'year':
					
					if (!isset($items[$key][$k]))
					{
						$items[$key][$k] = array();
					}
					if (1)
					{					
						if (!in_array($v, $items[$key][$k]))
						{					
							$items[$key][$k][] = $v;
						}
					}
					else
					{
						$items[$key][$k][] = $v;
					}
					
					break;
					
				default:
					break;
			}
		}
	}		
		




	$result->MoveNext();
}


// dump

//print_r($items);

// merge



foreach ($items as $key => $data)
{
	$go = true;

	// sanity check
	// if more than one title, check not too different, then accept one
	if (!isset($data['title']))
	{
		$go = false;
	}
	else
	{
		if (count($data['title']) == 2)
		{
			//print_r($data['title']);
			//exit();
		
			$d = levenshtein(strtolower($data['title'][0]), strtolower($data['title'][1]));
			
			//echo "d=$d\n";
			if ($d < 3)
			{
				$data['title'] = array($data['title'][0]);
			}
			else
			{
				$go = false;
			}
		}			
	}	
	
	
	if ($go)
	{
	


		$reference = new stdclass;
	
		foreach ($data as $k => $v)
		{
			switch ($k)
			{
				case 'authors':
					$max = strlen($v[0]);
					$index = 0;
					$i = 1;
					$n = count($v);
					while ($i < $n)
					{
						if (strlen($v[$i]) > $max)
						{
							$index = $i;
							$max = strlen($v[$i]);
						}
						$i++;
					}
				
					$reference->authors = explode(';', $v[$index]);
					break;
				
				default:
					if (count($v) == 1)
					{
						$reference->{$k} = $v[0];
					}
					break;
		
			}
	
		}
	
		//print_r($reference);
	
		$ris = "TY  - JOUR\n";
		foreach ($reference as $k => $v)
		{
			if ($k == 'authors')
			{
				foreach ($v as $a)
				{
					$ris .= 'AU  - ' . $a . "\n";
				}
			}
			else
			{
				$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
			}
		}
		$ris .= "ER  - \n";
	
		echo $ris . "\n";

	}

}


// export



?>