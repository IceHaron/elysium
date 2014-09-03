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

/**
* 
* Регистрация
* 
**/
if ($action == 'reg' && isset($_POST['nick']) && !isset($cid)) {

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

	} else if (!preg_match('/^(\w|\.|\-|\_)+\@\w+\.\w+/', $email) || strlen($email) <= 5 || preg_match('/^(\w|\.|\-|\_)+\@mailinator\.\w+/', $email)) {
		$output = 'Вы пытаетесь сломать наш сайт, но мы будем сопротивляться! (Неправильная почта)';

	} else {
		$pw = $db->escape($_POST['pw']);
		$history = json_encode(array('created' => time()));
		$privacy = json_encode(array('friends' => array('exp' => 0, 'ach' => 0, 'steam' => 0), 'reg' => array('exp' => 0, 'ach' => 0, 'steam' => 0), 'all' => array('exp' => 0, 'ach' => 0, 'steam' => 0)));
		$q = "
			INSERT INTO `ololousers` (`nick`, `mcname`, `email`, `pw`, `history`, `referrer`, `privacy`)
				VALUES ('$nick', '$nick', '$email', MD5('$pw'), '$history', '$referrer', '$privacy')";
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

			$from = array('id' => $r[0]['id'], 'email' => 'alphatest@inextinctae.ru', 'name' => 'Elysium Game');
			$to = array('email' => $email, 'name' => $nick);
			$mailMessage = "Здравствуйте, это письмо пришло вам потому что на этот почтовый адрес был зарегистрирован аккаунт на портале Elysium Game\r\n";
			$mailMessage .= "Для подтверждения регистрации перейдите по следующей ссылке:\r\n";
			$mailMessage .= "http://" . $_SERVER['HTTP_HOST'] . "/auth?action=confirm&code=" . base64_encode($r[0]['id'] . '|' . $email . '|' . $nick) . "\r\n";
			$mailMessage .= "В случае, если регистрация не будет подтверждена, аккаунт будет удален через 48 часов.\r\n";
			$mailMessage .= "Спасибо за то, что вы с нами!\r\n";

			$mailer->send('register', $from, $to, 'Вы зарегистрировали аккаунт на портале Elysium Game', $mailMessage);

			$achievement->earn($r[0]['id'], 22);

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

		if ($r[0]['group'] != '0') {
			$output = 'Ваш аккаунт уже был активирован ранее';

		} else {
			$r = $db->query("UPDATE `ololousers` SET `group` = 1 WHERE `id` = {$confirm[0]};");

			if ($r) {
				$output = 'Поздравляем, вы активировали свой аккаунт!';
				$location = '/auth?action=log';
				$q = "INSERT INTO `tokens` (`user`, `action`) VALUES ({$confirm[0]}, 'changename'),({$confirm[0]}, 'changename');";
				$r = $db->query($q);
				writeHistory($confirm[0], 'activated', time());

				$forumnick = $confirm[2];
				$forumemail = $confirm[1];
				$p = $db->query("SELECT `pw` FROM `ololousers` WHERE `nick` = '$forumnick' AND `email` = '$forumemail'");
				$forumpw = $p[0]['pw'];
				$salt = '9034u3ui';
				$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));

				$ch = curl_init('http://srv.elysiumgame.ru/');
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=reg&user=$forumnick&email=$forumemail&pw=$forumpw&key=$key&salt=$salt");
				$res = curl_exec($ch);
				curl_close($ch);
			}

		}

	} else $output = 'Что-то пошло не так.';

/**
* 
* Авторизация
* 
**/
} else if ($action == 'log' && isset($_POST['login']) && !isset($cid)) {
	// Защищаемся, проверяем валидность данных
	$login = $db->escape($_POST['login']);
	$pw = $db->escape($_POST['pw']);
	$check = saltPw($pw);
	$q = "SELECT `id`, `email`, `nick`, `group` FROM `ololousers` WHERE (`nick` = '$login' OR `email` = '$login') AND `pw` = MD5('$pw')";
	$answer = $db->query($q);

	if ($answer === NULL) $output = 'Не найдено такой комбинации логина/почты и пароля'; // Нутыпонил

	else if ($answer[0]['group'] == '0') {

		$output = 'Ваш аккаунт не активирован, сперва активируйте его.<br/>Письмо со ссылкой на активацию отправлено на вашу электронную почту.<br/>Если же письма нет, напишите нам с указанного вами адреса. <a href="mailto:alphatest@inextinctae.ru?subject=Не%20могу%20активировать%20аккаунт&body=Мой%20ник%20' . $answer[0]['nick'] . '">Вот на этот адрес</a> (Менять что-либо в заголовке и тексте сообщения не рекомендуем.)<br/>Если ссылка никуда не ведет, напишите на адрес "alphatest@inextinctae.ru" письмо с темой "Не могу активировать аккаунт" и сообщением "Мой ник MyNick", где MyNick - ваш ник на сайте.';

	} else {
		// Запихиваем данные в сессию и прыгаем в ЛК
		$_SESSION['login'] = $answer[0]['nick'];
		$_SESSION['email'] = $answer[0]['email'];
		$output = '<h1>Вы успешно авторизовались</h1>';
		$location = '/lk';
		$registered = TRUE;

		$forumnick = $answer[0]['nick'];
		$forumemail = $answer[0]['email'];
		$p = $db->query("SELECT `pw` FROM `ololousers` WHERE `nick` = '$forumnick' AND `email` = '$forumemail'");
		$forumpw = $p[0]['pw'];
		$salt = '9034u3ui';
		$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));

		$ch = curl_init('http://srv.elysiumgame.ru/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=reg&user=$forumnick&email=$forumemail&pw=$forumpw&key=$key&salt=$salt");
		$res = curl_exec($ch);
		curl_close($ch);

	}

