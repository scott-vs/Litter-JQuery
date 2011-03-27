<?php 
/*
 * home.php
 * 
 * The main page of Litter that displays most recently made litts.
 * 
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */

	require_once 'variables.php';
	require_once 'classes/Litt.php';
	require 'demoScript.php';
    $title = "Litter Home Page";
	require 'pages/header.php';
	
	$sql = openSQL();
	mysql_select_db("litter", $sql);
?>
	
	<?php require 'pages/id_tag.php'; ?>
	<div id="side_bar">
    	<div id="user_pane"> 
    		<?php echo($me->printUserPane()); ?>
    	</div>
    	<div id="user_list"> 
    		<?php require_once 'pages/topUsers.php';?>
    	</div>
    </div>
    <div id="home">
	    <div id="the_scoop">
		    <h2>What's the scoop?</h2>
		    <div id="reply_to" class="hidden_info"></div>
		    <textarea id="txt_box" rows="2" cols="20" ></textarea> <br/>
		    <span id="tiny_text">140 characters left.</span>
		    <input id="new_litt" type="button" value="Litt it!" />
	    </div>
	    <div id="litt_space">
	    <?php 
	    		
		   	
	    
	   		$litt = "litt_".$_COOKIE['litterID'];
	   		$user = "users_".$_COOKIE['litterID'];
	   		$result = mysql_query("SELECT * FROM $litt, $user WHERE $litt.user_id = $user.user_id ORDER BY litt_id DESC LIMIT 0, 20", $sql);
			$isFirst = TRUE;
	   		while ($row = mysql_fetch_array($result)) {
	   			$l = Litt::importFromDB($row);
	   			if ($isFirst){
	   				$littID = $l->getID();
	   				$topID = $littID;
	   				echo("<div id='top_litt' class='hidden_info' >$littID</div>");
	   				$isFirst = FALSE;
	   			}
				echo($l->printLitt());
			}
			$littID = $l->getID();
			echo("<div id='bottom_litt' class='hidden_info'>$littID</div>");
	    ?>
	    </div>
	    <a id="loadNext10" href="javascript:void(0)">Load Next 10</a>
   	</div>
   	<div id="on_deck" class="hidden_info"></div>
   	
<?php require 'pages/footer.php'; ?>
