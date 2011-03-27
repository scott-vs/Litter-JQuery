<?php
/*
 * Litt.php
 * 
 * Class that represents an individual Litt update in Litter.
 *  
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */
require_once 'classes/User.php';
require_once 'classes/BigInt.php';
class Litt {
	protected $litt_id;
	protected $text;
	protected $user_id;
	protected $reply;
	protected $user;
	
	function __construct($id){
		$this->litt_id = new BigInt($id);
	}
	
	public function setUser($user){
		$this->user_id = $user->getID();
		$this->user = $user;
	}
	
	public function getID(){
		return $this->litt_id->__toString();
	}
	
	public function saveToMasterDB($sql, $sub = "master"){
		$list = array (
						"litt_id", $this->litt_id,
						"text",sqlSafe($this->text),
						"user_id",$this->user_id,
						"reply",$this->reply
					  );
		$names = "";
		$values = "";
		$isFirst = true;
		for ($i = 0; $i < count($list); $i += 2){
			if ($isFirst)
				$isFirst = false;
			else {
				$names .= ", ";
				$values .= ", ";
			} 
			$names .= $list[$i];
			$values .= "'".$list[$i + 1]."'";
		}
		
		$insert = "INSERT INTO litt_$sub (".$names.") VALUES (".$values.")";
		mysql_query($insert);
	}
	
	public function printLitt(){
		$name = $this->user->getUserName();
		$picture = $this->user->getImageUrl();
		
		if ($this->reply != "0"){
			$replyText = $this->getReplyText();
			$title = "title='$replyText'";
		}
		else
			$title = "";
		$text = $this->fancyText();
		
		$s = "	<div class='litt' $title >
					<img src='$picture'  uid='".$this->user->getID()."'  />
					<div class='litt_top'>
						<div class='litt_username' uid='".$this->user->getID()."'  >
							$name:
						</div>
					</div>
					<div class='litt_text'>
					 $text
					</div>
					<div class='litt_reply'>
						<a href='javascript:void(0)' replyTo = '$name' littid ='l".$this->litt_id."'>reply</a>
					</div>
				</div>
			";
		return $s;
		
	}
	
	private function fancyText(){
		$s = preg_replace("/(http:\/\/[^\s]+)/", "<a href=\"$1\">$1</a>", $this->text);
		$s = preg_replace("/(@[^\s]+)/", "<span style='color:darkred;'>$1</span>", $s);
		$s = preg_replace("/(#[^\s]+)/", "<span style='color:green'>$1</span>", $s);
		return $s;
	}
	
	private function getReplyText(){
		if (!$sql){
			$sql = openSQL();
			mysql_select_db("litter", $sql);
		}
		
		$littTbl = "litt_".$_COOKIE["litterID"];
		$userTbl = "users_".$_COOKIE["litterID"];
		
		$q ="SELECT * FROM $littTbl, $userTbl WHERE $littTbl.user_id = $userTbl.user_id AND $littTbl.litt_id = ".$this->reply;
		$result = mysql_query($q,$sql);
		$row = mysql_fetch_array($result);
		$s = "@".$row["user_name"].": ".$row["text"];
		
		
		return $s;
	}
	
	public static function importFromTwitter($tweet) {
		$lit = new Litt($tweet['id_str']);
		$lit->text = $tweet['text'];
		return $lit;
	}
	
	public static function importFromDB($row){
		
		$lit = new Litt($row["litt_id"]);
		$lit->user = User::importFromDB($row);
		$lit->text = $row["text"];
		$lit->user_id = $row["user_id"];
		$lit->reply = new BigInt($row["reply"]);
		return $lit;
	}
	
	// Defining structure needed for MySQL database.
	public static function tableStruct($table){
		$table->addCol("litt_id", "BIGINT",FALSE,TRUE);
		$table->addCol("text", "MEDIUMTEXT");
		$table->addCol("user_id", "BIGINT");
		$table->addCol("reply", "BIGINT",TRUE);
	}
	
	public static function createNewLitt($user, $text, $replyTo){
		if (!$sql){
			$sql = openSQL();
			mysql_select_db("litter", $sql);
		}
		$littTbl = "litt_".$_COOKIE["litterID"];
		$q ="SELECT litt_id FROM $littTbl ORDER BY litt_id DESC LIMIT 0,1";
		$result = mysql_query($q,$sql);
		$myID = mysql_fetch_row($result);
		$myID = new BigInt($myID[0]);
		$myID->increment();
		
		$l = new Litt($myID);
		$text = stripcslashes($text);
		$l->text = $text;
		$l->reply = $replyTo;
		$l->setUser($user);
		return($l);
	}
	
}