<?php
$string = "Angeböt – Teßt_Projek&t 1";

// $string = preg_replace ( "#[\ \!&,\-\.\?]#" , "_" , $string );
// $string = preg_replace ( "#[\x20\x21\x26\x2C-\x2E\x3F]#" , "_" , $string );
// $string = preg_replace ( "#[^a-zA-Z0-9_]#" , "" , $string );

$string = urlencode($string);

echo $string;
echo "<br><br>";
$string = urldecode($string);
echo $string;