<?php

/**
* 
* Почтовый класс
* 
**/

class mail {

	private $login;
	private $pw;

/**
* 
* Конструктор, сэр.
* Из овсянки, сэр.
* 
**/
	public function mail() {
		GLOBAL $maillogin, $mailpw;
		$this->login = $maillogin;
		$this->pw = $mailpw;
	}

/**
* 
* Отсылка сообщения
* @param from - Массив с данными об отсылателе (id, email, name)
* @param to - Массив с данными о получателе (id, email, name)
* @param subject - Тема сообщения
* @param message - Текст сообщения
* @return string - callback-строка от сендера
* 
**/
	public function send($action, $from, $to, $subject, $message) {
		GLOBAL $db;
		$fromID = $from['id'];
		$fromMail = $from['email'];
		$fromName = $from['name'];
		$toMail = $to['email'];
		$toName = $to['name'];

		$header="Date: ".date("D, j M Y G:i:s")." +0300\r\n";
		$header.="From: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode('Elysium Game')))."?= <alphatest@inextinctae.ru>\r\n";
		$header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
		$header.="X-Priority: 3 (Normal)\r\n";
		$header.="Message-ID: <172562218.".date("YmjHis")."@mail.ru>\r\n";

		$header.="Reply-To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($fromName)))."?= <$fromMail>\r\n";
		$header.="To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($toName)))."?= <$toMail>\r\n";
		$header.="Subject: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($subject)))."?=\r\n";
		$header.="MIME-Version: 1.0\r\n";
		$header.="Content-Type: text/plain; charset=utf-8\r\n";
		$header.="Content-Transfer-Encoding: 8bit\r\n";

		$smtp_conn = fsockopen("smtp.ht-systems.ru", 25, $errno, $errstr, 10);
		if ($smtp_conn !== FALSE) {
			$data = $this->get_data($smtp_conn) . "<br/>";
			fputs($smtp_conn,"EHLO mail.ru\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,"AUTH LOGIN\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,base64_encode($this->login)."\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,base64_encode($this->pw)."\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,"MAIL FROM:$fromMail\r\n"); // Заменить на почту проекта
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,"RCPT TO:$toMail\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,"DATA\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,$header."\r\n".$message."\r\n.\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			fputs($smtp_conn,"QUIT\r\n");
			$data .= $this->get_data($smtp_conn) . "<br/>";

			if ($action) {
				$query = "INSERT INTO `mail` (`action`, `userid`, `to`, `text`) VALUES ('$action', '$fromID', '$toMail', '$message');";
				$db->query($query);
			}

			return $data;

		} else {
			
			if ($action) {
				$query = "INSERT INTO `mail` (`action`, `userid`, `to`, `text`) VALUES ('$action fail', '$fromID', '$toMail', '$errstr');";
				$db->query($query);
			}

			return FALSE;
		}
	}

	public function receive() {
		GLOBAL $db;
		$address = "pop3.ht-systems.ru";  // адрес pop3-сервера 
		$port    = "110";          // порт (стандартный pop3 - 110)

		$box = imap_open("{" . $address . ":" . $port . "/pop3}", $this->login, $this->pw);
		// echo '<pre>';
		// echo "\n\n";
		$count = imap_num_msg($box);
		// echo $count;

		$fringe = max($count-100, 0);

		for($i = $count; $i > $fringe; $i--) {
			$overview = imap_fetch_overview($box, $i);
			// echo 'Сырая тема: '. $overview[0]->subject;
			// echo "\n" . 'UTF-8 тема: '. imap_utf8($overview[0]->subject);
			// echo "\n" . 'QPrint тема: '. imap_qprint($overview[0]->subject);
			// echo "\n" . 'Base64 тема: '. imap_base64($overview[0]->subject);
			// echo "\n" . 'QPrint->UTF-8: '. imap_qprint(imap_utf8($overview[0]->subject));
			// echo "\n" . 'UTF-8->QPrint: '. imap_utf8(imap_qprint($overview[0]->subject));
			preg_match_all('/=\?([A-z0-9\-]+)\?(\w)\?([A-z0-9\+=\/]+)\?=\s?/', $overview[0]->subject, $matches);
			$encoding = $matches[1];
			$smthn = $matches[2];
			$textArr = $matches[3];
			$subject = '';

			foreach ($textArr as $part) $subject .= base64_decode($part);

			if ($subject == 'Не могу активировать аккаунт') {
				unset($answer, $nickname, $email, $player);
				// echo $subject;
				// echo "\n\n***************\n\n";
				$header = imap_header($box, $i);
				$email = $header->from[0]->mailbox . "@" . $header->from[0]->host;
				$body = imap_fetchbody($box, $i, 1);
				// echo "\n" . 'UTF-8: '. imap_utf8($body);
				// echo "\n" . 'QPrint: '. imap_qprint($body);
				// echo "\n" . 'Base64: '. imap_base64($body);
				// echo "\n" . 'QPrint->UTF-8: '. imap_qprint(imap_utf8($body));
				// echo "\n" . 'UTF-8->QPrint: '. imap_utf8(imap_qprint($body));
				// $text = base64_decode(imap_qprint(imap_utf8($body)));
				$text = base64_decode(imap_utf8($body));
				$pass = preg_match('/\w+$/', trim($text), $matches);

				if (!$pass) $answer = 'Неправильно указан ник: "' . $text . '"';
				else {
					$nickname = $matches[0];
					$player = $db->query("SELECT `id`, `group` FROM `ololousers` WHERE `email` = '$email' AND `nick` = '$nickname'");

					if (isset($player[0]))

						if ($player[0]['group'] == '0') {

							$db->query("UPDATE `ololousers` SET `group` = 1 WHERE `id` = {$player[0]['id']}");
							$answer = 'Аккаунт успешно активирован';

						} else $answer = 'Аккаунт с ником "' . $nickname . '" уже был активирован ранее';

					else $answer = 'Неправильно указан ник: "' . $nickname . '"';

				}

				$mails = $db->query("SELECT * FROM `mail` WHERE `to` = '$email' AND `text` = '$answer'");

				if ($mails === NULL && isset($answer)) {
					$from = array('id' => isset($player[0]['id']) ? $player[0]['id'] : 0, 'email' => 'alphatest@inextinctae.ru', 'name' => 'Elysium Game');
					$to = array('email' => $email, 'name' => isset($nickname) ? $nickname : 'unknown');
					$this->send('activation request', $from, $to, 'Результат активации аккаунта', $answer);
				}
				// $body = imap_qprint($body);
				// $body = iconv('windows-1251', 'utf-8', $body);
				// $body = base64_decode($body);
				// echo "\n\n***********\n\n";
			}

		}
		// echo '</pre>';

		imap_close($box);

	}

	private function get_data($smtp_conn) {
		$data="";
		while($str = fgets($smtp_conn,515))
		{
			$data .= $str;
			if(substr($str,3,1) == " ") { break; }
		}
		return $data;
	}

}