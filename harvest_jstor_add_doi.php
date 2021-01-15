<?php

// import JSTOR from RIS and either add, or update records with existing DOI
require_once(dirname(__FILE__) . '/ris.php');


//--------------------------------------------------------------------------------------------------
function get_value_from_key($keys, $values, $k)
{
	$v = '';
	$count = 0;
	while ($count < count($keys))
	{
		if ($keys[$count] == $k)
		{
			break;
		}
		$count++;
	}
	if ($count < count($keys))
	{
		$v = $values[$count];
	}
	return $v;
}


//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
	global $db;
	
	// print_r($reference);
	
	$guid = '';
	$doi = '';
	$jstor = 0;

	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'doi':
					$doi = $identifier->id;
					break;
				
				case 'handle':
					break;

				case 'jstor':
					
					$jstor = $identifier->id;
					break;
				
				default:
					break;
			}
		}
	}	

	// echo $doi . ' ' . $jstor . "\n";


	if ($doi != '')
	{
		echo 'UPDATE publications SET doi="' . $doi . '" WHERE jstor= "' . $jstor . '";' . "\n";
	}
	
	
	
	
	/*
	// Exists?
	if (have_guid($guid))
	{
		// have already
		$sql = "-- have $guid already, skip...\n";	
		echo $sql;	
		
		$update_sql = 'UPDATE publications SET epage=' . get_value_from_key($keys, $values, 'epage') . ' WHERE guid="' . $guid . '";';
		echo $update_sql . "\n";
		
	}
	*/
	/*
	else
	{
		if ($jstor != 0)
		{
			if (have_jstor($jstor))
			{
				// have already this JSTOR id
				$sql = "-- have JSTOR $jstor already, skip...\n";
				echo $sql;
			}
			else
			{
				// don't have this JSTOR record, either we don't have reference, 
				// or reference has external DOI
				$update_sql = have_reference($keys, $values, array('jstor' => $jstor));
				if ($update_sql != '')
				{
					// add JSTOR id to record
					echo $update_sql . "\n";
				}
				else
				{
					// add reference
					$sql = 'REPLACE INTO publications(' . join(',', $keys) . ') values('
						. join(',', $values) . ');';
					echo $sql . "\n";
				}
			}
		}
		
	}
	*/

	
}




//--------------------------------------------------------------------------------------------------
$filename = '';
if ($argc < 2)
{
	echo "Usage: import_ris.php <RIS file> \n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'ris_import');


?>