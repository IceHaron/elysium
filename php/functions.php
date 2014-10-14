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
	$equiv = array(20000 => 2, 20010 => 3, 20020 => 4);

	$q = "
		SELECT `ololousers`.`id`, `ololousers`.`group`, `purchases`.`item`
		FROM `ololousers`
		LEFT JOIN `purchases` ON (`purchases`.`user` = `ololousers`.`id` AND `purchases`.`item` >= 20000 AND `purchases`.`item` <= 29999 AND `purchases`.`start` <= now() AND `purchases`.`end` >= now())
		WHERE `ololousers`.`group` IN (1,2,3,4,5);";
	$r = $db->query($q);
	
	foreach ($r as $player) {
		if ($player['item'] !== NULL AND $player['group'] != $equiv[ $player['item'] ]) {
			$q = "UPDATE `ololousers` SET `group` = {$equiv[ $player['item'] ]} WHERE `id` = {$player['id']}";
			$db->query($q);
		} else if ($player['item'] == NULL AND $player['group'] != 1) {
			$q = "UPDATE `ololousers` SET `group` = 1 WHERE `id` = {$player['id']}";
			$db->query($q);
		} else {
		}
	}

	$q = "
		SELECT `ololousers`.`id`, `purchases`.`item`
		FROM `ololousers`
		JOIN `purchases` ON (`purchases`.`user` = `ololousers`.`id` AND `purchases`.`item` IN (10001, 10002) AND `purchases`.`start` <= now() AND `purchases`.`end` >= now())
		WHERE `ololousers`.`group` > 0;";
	$r = $db->query($q);

	foreach ($r as $player) {
		$allowPrefix[ $player['id'] ] = $player['id'];
	}
	
	$q = "
		SELECT `ololousers`.`id`, `ololousers`.`email`, `ololousers`.`nick`, `ololousers`.`prefix`, `ololousers`.`mcname`, `ololousers`.`group`, `usergroups`.`server_alias`, `usergroups`.`server_prefix`
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
		$prefix = str_replace('[&r] ', $player['server_prefix'], $player['prefix']);
		if ($prefix == '' || !isset($allowPrefix[ $player['id'] ])) $prefix = 'null';
		$prefix = urlencode($prefix);

		$ch = curl_init('http://srv.elysiumgame.ru/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=sync&user=$forumnick&prefix=$prefix&email=$forumemail&pw=$forumpw&group=$group&mcnick=$mcname&key=$key&salt=$salt");
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

/**
* 
* Пинг сервера
* @param server - айпишник
* @param port - порт
* @param timeout - таймаут
* @return array - инфа о сервере
* 
**/
function pingMCServer($server,$port=25565,$timeout=2){
	$fp = fsockopen($server, $port, $errno, $errstr, 5);
	if (!$fp) exit();
	$socket=socket_create(AF_INET,SOCK_STREAM,getprotobyname('tcp')); // set up socket holder
	$con = socket_connect($socket,$server,$port); // connect to minecraft server on port 25565
	socket_send($socket,chr(254).chr(1),2,null); // send 0xFE 01 -- tells the server we want pinglist info
	socket_recv($socket,$buf,3,null); // first 3 bytes indicate the len of the reply. not necessary but i'm not one for hacky socket read loops
	$buf=substr($buf,1,2); // always pads it with 0xFF to indicate an EOF message
	$len=unpack('n',$buf); // gives us 1/2 the length of the reply
	socket_recv($socket,$buf,$len[1]*2,null); // read $len*2 bytes and hang u[
	$data=explode(chr(0).chr(0),$buf); // explode on nul-dubs
	array_shift($data); // remove separator char
	return $data; // boom sucka
}

function giveBonus($player, $izum, $type, $reason = 'Бонус за покупку Изюма') {
	GLOBAL $db;
	if ($type == 'buy') $bonus = round(pow(2, -500000 / $izum) * 3 * $izum / 10);
	else $bonus = $izum;
	$q = "UPDATE `ololousers` SET `izumko` = `izumko` + $bonus WHERE `id` = $player;";
	$ololousers = $db->query($q);
	$q = "INSERT INTO `gifts` (`admin`, `user`, `izum`, `reason`) VALUES (0, $player, $bonus, '$reason');";
	$gifts = $db->query($q);

	if (!$ololousers || !$gifts) return FALSE;

	return TRUE;
}

function giveCoupon($player, $name, $effect = 0) {
	GLOBAL $db;
	$q = "INSERT INTO `coupons` (`user`, `name`, `effect`) VALUES ($player, '$name', $effect);";
	$r = $db->query($q);

	if (!$r) return FALSE;

	return TRUE;
}