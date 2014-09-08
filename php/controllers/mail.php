<?

// GLOBAL $db, $user;

foreach ($_POST as $k => $v) {
	$input[$k] = $db->escape($v);
}

$message = '';

switch ($_GET['action']) {

	case 'refer':
		$from = array('id' => $input['user'], 'email' => $input['from'], 'name' => $input['me']);
		$to = array('email' => $input['email'], 'name' => $input['name']);
		$subject = $input['subject'];
		$confirmSubject = 'Ваше приглашение на портал Elysium Game успешно отправлено';
		
		$confirmText = "То, что вы читаете это письмо, означает, что скорее всего, приглашение успешно дошло до получателя и все у нас функционирует нормально. Если приглашение вашему другу не пришло, попробуйте выполнить следующие действия:\r\n";
		$confirmText .= " - Попросите друга проверить папку \"СПАМ\";\r\n";
		$confirmText .= " - Удостоверьтесь, что адрес электронной почты друга был введен правильно: {$input['email']}\r\n";
		$confirmText .= " - Попробуйте выслать приглашение на свою собственную почту, если оно не придет, но придет такое же письмо, которое вы сейчас читаете, значит у нас что-то сломалось;\r\n";
		$confirmText .= "Если все-таки приглашение не пришло, пожалуйста, свяжитесь с кем-нибудь из администрации в любой социальной сети или по почте <ice-haron@rambler.ru>.\r\n";
		$confirmText .= "Спасибо за то, что вы с нами!\r\n";

		$messageText = "Привет, " . $to['name'] . ", хочу пригласить тебя зарегистрироваться на портале Elysium Game в качестве моего друга, подумай хорошенько, ведь за это и ты тоже получишь ништяки ;)\r\n";
		$messageText .= "Вот ссылка на регистрацию по моему приглашению: " . $input['link'] . "\r\n";
		$messageText .= $input['message'] . "\r\n";
		$messageText .= "С уважением, " . $from['name'] . ".\r\n";

		// $message .= $messageText;
	break;
	default: $message .= 'w00t?';
}

$s = $mailer->send('refer', $from, $to, $subject, $messageText);
$r = $mailer->send('', array('id' => '0', 'email' => 'robot@elysiumgame.ru', 'name' => 'Elysium Game'), $from, $confirmSubject, $confirmText);

// var_dump($s, $r);

// $message .= $data;
$message .= 'Отправка письма прошла успешно!';
