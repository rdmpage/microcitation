<?php

// import RIS and update selected fields

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
				/*
				if (isset($reference->journal))
				{
			
				
					if (isset($reference->journal->issue))
					{
						$sql = 'UPDATE publications SET issue="' . $reference->journal->issue . '"'
								. ' WHERE url="' . $link->url . '";' ;

						echo $sql . "\n";						
						
					}


				}
				*/
			}
			
			
			if (isset($reference->author))
			{
				$authors = array();
			
				foreach ($reference->author as $author)
				{
					$authors[] = $author->name;
				}

				$sql = 'UPDATE publications SET authors="' . join(';', $authors) . '"'
					. ' WHERE url="' . $link->url . '";' ;

				echo $sql . "\n";						
			
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