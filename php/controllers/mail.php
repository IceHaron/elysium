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
$r = $mailer->send('', array('id' => '0', 'email' => 'ice-haron@rambler.ru', 'name' => 'Elysium Game'), $from, $confirmSubject, $confirmText);

// var_dump($s, $r);

// $message .= $data;
$message .= 'Отправка письма прошла успешно!';



function send($mailFrom, $mailTo, $header, $messageText) {
	$smtp_conn = fsockopen("smtp.rambler.ru", 587,$errno, $errstr, 10);
	$data = get_data($smtp_conn);
	fputs($smtp_conn,"EHLO mail.ru\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,"AUTH LOGIN\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,base64_encode("ice-haron")."\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,base64_encode("poVTAS230105")."\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,"MAIL FROM:ice-haron@rambler.ru\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,"RCPT TO:$mailTo\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,"RCPT TO:$mailFrom\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,"DATA\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,$header."\r\n".$messageText."\r\n.\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	fputs($smtp_conn,"QUIT\r\n");
	$data .= get_data($smtp_conn) . "<br/>";

	return TRUE;
}

function get_data($smtp_conn) {
	$data="";
	while($str = fgets($smtp_conn,515))
	{
		$data .= $str;
		if(substr($str,3,1) == " ") { break; }
	}
	return $data;
}