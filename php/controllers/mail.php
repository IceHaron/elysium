<?

// GLOBAL $db, $user;

foreach ($_POST as $k => $v) {
	$input[$k] = $db->escape($v);
}

$message = '';

$header="Date: ".date("D, j M Y G:i:s")." +0300\r\n";
$header.="From: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode('Elysium Game')))."?= <ice-haron@rambler.ru>\r\n";
$header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
$header.="X-Priority: 3 (Normal)\r\n";
$header.="Message-ID: <172562218.".date("YmjHis")."@rambler.ru>\r\n";

switch ($_GET['action']) {

	case 'refer':
		$action = 'refer';
		$userID = $input['user'];
		$mailTo = $input['email'];
		$mailFrom = $input['from'];

		$confirmSubject = 'Ваше приглашение на портал Elysium Game успешно отправлено';
		
		$confirmText = "То, что вы читаете это письмо, означает, что скорее всего, приглашение успешно дошло до получателя и все у нас функционирует нормально. Если приглашение вашему другу не пришло, попробуйте выполнить следующие действия:\r\n";
		$confirmText .= " - Попросите друга проверить папку \"СПАМ\";\r\n";
		$confirmText .= " - Удостоверьтесь, что адрес электронной почты друга был введен правильно: {$input['email']}\r\n";
		$confirmText .= " - Попробуйте выслать приглашение на свою собственную почту, если оно не придет, но придет такое же письмо, которое вы сейчас читаете, значит у нас что-то сломалось;\r\n";
		$confirmText .= "Если все-таки приглашение не пришло, пожалуйста, свяжитесь с кем-нибудь из администрации в любой социальной сети или по почте <ice-haron@rambler.ru>.\r\n";
		$confirmText .= "Спасибо за то, что вы с нами!\r\n";

		$confirmHeader=$header."Reply-To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($input['me'])))."?= <$mailFrom>\r\n";
		$confirmHeader.="To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($input['me'])))."?= <$mailFrom>\r\n";
		$confirmHeader.="Subject: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($confirmSubject)))."?=\r\n";
		$confirmHeader.="MIME-Version: 1.0\r\n";
		$confirmHeader.="Content-Type: text/plain; charset=utf-8\r\n";
		$confirmHeader.="Content-Transfer-Encoding: 8bit\r\n";

		$subject = $input['subject'];
		$messageText = "Привет, " . $input['name'] . ", хочу пригласить тебя зарегистрироваться на портале Elysium Game в качестве моего друга, подумай хорошенько, ведь за это и ты тоже получишь ништяки ;)\r\n";
		$messageText .= "Вот ссылка на регистрацию по моему приглашению: " . $input['link'] . "\r\n";
		$messageText .= $input['message'] . "\r\n";
		$messageText .= "С уважением, " . $input['me'] . ".\r\n";

		$header.="Reply-To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($input['me'])))."?= <$mailFrom>\r\n";
		$header.="To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($input['name'])))."?= <$mailTo>\r\n";
		$header.="Subject: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($subject)))."?=\r\n";
		$header.="MIME-Version: 1.0\r\n";
		$header.="Content-Type: text/plain; charset=utf-8\r\n";
		$header.="Content-Transfer-Encoding: 8bit\r\n";

		$query = "INSERT INTO `mail` (`action`, `userid`, `to`, `text`) VALUES ('$action', $userID, '$mailTo', '$messageText');";

		// $message .= $messageText;
	break;
	default: $message .= 'w00t?';
}

send($mailFrom, $mailTo, $header, $messageText);
send($mailFrom, $mailFrom, $confirmHeader, $confirmText);
$db->query($query);

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