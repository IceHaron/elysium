<?php

REQUIRE_ONCE('settings.php');
REQUIRE_ONCE('php/functions.php'); // самопальные функции
// Подключаем классы...
REQUIRE_ONCE('php/classes/db.php'); // ...для работы с базой
REQUIRE_ONCE('php/classes/achievement.php'); // ...для работы с ачивками
$db = new db(); // ...создаем экземпляр
$achievement = new achievement(); // ...создаем экземпляр

$secret_key = 'CKgFLBA8HKuy3tWh'; // ключ доступа к обработчику
$username = $db->escape(strip_tags($_GET['nick']));
$token = $db->escape($_GET['hash']);

if($token == md5(md5($username.$secret_key.'mcrate'))) {
	$q = "SELECT `id` FROM `ololousers` WHERE `mcname` = '$username'";
	$r = $db->query($q);
	
	if (!count($r)) die("Error: Bad login");
	else $userid = $r[0]['id'];

	$bonus = giftForVoting($userid, 1, 'Голос на mcrate.su');
	
	if ($bonus) echo 'Success';
	else echo "Shit happened";
} else die("Error: Bad hash");

//Конец скрипта.


?>