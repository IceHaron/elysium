<?php
$action = $_GET['action'];
$registered = '';
$message = '';
$a = new achievement();

if (isset($_POST) && isset($cemail) && isset($clogin)) {
	$h = $db->query("SELECT `history` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'");
	$history = json_decode($h[0]['history'], TRUE);
}

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

	} else {
		$q = "SELECT `id` FROM `ololousers` WHERE `nick` = '$nick' AND `email` = '$email'";
		$r = $db->query($q);
		$a->earn($r[0]['id'], 0);
		$registered = 'Регистрация прошла успешно';
	}

} else if ($action == 'log' && isset($_POST['login'])) {
	$login = $db->escape($_POST['login']);
	$pw = $db->escape($_POST['pw']);
	$q = "SELECT `email`, `nick` FROM `ololousers` WHERE (`nick` = '$login' OR `email` = '$login') AND `pw` = MD5('$pw')";
	$answer = $db->query($q);
	if ($answer === NULL) $registered = 'Не найдено такой комбинации логина/почты и пароля';

	else {
		setcookie('login', $answer[0]['nick']);
		$_SESSION['login'] = $answer[0]['nick'];
		$_SESSION['email'] = $answer[0]['email'];
		header("Location: /lk");
	}

} else if ($action == 'off') {
	setcookie('login', NULL);
	unset($_SESSION['login']);
	unset($_SESSION['email']);
	header("Location: /");

} else if ($action == 'changepw' && isset($_POST['oldpw']) && isset($_POST['newpw'])) {
	$q = "SELECT IF(MD5('{$_POST['oldpw']}') = `pw`, 1, 0) as `pass` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'";
	$r = $db->query($q);
	$pass = $r[0]['pass'];
	if($pass) {
		$history['changedPw'][] = time();
		$h = json_encode($history);
		$q = "UPDATE `ololousers` SET `pw` = MD5('{$_POST['newpw']}'), `history` = '$h' WHERE `email` = '$cemail' AND `nick` = '$clogin'";
		$r = $db->query($q);
		if($r) $message = "Пароль успешно изменен";
		else $message = "something broken";
	} else {
		$message = "Неверно указан старый пароль";
	}

} else if ($action == 'steambind' && isset($_POST['token'])) {
	$q = "SELECT `id`, `steamid`, `exp` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'";
	$r = $db->query($q);
	if (!$r[0]['steamid']) {
		$exp = (int)$r[0]['exp'];
		$id = $r[0]['id'];
		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
		$steamUser = json_decode($s, true);
		$q = "SELECT count(*) as `c` FROM `ololousers` WHERE `steamid` = '{$steamUser['uid']}'";
		$c = $db->query($q);
		if ((int)$c[0]['c'] == 0) {
			if ($steamUser != '') {
				$history['steamBindingSet'][$steamUser['uid']] = time();
				$h = json_encode($history);
				$exp += 500;
				$q = "UPDATE `ololousers` SET `steamid` = '{$steamUser['uid']}', `history` = '$h', `exp` = $exp WHERE `email` = '$cemail' AND `nick` = '$clogin'";
				$r = $db->query($q);
				if($r) $message = "Привязка прошла успешно";
				else $message = "something broken";
				$a->earn($id,1);
			} else $message = "Сервис uLogin вернул пустой ID, мы не знаем, почему.";
		} else {
			$message = "Этот аккаунт Steam уже привязан к другой учетной записи.";
			unset($steamUser);
		}
	} else $message = "К вашей учетной записи уже привязан SteamID, сначала следует его отвязать";

} else if ($action == 'steambind' && isset($_POST['unbindID'])) {
	$q = "SELECT `id`, `steamid`, `exp` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'";
	$r = $db->query($q);
	if ($r[0]['steamid'] == $_POST['unbindID']) {
		$exp = $r[0]['exp'];
		$id = $r[0]['id'];
		$history['steamBindingBroken'][ $_POST['unbindID'] ] = time();
		$h = json_encode($history);
		$exp -= 500;
		$q = "UPDATE `ololousers` SET `steamid` = NULL, `history` = '$h', `exp` = $exp WHERE `email` = '$cemail' AND `nick` = '$clogin'";
		$r = $db->query($q);
		if($r) $message = "Аккаунт Steam успешно отвязан";
		else $message = "something broken";
		$a->earn($id,2);
	} else $message = "По какой-то причине привязанный к вашей учетной записи аккаунт Steam отличается от того, который вы пытаетесь отвязать";
}
?>