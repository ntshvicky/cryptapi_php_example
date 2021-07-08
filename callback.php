<?php
$myfile = fopen("callbackres.txt", "w") or die("Unable to open file!");
$txt = $_REQUEST;
fwrite($myfile, $txt);
fclose($myfile);
?>