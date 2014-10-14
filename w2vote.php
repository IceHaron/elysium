<?php

REQUIRE_ONCE('settings.php');
REQUIRE_ONCE('php/functions.php'); // самопальные функции
// Подключаем классы...
REQUIRE_ONCE('php/classes/db.php'); // ...для работы с базой
$db = new db(); // ...создаем экземпляр

$gift = 1000; // Количество денег, которое получит игрок за голосование.

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

$bonus = giveBonus($userid, $gift, 'vote', 'Голос на want2vote.com');
$coupon = giveCoupon($userid, 'votediscount', 0.1);

if ($bonus && $coupon) echo 'ok';
else {
	header("HTTP/1.1 404 Not Found");
	exit;
}

//Конец скрипта.


?>