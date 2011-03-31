<?php 
/*
 * settings.php
 * 
 * Settings page for logged in user.
 *  
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */

	// Save settings on form submittion, then bounce back home.
	if ($_POST["formSubmited"]){
		require_once 'utils.php';
		require_once 'classes/User.php';
		
		$sql = openSQL();
		mysql_select_db("litter", $sql);
		
		$web = $_POST["website"];
		
		if ($web != "" && substr($web, 0,7) != "http://")
			$web = "http://".$web;
		
		$me = new User($_COOKIE['litterID']);
   		$me->loadInfoFromDB();
		$settings = array (	
						"realName" => $_POST["rname"],
						"toy" => $_POST["toy"],
						"spot" => $_POST["spot"],
						"bio" => $_POST["bio"],
						"location" => $_POST["location"],
						"website" => $web
			);
		$me->setSettings($settings);
		$me->saveToDB($sql,$_COOKIE['litterID']);
		
		mysql_close($sql);
		
		header( 'Location: ./' ) ;
	}
	$title = "User Settings";
	require 'pages/header.php'; 
	require 'pages/id_tag.php';
	
	
?>

<div id="settings">
    <h2>Settings</h2>
	<form action="settings.php" method="post">
		<p>Real Name: <input type="text" name="rname" value="<?php echo($me->getRealName());?>" /><br />
		Bio: <input type="text" name="bio" value="<?php echo($me->getBio());?>" /><br />
		Location: <input type="text" name="location" value="<?php echo($me->getLocation());?>" /><br />
		Favorite Toy: <input type="text" name="toy" value="<?php echo($me->getToy());?>" /><br />
		Favorite Spot: <input type="text" name="spot" value="<?php echo($me->getSpot());?>" /><br />
		Website: <input type="text" name="website" value="<?php echo($me->getWebsite());?>" /><br />
		<input type="hidden" name="formSubmited" value="true" />
		<button type="submit" >save</button>
		<button type="button" onclick="window.location.href='./'">cancel</button></p>
	</form>
</div>
 
<?php require 'pages/footer.php'; ?>