/**
* 
* Логофф
* 
**/
} else if ($action == 'off') {
	// Очищаем куки (для упорышей) и переменные сессии
	if ($_GET['hash'] == session_id()) {
		session_unset();
		session_destroy();
		unset($_COOKIE['PHPSESSID']);
		setcookie('PHPSESSID', NULL);
	}
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
		$q = "SELECT IF(MD5('$oldpw') = `pw`, 1, 0) as `pass` FROM `ololousers` WHERE `id` = $cid";
		$r = $db->query($q);
		$pass = $r[0]['pass'];

		if($pass) {
			$q = "UPDATE `ololousers` SET `pw` = MD5('$newpw') WHERE `id` = $cid";
			$r = $db->query($q);

			if($r) {
				$message = "Пароль успешно изменен";
				// Ну и конечно же, пишем в историю
				writeHistory($cid, 'changedPw', time());

				$forumnick = $user->info['nick'];
				$forumemail = $user->info['email'];
				$p = $db->query("SELECT `pw` FROM `ololousers` WHERE `nick` = '$forumnick' AND `email` = '$forumemail'");
				$forumpw = $p[0]['pw'];
				$salt = '9034u3ui';
				$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));

				$ch = curl_init('http://srv.elysiumgame.ru/');
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=pw&user=$forumnick&email=$forumemail&pw=$forumpw&key=$key&salt=$salt");
				$res = curl_exec($ch);
				curl_close($ch);
				var_dump($res);

			} else $message = "something broken";

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
				writeHistory($id, 'steamBindingSet', array($steamUser['uid'] => time()));
				$exp += 500;
				$q = "UPDATE `ololousers` SET `steamid` = '{$steamUser['uid']}', `exp` = $exp WHERE `id` = $cid";
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
		$unbindID = $db->escape($_POST['unbindID']);
		writeHistory($id, 'steamBindingBroken', array($unbindID => time()));
		$exp -= 500;
		$q = "UPDATE `ololousers` SET `steamid` = NULL, `exp` = $exp WHERE `id` = $cid";
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

