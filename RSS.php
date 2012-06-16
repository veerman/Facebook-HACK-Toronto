<?php

include realpath($_SERVER['DOCUMENT_ROOT']).'/db.php';

header('P3P: CP="CAO PSA OUR"');
//header('Content-type: application/rss+xml');

$uid = !empty($_GET['uid']) ? mysql_real_escape_string($_GET['uid']) : '0';

$row = mysql_fetch_assoc(getRSS($uid));
echo stripslashes($row['rss']);

function getRSS($uid){
	$sql = "SELECT `uid`,`rss` FROM `slideshow` WHERE `uid`='$uid'";
	$result = mysql_query($sql) or die ("Select failed: ".mysql_error());
	return $result;
}

?>