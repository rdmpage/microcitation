<?

$filename = "files.txt";

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));

	echo 'UPDATE publications SET internetarchive=NULL WHERE guid LIKE "%' . $line . '";' . "\n";

}

?>