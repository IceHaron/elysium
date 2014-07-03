<?php

/**
* 
* Почтовый класс
* 
**/

class mail {

/**
* 
* Конструктор, сэр.
* Из овсянки, сэр.
* 
**/
	public function mail() {

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
		$header.="From: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode('Elysium Game')))."?= <ice-haron@rambler.ru>\r\n";
		$header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
		$header.="X-Priority: 3 (Normal)\r\n";
		$header.="Message-ID: <172562218.".date("YmjHis")."@rambler.ru>\r\n";

		$header.="Reply-To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($fromName)))."?= <$fromMail>\r\n";
		$header.="To: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($toName)))."?= <$toMail>\r\n";
		$header.="Subject: =?utf-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($subject)))."?=\r\n";
		$header.="MIME-Version: 1.0\r\n";
		$header.="Content-Type: text/plain; charset=utf-8\r\n";
		$header.="Content-Transfer-Encoding: 8bit\r\n";

		if ($action) {
			$query = "INSERT INTO `mail` (`action`, `userid`, `to`, `text`) VALUES ('$action', '$fromID', '$toMail', '$message');";
			$db->query($query);
		}

		$smtp_conn = fsockopen("smtp.rambler.ru", 587, $errno, $errstr, 10);
		$data = $this->get_data($smtp_conn) . "<br/>";
		fputs($smtp_conn,"EHLO mail.ru\r\n");
		$data .= $this->get_data($smtp_conn) . "<br/>";

		fputs($smtp_conn,"AUTH LOGIN\r\n");
		$data .= $this->get_data($smtp_conn) . "<br/>";

		fputs($smtp_conn,base64_encode("ice-haron")."\r\n");
		$data .= $this->get_data($smtp_conn) . "<br/>";

		fputs($smtp_conn,base64_encode("poVTAS230105")."\r\n");
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

		return $data;
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

