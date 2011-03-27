<?php
/* 
 * twitterPull.php
 * 
 * Pull latest feeds from Twitter API
 *  
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */
require_once 'variables.php';
require_once 'utils.php';
require_once 'classes/Litt.php';
require_once 'classes/User.php';

if (!function_exists(json_decode)) exit("PHP 5.2 required for JSON.");

if (!$sql){
	$sql = openSQL();
	mysql_select_db("litter", $sql);
}

$multiCurl = curl_multi_init();
foreach ($USER_LIST as $u){
	$feedUrl=("http://api.twitter.com/1/statuses/user_timeline.json?screen_name=".$u);
	$ch = "ch".$u;
	$$ch = curl_init();	
	
	curl_setopt($$ch, CURLOPT_URL, $feedUrl);
	curl_setopt($$ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($$ch, CURLOPT_TIMEOUT, 8);
	
	curl_multi_add_handle($multiCurl,$$ch);
}

$running=null;

do {
    usleep(10000);
    curl_multi_exec($multiCurl,$running);
} while ($running > 0);

foreach ($USER_LIST as $u){
	$ch = "ch".$u;
	$feed_content = curl_multi_getcontent($$ch);
	curl_multi_remove_handle($multiCurl, $$ch);
	
	$tweets = json_decode($feed_content, TRUE);
	
	if ($tweets["error"] == "Rate limit exceeded. Clients may not make more than 150 requests per hour.") // Rate limit has been reached, cancel import
		return;
	else if ($tweets["error"])
		continue;
	
	
	$userInfo = $tweets[0]["user"];
	$user = User::importFromTwitter($userInfo);
	$user->saveToDB($sql);
	
	foreach ($tweets as $tweet){
		if ($tweet["in_reply_to_user_id"]) continue;  // ignore any @reply tweets
		$litt = Litt::importFromTwitter($tweet);
		$litt->setUser($user);
		$litt->saveToMasterDB($sql);
	}
}

curl_multi_close($multiCurl);



?>