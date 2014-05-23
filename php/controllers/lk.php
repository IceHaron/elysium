<?
$r = $db->query("SELECT `user`.`email`, `user`.`nick`, `user`.`mcname`, `user`.`steamid`, `user`.`exp`, `ref`.`nick` AS `referrer`
									FROM `ololousers` AS `user`
									JOIN `ololousers` as `ref` ON (`user`.`referrer` = `ref`.`id`)
									WHERE `user`.`nick` = '$clogin' AND `user`.`email` = '$cemail'");
// Profile
$profile_str = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=0BE85074D210A01F70B48205C44D1D56&steamids=' . $r[0]['steamid']);
$profile = json_decode($profile_str, TRUE);
// Вот эта строка - и есть заглушка на случай если стим недоступен
if (!isset($profile['response']['players'][0])) $profile = array('response'=> array('players' => array(0 => array("personaname" => "Dummy", "profileurl" => "http://google.com", "avatar" => "http://placehold.it/32x32"))));
$exp = $r[0]['exp'];
$remain = $exp;
$expForLevel = 100;
$multiplier = 1.15;
$level = 1;
do {
	$level++;
	$remain = $exp - $expForLevel;
	$exp = $remain;
	$expForLevel = ceil($expForLevel * $multiplier);
} while ($remain > $expForLevel && $level < 70);
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
	, 'steamName' => $profile['response']['players'][0]['personaname']
	, 'steamURL' => $profile['response']['players'][0]['profileurl']
	, 'avatar' => $profile['response']['players'][0]['avatar']
);
