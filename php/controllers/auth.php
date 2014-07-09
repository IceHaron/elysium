<?php
/**
* 
* Авторизация, логинизация и прочая зация
* 
**/

$action = $_GET['action'];
$output = '';
$registered = FALSE;
$location = '';
$message = '';

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
	$email = substr($db->escape($_POST['email']), 0, 45);
	$nick = substr($db->escape($_POST['nick']), 0, 45);
	if (preg_match('/^[^A-Za-z0-9]|[^0-9A-Za-z\-\_]+/', $nick) || strlen($nick) <= 2) {
		$output = 'Вы пытаетесь сломать наш сайт, но мы будем сопротивляться! (Неправильный ник)';
	} else if (!preg_match('/^(\w|\.|\-|\_)+\@\w+\.\w+/', $email) || strlen($email) <= 5) {
		$output = 'Вы пытаетесь сломать наш сайт, но мы будем сопротивляться! (Неправильная почта)';
	} else {
		$pw = $db->escape($_POST['pw']);
		$history = json_encode(array('created' => time()));
		$privacy = json_encode(array('friends' => array('exp' => 0, 'ach' => 0, 'steam' => 0), 'reg' => array('exp' => 0, 'ach' => 0, 'steam' => 0), 'all' => array('exp' => 0, 'ach' => 0, 'steam' => 0)));
		$q = "
			INSERT INTO `ololousers` (`nick`, `email`, `pw`, `history`, `referrer`, `privacy`)
				VALUES ('$nick', '$email', MD5('$pw'), '$history', '$referrer', '$privacy')";
		$answer = $db->query($q);

		// Обрабатываем ошибки
		if (strpos($answer, 'Duplicate entry') !== FALSE){

			if (strpos($answer, 'for key \'email') !== FALSE)
				$output = 'Вы не можете зарегистрировать несколько аккаунтов на один адрес электронной почты';

			if (strpos($answer, 'for key \'nick') !== FALSE)
				$output = 'Такое имя уже используется';

		} else {
			// Проверяем регистрацию, получаем ачивки
			$q = "SELECT `id` FROM `ololousers` WHERE `nick` = '$nick' AND `email` = '$email'";
			$r = $db->query($q);

			$from = array('id' => $r[0]['id'], 'email' => 'ice_haron@mail.ru', 'name' => 'Elysium Game');
			$to = array('email' => $email, 'name' => $nick);
			$mailMessage = "Здравствуйте, это письмо пришло вам потому что на этот почтовый адрес был зарегистрирован аккаунт на портале Elysium Game\r\n";
			$mailMessage .= "Для подтверждения регистрации перейдите по следующей ссылке:\r\n";
			$mailMessage .= "http://" . $_SERVER['HTTP_HOST'] . "/auth?action=confirm&code=" . base64_encode($r[0]['id'] . '|' . $email . '|' . $nick) . "\r\n";
			$mailMessage .= "В случае, если регистрация не будет подтверждена, аккаунт будет удален через 48 часов.\r\n";
			$mailMessage .= "Спасибо за то, что вы с нами!\r\n";

			$mailer->send('register', $from, $to, 'Вы зарегистрировали аккаунт на портале Elysium Game', $mailMessage);

			$achievement->earn($r[0]['id'], 15);

			if ($referrer != 1) $achievement->earn($r[0]['id'], 8);
			if ($referrer == 1) $achievement->earn($r[0]['id'], 14);
			$output = 'Регистрация прошла успешно';
			$registered = TRUE;
			$location = '/auth?action=log';
		}
	}

