<?php
/**
* 
* Авторизация, логинизация и прочая зация
* 
**/

$action = $_GET['action'];
$registered = '';
$message = '';
$a = new achievement();

// Не, ну просто так мы сюда не попадаем, а большинство действий записываются в историю в базу, так что первы делом получаем историю, если мы конечно залогинены
if (isset($_POST) && isset($cemail) && isset($clogin)) {
	$h = $db->query("SELECT `history` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'");
	$history = json_decode($h[0]['history'], TRUE); // Декодим историю в ассоциативный массив
}

/**
* 
* Регистрация
* 
**/
if ($action == 'reg' && isset($_POST['nick'])) {

	// Нас пригласили?
	if (isset($_GET['referrer'])) {
		// Нас пригласили.
		$ref = explode('_', base64_decode($_GET['referrer']));
		$refid = $db->escape($ref[0]);
		$refnick = $db->escape($ref[1]);
		$q = "SELECT * FROM `ololousers` WHERE `id` = $refid AND `nick` = '$refnick'";
		$r = $db->query($q);

		if (isset($r[0]['id']))
			$referrer = $r[0]['id'];

		else $referrer = 1; // Если пригласившего не существует, то пригласивший - Харон (это для хитрецов, которые сами себя будут приглашать)
	}

	if (!isset($referrer)) $referrer = 1; // Если нас не приглашали, то пригласивший - Харон =)

	// Защищаемся от инъекций и записываем в базу нового игрока
	$email = $db->escape($_POST['email']);
	$nick = $db->escape($_POST['nick']);
	$pw = $db->escape($_POST['pw']);
	$history = json_encode(array('created' => time()));
	$q = "INSERT INTO `ololousers` (`nick`, `email`, `pw`, `history`, `referrer`) VALUES ('$nick', '$email', MD5('$pw'), '$history', '$referrer')";
	$answer = $db->query($q);

	// Обрабатываем ошибки
	if (strpos($answer, 'Duplicate entry') !== FALSE){

		if (strpos($answer, 'for key \'email') !== FALSE)
			$registered = 'Вы не можете зарегистрировать несколько аккаунтов на один адрес электронной почты';

		if (strpos($answer, 'for key \'nick') !== FALSE)
			$registered = 'Такое имя уже используется';

	} else {
		// Проверяем регистрацию, получаем ачивки
		$q = "SELECT `id` FROM `ololousers` WHERE `nick` = '$nick' AND `email` = '$email'";
		$r = $db->query($q);
		$a->earn($r[0]['id'], 15);

		if ($referrer != 1) $a->earn($r[0]['id'], 8);
		if ($referrer == 1) $a->earn($r[0]['id'], 14);
		$registered = 'Регистрация прошла успешно';
	}

/**
* 
* Авторизация
* 
**/
} else if ($action == 'log' && isset($_POST['login'])) {
	// Защищаемся, проверяем валидность данных
	$login = $db->escape($_POST['login']);
	$pw = $db->escape($_POST['pw']);
	$q = "SELECT `id`, `email`, `nick` FROM `ololousers` WHERE (`nick` = '$login' OR `email` = '$login') AND `pw` = MD5('$pw')";
	$answer = $db->query($q);

	if ($answer === NULL) $registered = 'Не найдено такой комбинации логина/почты и пароля'; // Нутыпонил

	else {
		// Запихиваем данные в сессию и прыгаем в ЛК
		$_SESSION['id'] = $answer[0]['id'];
		$_SESSION['login'] = $answer[0]['nick'];
		$_SESSION['email'] = $answer[0]['email'];
		header("Location: /lk");
	}

/**
* 
* Логофф
* 
**/
} else if ($action == 'off') {
	// Очищаем куки (для упорышей) и переменные сессии
	setcookie('login', NULL);
	unset($_SESSION['login']);
	unset($_SESSION['email']);
	header("Location: /");

/**
* 
* Смена пароля
* 
**/
} else if ($action == 'changepw' && isset($_POST['oldpw']) && isset($_POST['newpw'])) {
	// Проверяем совпадение старого пароля, если все хорошо, пишем новый пароль
	$q = "SELECT IF(MD5('{$_POST['oldpw']}') = `pw`, 1, 0) as `pass` FROM `ololousers` WHERE `id` = $cid";
	$r = $db->query($q);
	$pass = $r[0]['pass'];

	if($pass) {
		// Ну и конечно же, пишем в историю
		$history['changedPw'][] = time();
		$h = json_encode($history);
		$q = "UPDATE `ololousers` SET `pw` = MD5('{$_POST['newpw']}'), `history` = '$h' WHERE `id` = $cid";
		$r = $db->query($q);

		if($r) $message = "Пароль успешно изменен";

		else $message = "something broken";

	} else {
		$message = "Неверно указан старый пароль";
	}

/**
* 
* Привязка Steam
* 
**/
} else if ($action == 'steambind' && isset($_POST['token'])) {
	$q = "SELECT `id`, `steamid`, `exp` FROM `ololousers` WHERE `id` = $cid";
	$r = $db->query($q);

	if (!$r[0]['steamid']) {
		// Если привязки нет, то получаем инфу из uLogin и смотрим, привязан ли этот акк к кому-нибудь еще
		$exp = (int)$r[0]['exp'];
		$id = $r[0]['id'];
		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
		$steamUser = json_decode($s, true);
		$q = "SELECT count(*) as `c` FROM `ololousers` WHERE `steamid` = '{$steamUser['uid']}'";
		$c = $db->query($q);

		if ((int)$c[0]['c'] == 0) {

			if ($steamUser != '') {
				// Если акк ни к кому не привязан, и авторизация в Steam удалась, то привязываемся и пишем в историю
				$history['steamBindingSet'][$steamUser['uid']] = time();
				$h = json_encode($history);
				$exp += 500;
				$q = "UPDATE `ololousers` SET `steamid` = '{$steamUser['uid']}', `history` = '$h', `exp` = $exp WHERE `id` = $cid";
				$r = $db->query($q);

				if($r) $message = "Привязка прошла успешно";

				else $message = "something broken";
				$a->earn($id,1);

			} else $message = "Сервис uLogin вернул пустой ID, мы не знаем, почему."; // Shit happens

		} else {
			// Нутыпонил
			$message = "Этот аккаунт Steam уже привязан к другой учетной записи.";
			unset($steamUser);
		}

	} else $message = "К вашей учетной записи уже привязан SteamID, сначала следует его отвязать";

/**
* 
* Отвязка Steam
* 
**/
} else if ($action == 'steambind' && isset($_POST['unbindID'])) {
	$q = "SELECT `id`, `steamid`, `exp` FROM `ololousers` WHERE `id` = $cid";
	$r = $db->query($q);

	if ($r[0]['steamid'] == $_POST['unbindID']) {
		// Если мы действительно хотим отвязать тот айдишник, который привязан, то все хорошо
		$exp = $r[0]['exp'];
		$id = $r[0]['id'];
		$history['steamBindingBroken'][ $_POST['unbindID'] ] = time();
		$h = json_encode($history);
		$exp -= 500;
		$q = "UPDATE `ololousers` SET `steamid` = NULL, `history` = '$h', `exp` = $exp WHERE `id` = $cid";
		$r = $db->query($q);

		if($r) $message = "Аккаунт Steam успешно отвязан";

		else $message = "something broken"; // Shit happens sometimes again
		$a->earn($id,2);
	// Ну это уже против утырков
	} else $message = "По какой-то причине привязанный к вашей учетной записи аккаунт Steam отличается от того, который вы пытаетесь отвязать";

/**
* 
* Настройки приватности
* 
**/
} else if ($action == 'privacy') {
	$privacy = 0;
	foreach ($_POST as $value => $status) {
		$privacy += (int)$value;
	}
	$q = "UPDATE `ololousers` SET `privacy` = '$privacy' WHERE `id` = {$user->info['id']}";
	$r = $db->query($q);
	// var_dump($q);

}
?>