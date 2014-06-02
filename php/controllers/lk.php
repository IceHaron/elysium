<?
$message = '';
if (isset($_POST)) {
	$h = $db->query("SELECT `history` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'");
	$history = json_decode($h[0]['history'], TRUE);
}
if (isset($_POST['oldpw']) && isset($_POST['newpw'])) {
	$q = "SELECT IF(MD5('{$_POST['oldpw']}') = `pw`, 1, 0) as `pass` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'";
	$r = $db->query($q);
	$pass = $r[0]['pass'];
	if($pass) {
		$history['changedPw'][] = time();
		$h = json_encode($history);
		$q = "UPDATE `ololousers` SET `pw` = MD5('{$_POST['newpw']}'), `history` = '$h' WHERE `email` = '$cemail' AND `nick` = '$clogin'";
		$r = $db->query($q);
		if($r) $message = "Пароль успешно изменен";
		else $message = "something broken";
	} else {
		$message = "Неверно указан старый пароль";
	}
} else if (isset($_POST['token'])) {
	$q = "SELECT `steamid` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'";
	$r = $db->query($q);
	if (!$r[0]['steamid']) {
		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
		$steamUser = json_decode($s, true);
		$q = "SELECT count(*) as `c` FROM `ololousers` WHERE `steamid` = '{$steamUser['uid']}'";
		$c = $db->query($q);
		if ((int)$c[0]['c'] > 1) {
			if ($steamUser != '') {
				$history['steamBindingSet'][$steamUser['uid']] = time();
				$h = json_encode($history);
				$q = "UPDATE `ololousers` SET `steamid` = '{$steamUser['uid']}', `history` = '$h' WHERE `email` = '$cemail' AND `nick` = '$clogin'";
				$r = $db->query($q);
				if($r) $message = "Привязка прошла успешно";
				else $message = "something broken";
			} else $message = "Сервис uLogin вернул пустой ID, мы не знаем, почему.";
		} else {
			$message = "Этот аккаунт Steam уже привязан к другой учетной записи.";
			unset($steamUser);
		}
	} else $message = "К вашей учетной записи уже привязан SteamID, сначала следует его отвязать";

} else if (isset($_POST['unbindSteam']) && isset($_POST['unbindID'])) {
	$q = "SELECT `steamid` FROM `ololousers` WHERE `email` = '$cemail' AND `nick` = '$clogin'";
	$r = $db->query($q);
	if ($r[0]['steamid'] == $_POST['unbindID']) {
		$history['steamBindingBroken'][ $_POST['unbindID'] ] = time();
		$h = json_encode($history);
		$q = "UPDATE `ololousers` SET `steamid` = NULL, `history` = '$h' WHERE `email` = '$cemail' AND `nick` = '$clogin'";
		$r = $db->query($q);
		if($r) $message = "Аккаунт Steam успешно отвязан";
		else $message = "something broken";
	} else $message = "По какой-то причине привязанный к вашей учетной записи аккаунт Steam отличается от того, который вы пытаетесь отвязать";
}
$r = $db->query("SELECT `user`.`email`, `user`.`nick`, `user`.`mcname`, `user`.`steamid`, `user`.`exp`, `ref`.`nick` AS `referrer`
									FROM `ololousers` AS `user`
									LEFT JOIN `ololousers` as `ref` ON (`user`.`referrer` = `ref`.`id`)
									WHERE `user`.`nick` = '$clogin' AND `user`.`email` = '$cemail'");

$steamID = isset($steamUser['uid']) ? $steamUser['uid'] : $r[0]['steamid'];
// Profile
if ($steamID) {
	$profile_str = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=0BE85074D210A01F70B48205C44D1D56&steamids=' . $steamID);


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////Нужно будет обязательно после регистрации домена получить API-Key для стима//////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	$profile = json_decode($profile_str, TRUE);
	// Вот эта строка - и есть заглушка на случай если стим недоступен
	if (!isset($profile['response']['players'][0])) $profile = array('response'=> array('players' => array(0 => array("personaname" => "Dummy", "profileurl" => "http://google.com", "avatar" => "http://placehold.it/32x32"))));
} else $profile = FALSE;
$exp = $r[0]['exp'];
$remain = $exp;
$expForLevel = 100;
$multiplier = 1.15;
$level = 1;
while ($remain > $expForLevel && $level < 70) {
	$level++;
	$remain = $exp - $expForLevel;
	$exp = $remain;
	$expForLevel = ceil($expForLevel * $multiplier);
};
if ($level < 70) {
	$percent = floor($remain / $expForLevel * 100);
	$signature = $remain . ' / ' . $expForLevel . ' exp (' . $percent . '%)';
} else {
	$percent = 100;
	$signature = $remain . ' exp';
}
$user = array(
	  'email' => $r[0]['email']
	, 'nick' => $r[0]['nick']
	, 'mcName' => $r[0]['mcname']
	, 'level' => $level
	, 'signature' => $signature
	, 'percent' => $percent
	, 'referrer' => $r[0]['referrer']
	, 'steamID' => $steamID
);
if ($profile) {
	$user['steamName'] = $profile['response']['players'][0]['personaname'];
	$user['steamURL'] = $profile['response']['players'][0]['profileurl'];
	$user['avatar'] = $profile['response']['players'][0]['avatar'];
}