<?php

REQUIRE_ONCE('settings.php');
REQUIRE_ONCE('php/functions.php'); // самопальные функции
// Подключаем классы...
REQUIRE_ONCE('php/classes/db.php'); // ...для работы с базой
REQUIRE_ONCE('php/classes/achievement.php'); // ...для работы с ачивками
$db = new db(); // ...создаем экземпляр
$achievement = new achievement(); // ...создаем экземпляр

$key = 'elysiumololokey'; // ключ доступа к обработчику
if ($_GET['key']!=md5($key) || $_GET['nickname']=='') {
	header("HTTP/1.1 404 Not Found");
	exit;
}

$username = htmlspecialchars($_GET['nickname']); // Передает Имя проголосовавшего за проект

//Далее идёт код отвечающий за выдачу поощрений!

$q = "SELECT `id` FROM `ololousers` WHERE `mcname` = '$username'";
$r = $db->query($q);

if (!count($r)) {
	header("HTTP/1.1 404 Not Found");
	exit;
} else $userid = $r[0]['id'];

$bonus = giftForVoting($userid, 4, 'Голос на want2vote.com');

if ($bonus) echo 'ok';
else {
	header("HTTP/1.1 404 Not Found");
	exit;
}

//Конец скрипта.


?>