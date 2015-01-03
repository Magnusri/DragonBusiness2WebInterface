<?php

require 'functions.php';

function getModreqs($db){
	$stmt = $db->prepare("
    SELECT rts_reportrts_request.id,rts_reportrts_request.text,rts_reportrts_request.mod_comment,rts_reportrts_user.name as user_name,rts_reportrts_request.mod_id as mod_name
FROM rts_reportrts_request
left join rts_reportrts_user on rts_reportrts_request.user_id = rts_reportrts_user.id
");
	try {
		$stmt->execute();
	}
	catch(PDOException $e){
		
	}
	
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return $list;
}

$list = getModreqs($db);

foreach ($list as $item) {

	$reqID = $item['id'];
	$reqQuestion = $item['text'];
	$modAnswer = $item['mod_comment'];
	$userName = $item['user_name'];
	
	echo "<div class='modreq'>
		<p class='question'>$reqQuestion</p>
	</div>";
}

getModreqs($db);