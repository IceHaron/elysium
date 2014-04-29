<?php
$action = $_GET['action'];
$registered = '';
if ($action == 'reg' && isset($_POST['nick'])) {
	$email = $db->escape($_POST['email']);
	$nick = $db->escape($_POST['nick']);
	$pw = $db->escape($_POST['pw']);
	$history = json_encode(array('created' => time()));
	$q = "INSERT INTO `ololousers` (`nick`, `email`, `pw`, `history`) VALUES ('$nick', '$email', MD5('$pw'), '$history')";
	$answer = $db->query($q);
	if (strpos($answer, 'Duplicate entry') !== FALSE){
		if (strpos($answer, 'for key \'email') !== FALSE)
			$registered = 'Вы не можете зарегистрировать несколько аккаунтов на один адрес электронной почты';
		if (strpos($answer, 'for key \'nick') !== FALSE)
			$registered = 'Такое имя уже используется';
	} else $registered = 'Регистрация прошла успешно';
} else if ($action == 'log' && isset($_POST['login'])) {
	$login = $db->escape($_POST['login']);
	$pw = $db->escape($_POST['pw']);
	$q = "SELECT `nick` FROM `ololousers` WHERE (`nick` = '$login' OR `email` = '$login') AND `pw` = MD5('$pw')";
	$answer = $db->query($q);
	if ($answer === NULL) $registered = 'Не найдено такой комбинации логина/почты и пароля';
	else {
		setcookie('login', $answer[0]['nick']);
		header("Location: /");
	}
} else if ($action == 'off') {
	setcookie('login', NULL);
	header("Location: /");
}