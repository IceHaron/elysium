<?
if (!isset($cemail)) header('Location: /');
$r = $db->query("SELECT `user`.`id`, `user`.`email`, `user`.`nick`, `user`.`mcname`, `user`.`steamid`, `user`.`exp`, `ref`.`nick` AS `referrer`
									FROM `ololousers` AS `user`
									LEFT JOIN `ololousers` as `ref` ON (`user`.`referrer` = `ref`.`id`)
									WHERE `user`.`id` = $cid");

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
$level = $user->getLevel($r[0]['exp']);
if ($level['level'] < 70) {
	$percent = floor($level['exp'] / $level['need'] * 100);
	$signature = $level['exp'] . ' / ' . $level['need'] . ' exp (' . $percent . '%)';
} else {
	$percent = 100;
	$signature = $level['exp'] . ' exp';
}
$a = new achievement();
// $a->check($r[0]['id']);
$achievements = $a->getAch($r[0]['id']);
$info = array(
	  'email' => $r[0]['email']
	, 'nick' => $r[0]['nick']
	, 'mcName' => $r[0]['mcname']
	, 'level' => $level['level']
	, 'signature' => $signature
	, 'percent' => $percent
	, 'referrer' => $r[0]['referrer']
	, 'steamID' => $steamID
	, 'achievements' => array_slice($achievements, 0, 5)
);
if ($profile) {
	$info['steamName'] = $profile['response']['players'][0]['personaname'];
	$info['steamURL'] = $profile['response']['players'][0]['profileurl'];
	$info['avatar'] = $profile['response']['players'][0]['avatar'];
}
