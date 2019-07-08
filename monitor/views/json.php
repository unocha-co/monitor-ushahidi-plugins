<?php 
if (!empty($_GET['callback']))
	echo $_GET['callback'] . '(' . $json . ');';
else
	echo $json; 
?>
