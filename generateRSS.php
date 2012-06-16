<?php

require 'php-sdk/src/facebook.php';
include realpath($_SERVER['DOCUMENT_ROOT']).'/db.php';

//header('P3P: CP="CAO PSA OUR"');
//header('Content-type: application/json');

$facebook = new Facebook(array(
  'appId'  => '319544974795592',
  'secret' => '24e6ef1bd7bc0de78835c2ee79546be2',
));

$user = $facebook->getUser();
if ($user) {
	try{
		$uid = $facebook->getUser();

		$since_dt = new DateTime();
		$since = $since_dt->getTimestamp();
		$user_profile = $facebook->api("/$uid",'GET');
	    //print_r($user_profile);

		$photos_tagged = $facebook->api(array('method' => 'fql.query', 'query' => "SELECT object_id, pid, src_big, caption, like_info, comment_info, modified FROM photo WHERE object_id IN (SELECT object_id FROM photo_tag WHERE subject = $uid AND created > $since)"));
		$photos_albums = $facebook->api(array('method' => 'fql.query', 'query' => "SELECT object_id, pid, src_big, caption, like_info, comment_info, modified FROM photo WHERE aid IN (SELECT aid FROM album WHERE owner = $uid AND (created > $since OR modified > $since)) AND (created > $since)"));
	
		$photos = array_merge((array)$photos_albums, (array)$photos_tagged);
		$photos = array_map("unserialize", array_unique(array_map("serialize", $photos))); // remove duplicates

		foreach ( $photos as $key => $value ){
			$photos[$key]['like_count'] = $photos[$key]['like_info']['like_count'];
			$photos[$key]['comment_count'] = $photos[$key]['comment_info']['comment_count'];
			unset($photos[$key]['like_info']);
			unset($photos[$key]['comment_info']);
		}

		function photo_compare( $a, $b ){ // compare, needed for sort
			return strcmp( $b['like_count'], $a['like_count'] );
		}

		usort( $photos, 'photo_compare' );

		$photo_comments = array();
		$max_photos = count($photos) > 10 ? 10 : count($photos);
		for ($i = 0; $i < $max_photos; $i++) {
			$object_id = $photos[$i]['object_id'];
			echo '<img src="'.$photos[$i]['src_big'].'" />Caption: '.$photos[$i]['caption'].' Likes: '.$photos[$i]['like_count'].' Comments: '.$photos[$i]['comment_count'].' Modified: '.$photos[$i]['modified'].'<br />';
		}
	}
	catch (FacebookApiException $e) {
		echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
		$user = null;
	}
}
else{
	echo 'Token error';	
}

/*

function insertRSS($uid,$rss){
	$sql = "UPDATE `slideshow` SET 'rss' = '$rss' WHERE `uid` = '$uid';";
	$result = mysql_query($sql) or die ("Update failed: ".mysql_error());
	if (mysql_affected_rows() == 0){
		$sql = "INSERT INTO `slideshow` (`uid`,`rss`) VALUES ('$uid','$rss');";
		$result = mysql_query($sql) or die ("Insert failed: ".mysql_error());
	}
	return;
}

function getRSS($uid){
	$sql = "SELECT `uid`,`rss` FROM `slideshow` WHERE `uid`='$uid'";
	$result = mysql_query($sql) or die ("Select failed: ".mysql_error());
	return $result;
}

//*/

?>