/**
* 
* Сброс пароля
* 
**/
} else if ($action == 'reset' && !isset($cid)) {

	if (isset($_GET['token'])) {
		$player = tokenDecode($_GET['token']);
		$newpw = getRandomString(6);

		if ($player) $q = "UPDATE `ololousers` SET `pw` = MD5('$newpw') WHERE `id` = {$player['id']}";
		else exit('Wrong token');

		$r = $db->query($q);

		if ($r) {
			writeHistory($player['id'], 'pwReset', time());
			$from = array('id' => $player['id'], 'email' => 'alphatest@inextinctae.ru', 'name' => 'Elysium Game');
			$to = array('email' => $player['email'], 'name' => $player['nick']);
			$sent = $mailer->send('pwreset', $from, $to, 'Ваш новый пароль', "Вы успешно сбросили пароль, теперь он у вас такой:\r\n" . $newpw . "\r\nРекомендуем сразу же после входа на сайт, сменить пароль на другой.");
			if ($sent !== FALSE) {
				$output = "Сброс пароля прошел удачно";

				$forumnick = $player['nick'];
				$forumemail = $player['email'];
				$p = $db->query("SELECT `pw` FROM `ololousers` WHERE `nick` = '$forumnick' AND `email` = '$forumemail'");
				$forumpw = $p[0]['pw'];
				$salt = '9034u3ui';
				$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));

				$ch = curl_init('http://srv.elysiumgame.ru/');
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=pw&user=$forumnick&email=$forumemail&pw=$forumpw&key=$key&salt=$salt");
				$res = curl_exec($ch);
				curl_close($ch);
			}

			else $output = "Подключение к почтовому серверу не удалось, пожалуйста, попробуйте обновить страницу чуть позже.";
		}

	} else if (isset($_POST['email'])) {
		$email = $db->escape($_POST['email']);
		$nick = $db->escape($_POST['nick']);
		$q = "SELECT `id` FROM `ololousers` WHERE `email` = '$email' AND `nick` = '$nick'";
		$r = $db->query($q);

		if (gettype($r) == 'array' && count($r) == 1) {
			$id = $r[0]['id'];
			$token = tokenEncode($id, $email, $nick);
			$from = array('id' => $id, 'email' => 'alphatest@inextinctae.ru', 'name' => 'Elysium Game');
			$to = array('email' => $email, 'name' => $nick);
			$mailMessage = "Вам пришло это письмо так как вы запрашивали сброс пароля своего аккаунта, если это так, то пройдите, пожалуйста по ссылке:\r\nhttp://" . $_SERVER['HTTP_HOST'] . '/auth?action=reset&token=' . $token . "\r\n" . 'Если же вы не запрашивали сброс пароля, просто проигнорируйте это письмо, но знайте: вас заметили и пытаются затроллить!';
			$sent = $mailer->send('resetpw', $from, $to, 'Сброс пароля', $mailMessage);
			
			if ($sent !== FALSE) $output = "Письмо с инструкциями для смены пароля было выслано на вашу электронную почту";
			else $output = "Подключение к почтовому серверу не удалось, пожалуйста, попробуйте обновить страницу чуть позже.";

		} else $output = 'По-моему, вы нас хотите обмануть. Не найдено такого сочетания e-mail + nick';

	} else {
		$output = '
			<p>Для сброса пароля введите свой Электронный адрес и Ник на сайте</p>
			<form method="POST">
				<input type="text" name="email" placeholder="E-Mail" required>
				<input type="text" name="nick" placeholder="Ник" required>
				<input type="submit" value="Сбросить пароль">
			</form>
		';
	}

} else if ($action == 'send') {
	$output = '<h1>Саусэм глюпи, Уася?</h1>';
	
	if (isset($cid)) $achievement->earn($cid, 18);
/*
	$q = "SELECT `id`, `nick`, `email` FROM `ololousers`";
	$r = $db->query($q);
	$res = '';
	foreach ($r as $recipient) {
		$mail = "Здравствуйте, вы получили это письмо потому, что на этот адрес был зарегистрирован аккаунт на портале Elysium Game с ником {$recipient['nick']}.\r\nИзвещаем вас, что ваш аккаунт на данный момент является неактивированным и будет удален в день запуска нашего сервера, также, вы не можете авторизоваться на сайте. \r\n Для активации вашего аккаунта, вам нужно пройти по следующей ссылке:\r\n http://" . $_SERVER['HTTP_HOST'] . "/auth?action=confirm&code=" . base64_encode($recipient['id'] . '|' . $recipient['email'] . '|' . $recipient['nick']) . "\r\nСпасибо, что вы с нами!\r\nElysium Game.";
		$from = array('id' => 0, 'email' => 'alphatest@inextinctae.ru', 'name' => 'Elysium Game');
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

/**
* 
* Смена ника
* 
**/
} else if (($action == 'sitenick' || $action == 'mcnick') && isset($cid)) {

	if ($action == 'sitenick') $what = 'nick';
	else $what = 'mcname';

	if (isset($_POST['newnick'])) {

		if ((int)$user->info['tokens']['changename'] > 0) {
			$newNick = substr($db->escape($_POST['newnick']), 0, 45);

			if (preg_match('/^[^A-Za-z0-9]|[^0-9A-Za-z\-\_]+/', $newNick) || strlen($newNick) <= 2) {
				$output = '<b>Вы пытаетесь сломать наш сайт, но мы будем сопротивляться! (Неправильный ник)</b>';
				$registered = TRUE;

			} else {
				$remainTokens = (int)$user->info['tokens']['changename'] - 1;
				$answerUsers = $db->query("UPDATE `ololousers` SET `$what` = '$newNick' WHERE `id` = $cid");
				$answerTokens = $db->query("DELETE FROM `tokens` WHERE `user` = $cid AND `action` = 'changename' LIMIT 1;");

				if ($answerUsers === TRUE && $answerTokens === TRUE) {
					if ($action == 'sitenick') $_SESSION['login'] = $newNick;
					writeHistory($cid, 'changed' . $action, array(time() => array('old' => $user->info['nick'], 'new' => $newNick)));
					$output = 'Ник сменен успешно';
					$location = '/lk';

				} else {
					$output = 'Произошла какая-то ошибка, если вы не знаете, как такое могло произойти, <a href="mailto:alphatest@inextinctae.ru?subject=Не%20меняется%20ник&body=Ваше%20сообщение">Напишите нам</a>';
				}

			}

		} else $output = 'У вас недостаточно токенов на смену ника';

	} else {

		if ((int)$user->info['tokens']['changename'] <= 0) $output = 'У вас недостаточно токенов на смену ника';
		else $registered = TRUE;

	}


/**
* 
* Для тех, кто уже авторизован ни к чему посещать страницы авторизации, регистрации и сброса пароля, выбрасываем в ЛК
* 
**/
} else if (($action == 'log' || $action == 'reg' || $action == 'reset') && isset($cid)) {
	header("Location: /lk");
}
?>