/**
* 
* Подтверждение
* 
**/
} else if ($action == 'confirm' && isset($_GET['code'])) {
	$confirm = explode('|', base64_decode($_GET['code']));
	$q = "SELECT * FROM `ololousers` WHERE `id` = {$confirm[0]} AND `email` = '{$confirm[1]}' AND `nick` = '{$confirm[2]}';";
	$r = $db->query($q);

	if (gettype($r) == 'array') {
		$db->query("UPDATE `ololousers` SET `group` = 1 WHERE `id` = {$confirm[0]};");
		$output = 'Поздравляем, вы активировали свой аккаунт!';
		$location = '/auth?action=log';
	} else $output = 'Что-то пошло не так.';

/**
* 
* Авторизация
* 
**/
} else if ($action == 'log' && isset($_POST['login'])) {
	// Защищаемся, проверяем валидность данных
	$login = $db->escape($_POST['login']);
	$pw = $db->escape($_POST['pw']);
	$q = "SELECT `id`, `email`, `nick`, `group` FROM `ololousers` WHERE (`nick` = '$login' OR `email` = '$login') AND `pw` = MD5('$pw')";
	$answer = $db->query($q);

	if ($answer === NULL) $output = 'Не найдено такой комбинации логина/почты и пароля'; // Нутыпонил

	else if ($answer[0]['group'] == '0') {

		$output = 'Ваш аккаунт не активирован, сперва активируйте его. Письмо со ссылкой на активацию отправлено на вашу электронную почту, если же письма нет, напишите нам с указанного вами адреса. <a href="mailto:ice_haron@mail.ru?subject=Не%20могу%20активировать%20аккаунт&body=Мой%20ник%20' . $answer[0]['nick'] . '">Вот на этот адрес</a> (Менять что-либо в заголовке и тексте сообщения не рекомендуем.)';

	} else {
		// Запихиваем данные в сессию и прыгаем в ЛК
		$_SESSION['login'] = $answer[0]['nick'];
		$_SESSION['email'] = $answer[0]['email'];
		$output = 'Вы успешно авторизовались';
		$location = '/lk';
		$registered = TRUE;
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
	$location = '/';
	header("Location: " . $location);

/**
* 
* Смена пароля
* 
**/
} else if ($action == 'changepw' && isset($_POST['oldpw']) && isset($_POST['newpw']) && isset($_POST['repw'])) {
	$oldpw = $db->escape($_POST['oldpw']);
	$newpw = $db->escape($_POST['newpw']);
	$repw = $db->escape($_POST['repw']);
	if ($newpw == $repw) {
		// Проверяем совпадение старого пароля, если все хорошо, пишем новый пароль
		$q = "SELECT IF(MD5('{$oldpw}') = `pw`, 1, 0) as `pass` FROM `ololousers` WHERE `id` = $cid";
		$r = $db->query($q);
		$pass = $r[0]['pass'];

		if($pass) {
			// Ну и конечно же, пишем в историю
			$history['changedPw'][] = time();
			$h = json_encode($history);
			$q = "UPDATE `ololousers` SET `pw` = MD5('{$newpw}'), `history` = '$h' WHERE `id` = $cid";
			$r = $db->query($q);

			if($r) $message = "Пароль успешно изменен";

			else $message = "something broken";

		} else {
			$message = "Неверно указан старый пароль";
		}
	} else {
		$message = "Вы умудрились ввести неправильное подтверждение пароля";
	}

	$location = '/lk';

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
				$achievement->earn($id,1);

			} else $message = "Сервис uLogin вернул пустой ID, мы не знаем, почему."; // Shit happens

		} else {
			// Нутыпонил
			$message = "Этот аккаунт Steam уже привязан к другой учетной записи.";
			unset($steamUser);
		}

	} else $message = "К вашей учетной записи уже привязан SteamID, сначала следует его отвязать";

	$location = '/lk';

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
		$achievement->earn($id,2);
	// Ну это уже против утырков
	} else $message = "По какой-то причине привязанный к вашей учетной записи аккаунт Steam отличается от того, который вы пытаетесь отвязать";

/**
* 
* Настройки приватности
* 
**/
} else if ($action == 'privacy') {
	$privacy = array('friends' => array('exp' => 0, 'ach' => 0, 'steam' => 0), 'reg' => array('exp' => 0, 'ach' => 0, 'steam' => 0), 'all' => array('exp' => 0, 'ach' => 0, 'steam' => 0));

	foreach ($_POST as $addr => $val) {
		$a = explode('_', $addr);
		$access = 1;
		$privacy[ $a[0] ][ $a[1] ] = $access;
	}

	$privstr = json_encode($privacy);
	$q = "UPDATE `ololousers` SET `privacy` = '$privstr' WHERE `id` = {$user->info['id']}";
	$r = $db->query($q);
	$location = '/lk';

} else if ($action == 'send') {
	$output = 'Саусэм глюпи, Уася?';
	if (isset($cid)) $achievement->earn($cid, 18);
/*
	$q = "SELECT `id`, `nick`, `email` FROM `ololousers`";
	$r = $db->query($q);
	$res = '';
	foreach ($r as $recipient) {
		$mail = "Здравствуйте, вы получили это письмо потому, что на этот адрес был зарегистрирован аккаунт на портале Elysium Game с ником {$recipient['nick']}.\r\nИзвещаем вас, что ваш аккаунт на данный момент является неактивированным и будет удален в день запуска нашего сервера, также, вы не можете авторизоваться на сайте. \r\n Для активации вашего аккаунта, вам нужно пройти по следующей ссылке:\r\n http://" . $_SERVER['HTTP_HOST'] . "/auth?action=confirm&code=" . base64_encode($recipient['id'] . '|' . $recipient['email'] . '|' . $recipient['nick']) . "\r\nСпасибо, что вы с нами!\r\nElysium Game.";
		$from = array('id' => 0, 'email' => 'ice_haron@mail.ru', 'name' => 'Elysium Game');
		$to = array('email' => $recipient['email'], 'name' => $recipient['nick']);
		$subject = 'Требуется активация аккаунта Elysium Game';
		$res .= "Sending to: {$recipient['email']}\r\n";
		$res .= $mailer->send('activation', $from, $to, $subject, $mail);
		$res .= "\r\n";
	}
	echo '<pre>';
	echo($res);
	echo '</pre>';
*/

}
?>