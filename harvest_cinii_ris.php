<?php

// import from RIS

require_once(dirname(__FILE__) . '/ris.php');

$ids = array();

//--------------------------------------------------------------------------------------------------
function ris_import($reference)
{
	global $ids;
	//print_r($reference);
	
	if (isset($reference->link))
	{
		foreach ($reference->link as $link)
		{
			if ($link->anchor == 'LINK')
			{
				$id = str_replace('http://ci.nii.ac.jp/naid/', '', $link->url);
				$ids[] = $id;
			}
			/*
			if ($link->anchor == 'PDF')
			{
				$keys[] = 'pdf';
			
				$pdf = $link->url ;
				
				if ($guid == '')
				{
					$guid = $link->url;
				}
			
				if (preg_match('/wenjianming=(?<pdf>.*)&/Uu', $pdf, $m))
				{
					$pdf = 'http://www.plantsystematics.com/qikan/manage/wenzhang/' . $m['pdf'] . '.pdf';
				}
			
				$values[] = '"' . $pdf . '"';
			}*/
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

echo join(",\n", $ids) . "\n";


?>