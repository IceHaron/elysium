<?

/**
* 
* Рандомная строка нужной длины
* @param length - требуемая длина строки
* @return string - строка
* 
**/
function getRandomString($length = 1){
	$result = '';
	$array = array_merge(range('a','z'), range('0','9'));
	for($i = 0; $i < $length; $i++){
		$result .= $array[mt_rand(0, 35)];
	}
	return $result;
}

/**
* 
* Ну это зерофилл, что тут можно сказать?
* @param str - строка/число
* @param outLen - требуемая длина
* @return string - отформатированная строка
* 
**/
function zerofill($str, $outLen) {
	$inpLen = strlen((string)$str);
	$addLen = $outLen - $inpLen;
	$addStr = '';
	for ($i = 0; $i < $addLen; $i++) {
		$addStr .= '0';
	}
	$addStr .= $str;
	return $addStr;
}

/**
* 
* Запись в историю
* @param id - идентификатор пользователя
* @param key - ключ в массиве истории
* @param value - записываемое значение
* @return boolean - успешность записи
* 
**/
function writeHistory($id, $key, $value) {
	GLOBAL $db;
	$q = "SELECT `history` FROM `ololousers` WHERE `id` = $id";
	$r = $db->query($q);
	$history = json_decode($r[0]['history'], TRUE);
	$history[$key][] = $value;
	$str = json_encode($history);
	$q = "UPDATE `ololousers` SET `history` = '$str' WHERE `id` = $id";
	$r = $db->query($q);
	return $r;
}

function syncAccs() {
	GLOBAL $db;
	$output = '';
	$q = "
		SELECT `ololousers`.`email`, `ololousers`.`nick`, `ololousers`.`mcname`, `ololousers`.`group`, `usergroups`.`server_alias`
		FROM `ololousers`
		JOIN `usergroups` ON (`usergroups`.`id` = `ololousers`.`group`)
		WHERE `ololousers`.`group` != 0;";
	$r = $db->query($q);

	foreach ($r as $player) {
		$forumnick = $player['nick'];
		$forumemail = $player['email'];
		$salt = '9034u3ui';
		$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));
		$forumpw = md5($key);
		$group = $player['group'] . '__' . $player['server_alias'];
		$mcname = $player['mcname'];

		$ch = curl_init('http://srv.elysiumgame.ru/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=sync&user=$forumnick&email=$forumemail&pw=$forumpw&group=$group&mcnick=$mcname&key=$key&salt=$salt");
		$res = curl_exec($ch);
		curl_close($ch);
		$output .= "&lt;$forumemail&gt; $res<br/><br/>";
	}

	return $output;
}

function deleteUsers() {
	GLOBAL $db, $mailer;
	$q = "SELECT `id`, `email`, `nick`, `history`, `group` FROM `ololousers`";
	$r = $db->query($q);
	$output = '';

	foreach ($r as $unit) {
		$hist = json_decode($unit['history'], TRUE);

		if (time() - $hist['created'] >= 172800 && $unit['group'] == 0) {
			$q = "DELETE FROM `ololousers` WHERE `id` = {$unit['id']}";
			$r = $db->query($q);
			$from = array('id' => $unit['id'], 'email' => 'robot@elysiumgame.ru', 'name' => 'Elysium Game');
			$to = array('email' => $unit['email'], 'name' => $unit['nick']);
			$subject = 'Ваш аккаунт на сайте Elysium Game удален';
			$message = 'В связи с тем, что вы создали аккаунт и за двое суток не активировали его, аккаунт был безвозвратно удален.';
			$s = $mailer->send('userdeleted', $from, $to, $subject, $message);
			$output .= 'Удален аккаунт ' . $unit['id'] . ' с ником "' . $unit['nick'] . '"<br/>';

		}

	}

	return $output;
}