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

function purgeSteam() {
	GLOBAL $db;
	$q = "SELECT * FROM `ololousers`";
	$r = $db->query($q);
	foreach ($r as $player) {
		$history = json_decode($player['history'], TRUE);
		unset($history['steamBindingSet'], $history['steamBindingBroken']);
		$str = json_encode($history);
		$q = "UPDATE `ololousers` SET `steamid` = NULL, `history` = '$str' WHERE `id` = {$player['id']}";
		$r = $db->query($q);
	}
}