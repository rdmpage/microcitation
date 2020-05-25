<?php

// import CiNii from RIS and add CiNii to existing data

require_once(dirname(__FILE__) . '/ris.php');


//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
	
	//print_r($reference);
	
	if (isset($reference->link))
	{
		foreach ($reference->link as $link)
		{
			if ($link->anchor == 'LINK')
			{
				$cinii = str_replace('http://ci.nii.ac.jp/naid/', '', $link->url);
				
				$qualifiers = array();
				
				
				if (isset($reference->journal))
				{
				
					if (isset($reference->journal->identifier))
					{
						$qualifiers[] = 'issn="' . $reference->journal->identifier[0]->id . '"';
					}
			
				
					if (isset($reference->journal->volume))
					{
						$qualifiers[] = 'volume="' . $reference->journal->volume . '"';
					}

					if (isset($reference->journal->pages))
					{
						if (preg_match('/(?<spage>\d+)/', $reference->journal->pages, $m))
						{
							$qualifiers[] = 'spage="' . $m['spage'] . '"';
						}
					}


				}
					
				//print_r($qualifiers);
				
				if (count($qualifiers) == 3)
				{
					$sql = 'UPDATE publications SET cinii="' . $cinii . '"'
						. ' WHERE ' . join(" AND ", $qualifiers) . ';'; 

					echo $sql . "\n";
		
				
				}
				
				
				// sql
				// cinii
	

			}
		}
	}

	
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