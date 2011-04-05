<?php
/*
 * topUsers.php
 * 
 * Box on the side of the main page that displays the top 20 users of
 * Litter.
 * 
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */

if (!$sql){
	$sql = openSQL();
   	mysql_select_db('litter', $sql);
 }

$littTbl = 'litt_'.$_COOKIE['litterID'];
$userTbl = 'users_'.$_COOKIE['litterID'];
$q = "SELECT *, COUNT( DISTINCT litt_id ) AS ltts FROM $littTbl, $userTbl WHERE $littTbl.user_id = $userTbl.user_id GROUP BY $littTbl.user_id ORDER BY ltts DESC LIMIT 0, 20 ";
$result = mysql_query($q, $sql);

echo('Top Users:<br/>');
$count = 0;
while ($row = mysql_fetch_array($result)){
	$u = User::importFromDB($row);
	
	echo('<span class="user_picture"><img src="'.$u->getImageUrl().'" alt="someone\'s picture" /><input type="hidden" value="'.$u->getID().'" /></span>');
	$count++;
	if ($count % 4 == 0)
		echo('<br/>');
}

?>