<?php

header('content-type:text/html;charset=utf-8;');
require 'database.php';

//---------------NOTES----------------
//REQUIRES THE "nithutvalg" DATABASE!

//---------------$db file-------------
//A valid $db (DBO-Object) should be created like this:
//
//$db_host = 'localhost:3306';
//$db_name = 'users';
//$db_user = 'root';
//$db_pass = 'password';
//
//$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_pass);
//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//


//----------GLOBAL IF STATEMENTS
if (isset($_GET['logout'])){
	logoutUser('index.php');
	echo "<br/>User logged out!";
}


//---------------------------FUNCTIONS FOLLOWING!----------------------------------------------------------------//

//Login a specified user from a userdb, and redirecting to redirect   (Userid stored in _SESSION['id'])
function login($db, $username, $password, $redirect){
	$stmt = $db->prepare('SELECT * FROM users WHERE username=:username');
	$stmt->bindParam(':username', $username, PDO::PARAM_STR);
	try{
		$stmt->execute();
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
	$userinfo = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if (trim((string)mcrypt_decrypt(MCRYPT_RIJNDAEL_128, 'encrKey12', $userinfo['password'], MCRYPT_MODE_CBC)) === (string)$password){
		$_SESSION['id'] = $userinfo['id'];
		header("Location: $redirect");
	} else {
		echo "Incorrect username or password!";
	}
}

//Logout a user by unsetting the _SESSION['id'], and redirecting to redirect
function logoutUser($redirect){
	unset($_SESSION['id']);
	header("Location: $redirect");
}

//Registering a user on a specified userdb, takes: db, username, password. (returns true for successful register. else false)
function addUser($db, $newusername, $newpassword, $newname, $newsurname, $newemail, $newstudentnr){
	
	//encrypt
	$encrPassword = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, 'encrKey12', $newpassword, MCRYPT_MODE_CBC);
	
	$stmt = $db->prepare("INSERT INTO users(username, password, name, surname, epost, studentnr) VALUES(:getnewuser, :getnewpassword, :getnewname, :getnewsurname, :getnewemail, :getnewstudentnr)");
	$stmt->bindParam(':getnewuser', $newusername);
	$stmt->bindParam(':getnewpassword', $encrPassword);
	$stmt->bindParam(':getnewname', $newname);
	$stmt->bindParam(':getnewsurname', $newsurname);
	$stmt->bindParam(':getnewemail', $newemail);
	$stmt->bindParam(':getnewstudentnr', $newstudentnr);
	try{
		@$stmt->execute();
		return true;
	}
	catch(PDOException $e){
		if($e->getCode() == 23000){
			echo "<br/>You are already registered!";
		} else {
			echo $e->getMessage();
			return false;
		}
	}
}

//Registering a user on a specified utvalgdb, takes: db, name, description, shortdescription. (returns true for successful register. else false)
function addUtvalg($db, $name, $description, $shortdescription){
	$stmt = $db->prepare("INSERT INTO utvalg(name, description, shortdescription) VALUES(:name, :description, :shortdesvription)");
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':description', $description);
	$stmt->bindParam(':shortdescription', $shortdescription);
	
	try{
		@$stmt->execute();
		return true;
	}
	catch(PDOException $e){
		return false;
	}
}

//Registers a user to an utvalg in the connection table. takes: db, userid, utvalgid. (returns true for successful register. else false)
function addUserToUtvalg($db, $userid, $utvalgid){

	$stmt = $db->prepare("INSERT INTO user_utvalg(utvalg_id, users_id) VALUES(:utvalgid, :userid)");
	$stmt->bindParam(':utvalgid', $utvalgid);
	$stmt->bindParam(':userid', $userid);
	
	try{
		@$stmt->execute();
		return true;
	}
	catch(PDOException $e){
		return false;
	}
}

//Registers a user to an utvalg in the connection table. takes: db, userid, utvalgid. (returns true for successful register. else false)
function removeUserFromUtvalg($db, $userid, $utvalgid){

	$stmt = $db->prepare("DELETE FROM user_utvalg WHERE users_id=':userid' AND utvalg_id=':utvalgid'");
	$stmt->bindParam(':utvalgid', $utvalgid);
	$stmt->bindParam(':userid', $userid);
	
	try{
		@$stmt->execute();
		return true;
	}
	catch(PDOException $e){
		return false;
	}
}

//Returns a 2D Array of the 'users'-table Fields(username, name, surname, epost, studentnr). Eks: Array['0']['username']
function getUserList($db){
	$stmt = $db->prepare("SELECT username,name,surname,epost,studentnr FROM users");
	try {
		$stmt->execute();
	}
	catch(PDOException $e){
		
	}
	
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return $list;
}

