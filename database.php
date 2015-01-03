<?php

$db_host = 'localhost:3306';
$db_name = 'dragonbusiness';
$db_user = 'minecraft';
$db_pass = 'minecraftpass';

$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);