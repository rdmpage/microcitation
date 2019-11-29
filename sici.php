<?php

// Compute SICI


// Based on https://github.com/jprante/elasticsearch-analysis-standardnumber/blob/master/src/main/java/org/xbib/elasticsearch/common/standardnumber/SICI.java
function checksum($sici) {
	$ALPHABET = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ#";

    $modulus = strlen($ALPHABET);
    
	$len = strlen($sici);
	$sum = 0;
	for ($i = 0; $i < $len; $i++) 
	{
		$val = strpos($ALPHABET, $sici[$i]);
		if ($val === false) {
			$val = -1;
		}
		$sum += $val *  ($i %2 == 0 ? 1 : 3);
	}
    $chk = $modulus - $sum % $modulus;    
    $check = $chk > 35 ? '#' : $ALPHABET[$chk];
	return $check;
}




$sici_string = '0095-4403(199502/03)21:3<12:WATIIB>2.0.TX;2-';
//$sici_string = '0015-6914(19960101)157:1<62:KTSW>2.0.TX;2-';

$sici_string .= checksum($sici_string);

echo $sici_string . "\n";



?>