//Returns a 2D Array of the 'users'-table Fields(username, name, surname, epost, studentnr). Eks: Array['0']['username']
function getUtvalgList($db){
	$stmt = $db->prepare("SELECT name,description,shortdescription FROM utvalg");
	try {
		$stmt->execute();
	}
	catch(PDOException $e){
		
	}
	
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return $list;
}

//Returns a 2D Array of the 'users'-table that is registered to of the chosen utvalg. Eks: Array['0']['username']
function getUserListInUtvalg($db, $utvalgid){
	$stmt = $db->prepare("SELECT username,name,surname,epost,studentnr FROM users LEFT JOIN user_utvalg ON user_utvalg.users_id=users.id WHERE user_utvalg.utvalg_id = ':utvalgid'");
	$stmt->bindParam(':utvalgid', $utvalgid);
	try {
		$stmt->execute();
	}
	catch(PDOException $e){
		
	}
	
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return $list;
}

function listToHTML($db, $list){
	$line = "";
	
	foreach ($list as $item) {
		$line = $item['id'].' - '.$item['text'].'</br>'.' - ANSWER: '.$item['mod_comment'];
		echo "
			- QUESTION: $line
			</br>
			</br>
		";
	}
}

//Gets the username and returns it as a string, based on the provided userdb and id
function getUserName($db, $id){
	$stmt = $db->prepare('SELECT * FROM users WHERE id=:id');
	$stmt->bindParam(':id', $id, PDO::PARAM_STR);
	try{
		$stmt->execute();
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
	$userinfo = $stmt->fetch(PDO::FETCH_ASSOC);
	return $userinfo['username'];
}

//Gets the accesslevel of the provided userid
function getAccess($db, $userid){
	$stmt = $db->prepare('SELECT access FROM users WHERE id=:id');
	$stmt->bindParam(':id', $userid);
	try{
		$stmt->execute();
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
	$userinfo = $stmt->fetch(PDO::FETCH_ASSOC);
	return $userinfo['access'];
}

//Draws a simple Login-Form in HTML Posting info to POST
function drawFormLogin($formDescription, $usernameText, $passwordText){
	echo "
		<form action=\"index.php\" method=\"POST\">
			$formDescription<br/><br/>
			$usernameText<br><input type=\"text\" name=\"loginusername\"/><br />
			$passwordText<br><input type=\"password\" name=\"loginpassword\"/><br />
			<input type=\"submit\" value=\"Submit\"/>
		</form>
	";
}

//Draws a simple Registration-Form in HTML Posting info to POST
function drawFormRegister($CSSid, $formDescription, $usernameText, $passwordText, $nametext, $surnametext, $emailtext, $studentnrtext){
	echo "
		<form id=$CSSid action=\"index.php\" method=\"POST\">
			$formDescription<br/><br/>
			$usernameText<br><input type=\"text\" name=\"newusername\"/><br />
			$passwordText<br><input type=\"password\" name=\"newpassword\"/><br />
			$nametext<br><input type=\"text\" name=\"newname\"/><br />
			$surnametext<br><input type=\"text\" name=\"newsurname\"/><br />
			$emailtext<br><input type=\"text\" name=\"newemail\"/><br />
			$studentnrtext<br><input type=\"text\" name=\"newstudentnr\"/><br />
			<input type=\"submit\" value=\"Submit\"/>
		</form>
	";
}

//Gets all the utvalg, and displays them through a defined div-tag, using the CSS-tag: $class.
function drawAllUtvalgThumbnail($db, $class){
	
	$list = getUtvalgList($db);
	
	$line = "";
	
	foreach ($list as $item) {
		$name = $item['name'];
		$descr = $item['shortdescription'];
		echo "
			<a href='utvalg.php?utvalg=$name'>
			<div class=$class>
				<!--Tittel-->
				<h1>$name</h1>
				
				<!--Description-->
				<br>
				<p>
					$descr
				</p>
			</div>
			</a>
	";
	}
}

//Draws the HTML header
function drawHeader(){
	echo '
		<div class ="Banner">
			<img class ="logo "src="westerdal.png" alt="Westerdals Logo">
		</div>
	';
}

//Draws a working logout button
function drawLogoutBtn(){
	if (isset($_SESSION['id'])){
		echo '<form action="index.php" method="GET"><input type="submit" value="Logout" name="logout"/></form>';
	}
}
?>

