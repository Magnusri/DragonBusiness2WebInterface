<?php

header('content-type:text/html;charset=utf-8;');
require 'database.php';

//---------------NOTES----------------
//REQUIRES THE "dragonbusiness" DATABASE!

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
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] === ""){
    header('Location: '.'index.php');
    die("You need to log in to access this page!");
}

//---------------------------FUNCTIONS FOLLOWING!----------------------------------------------------------------//

function getTop10Companies($db){
	$stmt = $db->prepare("
    select * 
    from company 
    ORDER BY company_value*1 DESC
    LIMIT 10;
");
	try {
		$stmt->execute();
	}
	catch(PDOException $e){
		
	}
	
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return $list;
}

function printTop10Companies($db){
    $list = getTop10Companies($db);
    

    echo "<h1>Top 10 companies</h1>";

    $counter = 1;
    foreach ($list as $item) {

        $company_id = $item['company_id'];
        $company_name = $item['company_name'];
        $company_value = $item['company_value'];
        $company_info = $item['company_info'];

        echo "<div class='modreq'>
            <p class='question'>$counter. $company_name - $company_value$</p>
        </div>";
        
        $counter++;
    }
}

?>

