<?php
/*
 * User.php
 * 
 * Class that represents a Litter user.
 * 
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */
require_once 'variables.php';

class User{
	protected $userId;
	protected $userName;
	protected $realName;
	protected $toy;
	protected $spot;
	protected $bgColor;
	protected $bio;
	protected $website;
	protected $location;
	protected $imageUrl;
	
	function __construct($uID){
		$this->userId = $uID;
	}
	
	function loadInfoFromDB(){
		$userTbl = "users_".$_COOKIE['litterID'];
		
		if (!$sql){
			$sql = openSQL();
			mysql_select_db("litter", $sql);
		}
		$q = "SELECT * FROM $userTbl where user_id = $this->userId";
		$result = mysql_query($q, $sql);
		$row = mysql_fetch_array($result);
		$this->import2($row);
	}
	
	protected function import2($row){
		$this->userName = $row["user_name"];
		$this->realName = $row["real_name"];
		$this->toy = $row["toy"];
		$this->spot = $row["spot"];
		$this->bgColor = $row["bg_color"];
		$this->bio = $row["bio"];
		$this->website = $row["website"];
		$this->location = $row["location"];
		$this->imageUrl = $row["image_URL"];
	}
	
	public function getID(){
		return $this->userId;
	}
	
	public function getUserName(){
		return $this->userName;
	}
	
	public function getRealName(){
		return $this->realName;
	}
	
	public function getImageUrl(){
		return $this->imageUrl;
	}
	
	public function getLargeImageUrl(){
		return str_replace("_normal", "_reasonably_small", $this->imageUrl);
	}
	
	public function getBio(){
		return $this->bio;
	}
	
	public function getLocation(){
		return $this->location;
	}
	
	public function getToy(){
		return $this->toy;
	}
	
	public function getSpot(){
		return $this->spot;
	}
	
	public function getWebsite(){
		return $this->website;
	}
	
	public function setSettings($settings){
		$keys = array_keys($settings);
		for ($x = 0; $x < count($settings); $x++){
			$name  = $keys[$x];
			$value = $settings[$name];
			$value = strip_tags($value); // prevent XSS
			$value = stripcslashes($value);
			$this->$name = $value;
		}
	}
	
	private function notNull($s){
		$s ? $s = $s : $s = "";
		return $s;
	}
	
	public function saveToDB($sql, $subName = "master"){
		mysql_select_db("litter", $sql);
		// Add New User
	    $insert = "INSERT INTO users_$subName (user_id) VALUES ('".$this->userId."')";
		mysql_query($insert,$sql);
		
		// Update Users
		$insert = "UPDATE users_$subName
		    		SET user_name = '".$this->notNull($this->userName)."',
		    		  	real_name = '".$this->notNull(sqlSafe($this->realName))."',
						bg_color = '".$this->notNull($this->bgColor)."',
						location = '".$this->notNull(sqlSafe($this->location))."',
						bio = '".$this->notNull(sqlSafe($this->bio))."',
						toy = '".$this->notNull(sqlSafe($this->toy))."',
						spot = '".$this->notNull(sqlSafe($this->spot))."',
						website = '".$this->notNull($this->website)."',
						image_URL = '".$this->notNull($this->imageUrl)."'
		           WHERE user_id = ".$this->notNull($this->userId);
		mysql_query($insert,$sql);
	}
	
	public function setFavs($toy, $spot){
		$this->toy = $toy;
		$this->spot = $spot;
	}
	
	public function printUserPane(){
		$user = $this->userName;
		$s ='<div style="text-align:center">
				'.$this->userName.'
			</div>
			<div id="user_tag">
				<div id="user_bio">
		      		 '.$this->bio.'<br />
				</div>
				<img src="'.$this->getLargeImageUrl().'" alt="user profile picture" />
			</div>
			<div id="user_deets">
		';
		$s .= $this->printUserPaneDetail("Real Name", $this->realName);
		$s .= $this->printUserPaneDetail("Location", $this->location);
		$s .= $this->printUserPaneDetail("Favorite Toy", $this->toy);
		$s .= $this->printUserPaneDetail("Favorite Spot", $this->spot);
		($this->website == "") ? $website = null : $website = '<a href="'.$this->website.'">'.$this->website.'</a><br />';
		$s .= $this->printUserPaneDetail("Website", $website);
		$s .= "</div>";
		return $s;
	}
	
	private function printUserPaneDetail($name, $value){
		$s = "";
		if ($value != null && $value != ""){
			$s .= '<div class="deet_title">'.$name.':</div>';
			$s .= '<div class="deet_value">'.$value.'</div>';
		}
		return $s;
	}
	
	public static function importFromTwitter($userInfo){
		$u = new User($userInfo["id"]);
		$u->userName = $userInfo["screen_name"];
		$u->realName = $userInfo["name"];
		$u->bgColor = $userInfo["profile_background_color"];
		$u->bio = $userInfo["description"];
		$u->website = $userInfo["url"];
		$u->location = $userInfo["location"];
		$u->imageUrl = $userInfo["profile_image_url"];
		return $u;
	}
	
	public static function importFromDB($row){
		$u = new User($row["user_id"]);
		$u->import2($row);
		return $u;
	}
	
	// Defining structure needed for MySQL database.
	public static function tableStruct($table){
		$table->addCol("user_id", "BIGINT",FALSE,TRUE);
		$table->addCol("user_name", "MEDIUMTEXT", TRUE);
		$table->addCol("real_name", "MEDIUMTEXT", TRUE);
		$table->addCol("toy", "MEDIUMTEXT", TRUE);
		$table->addCol("spot", "MEDIUMTEXT", TRUE);
		$table->addCol("bg_color", "MEDIUMTEXT", TRUE);
		$table->addCol("bio", "MEDIUMTEXT", TRUE);
		$table->addCol("website", "MEDIUMTEXT", TRUE);
		$table->addCol("location", "MEDIUMTEXT", TRUE);
		$table->addCol("image_URL", "MEDIUMTEXT", TRUE);
	}
}