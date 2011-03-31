<?php
/*
 * 	createNewSession.php
 * 
 * Sets up a brand new Litter environment specifically for the user
 * to be expired and deleted after two hours.
 * 
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 *
 */
require_once 'variables.php';
require_once 'utils.php';
require_once 'classes/Litt.php';
require_once 'classes/User.php';
require_once 'classes/SqlTable.php';

$sql = openSQL();
		
// Create the core Litter DB if one doesn't exsist already
if (mysql_query("CREATE DATABASE litter",$sql)) {
	mysql_select_db("litter", $sql);
	
	$tolkens = new SqlTable("tolkens");
	$tolkens->addCol("litter_id", "INT", FALSE, TRUE);
	$tolkens->addCol("expires", "BIGINT");
	$tolkens->createTable($sql);
	
	$masterLitt = new SqlTable("litt", "master");
	Litt::tableStruct($masterLitt);
	$masterLitt->createTable($sql);
	
	$masterUsers = new SqlTable("users", "master");
	User::tableStruct($masterUsers);
	$masterUsers->createTable($sql);
} 

require_once 'twitterPull.php';

// Create a unique LitterID for this demo session.
mysql_select_db("litter", $sql);

$id = rand(100000,999999);
$inTwoHours = time() + 60 * 60 * 2;

setcookie('litterID', $id, $inTwoHours); 
setcookie("litterPhase", 0, $inTwoHours);
mysql_query("INSERT INTO tolkens (litter_id, expires) VALUES (".$id.", ".$inTwoHours.")",$sql);
$_SESSION['expires'] = $inTwoHours;

// Create session-specific Litter tables
mysql_select_db("litter", $sql);

$littTable = new SqlTable("litt", $id);
Litt::tableStruct($littTable);
$littTable->createTable($sql);

$userTable = new SqlTable("users", $id);
User::tableStruct($userTable);
$userTable->createTable($sql);

// Copy Master Litt table into session table, saving the most 
// recent 25 to be added later on. 
$query = "INSERT INTO litt_".$id." 
				SELECT * FROM litt_master
				WHERE litt_id <= 
					(SELECT litt_id FROM litt_master
					 ORDER BY litt_id DESC 
					 LIMIT 25 , 1)
			";
mysql_query($query, $sql);

// Copy Master User table into session table, inserting random
// favorite cat toys and resting spots.
mysql_query("INSERT INTO users_".$id." SELECT * FROM users_master", $sql);
$result = mysql_query("SELECT * FROM users_".$id, $sql);
while ($row = mysql_fetch_array($result))
{
	$toy = $FAV_TOYS[rand(0, count($FAV_TOYS)-1)];
	$spot = $FAV_SPOT[rand(0, count($FAV_SPOT)-1)];
	mysql_query("UPDATE users_".$id." 
				    SET toy='".$toy."', 
					    spot='".$spot."' 
				  WHERE user_id = ".$row["user_id"], $sql);
}

// Add users Tiger and Mimi to the mix.
$tiger = new User($id);
$settings = array (	"userName" => "Wild_Tiger",
					"realName" => "Tiger",
					"toy" => "empty cups",
					"spot" => "chair in front of window",
					"bgColor" => "#FFFFFF",
					"bio" => "I'm Scott VonSchilling's tabby cat.",
					"location" => "Princeton, NJ",
					"imageUrl" => "./images/tiger_normal.jpg"
			);
$tiger->setSettings($settings);
$tiger->saveToDB($sql,$id);

$mimi = new User("1234567");
$settings = array (	"userName" => "Classy_Mimi",
					"realName" => "Nekomimi",
					"toy" => "twist ties",
					"spot" => "on top of the couch",
					"bgColor" => "#FFFFFF",
					"bio" => "I am NOT Scott VonSchilling's cat. Scott VonSchilling is my human.",
					"location" => "Princeton, NJ",
					"imageUrl" => "./images/mimi_normal.jpg"
			);
$mimi->setSettings($settings);
$mimi->saveToDB($sql,$id);

mysql_close($sql); 

echo("success"); 
?>