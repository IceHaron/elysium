<?
/**
* 
* Личный кабинет, обитель юзверей
* 
**/

if (!isset($cemail)) header('Location: /'); // Как бы сложно посмотреть свой ЛК, не залогинившись, выбрасываем на главную.

$r = $db->query("SELECT `user`.`id`, `user`.`email`, `user`.`nick`, `user`.`mcname`, `user`.`steamid`, `user`.`exp`, `user`.`izumko`, `ref`.`nick` AS `referrer`
									FROM `ololousers` AS `user`
									LEFT JOIN `ololousers` as `ref` ON (`user`.`referrer` = `ref`.`id`)
									WHERE `user`.`id` = $cid");

// Собираем инфу о привязанной Steam-учетке, если он, конечно, есть
$steamID = isset($steamUser['uid']) ? $steamUser['uid'] : $r[0]['steamid'];

// Profile
if ($steamID) {
	$profile_str = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=0BE85074D210A01F70B48205C44D1D56&steamids=' . $steamID);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////Нужно будет обязательно после регистрации домена получить API-Key для стима//////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	$profile = json_decode($profile_str, TRUE);
	// Вот эта строка - и есть заглушка на случай если Steam недоступен
	if (!isset($profile['response']['players'][0])) $profile = array('response'=> array('players' => array(0 => array("personaname" => "Dummy", "profileurl" => "http://google.com", "avatar" => "http://placehold.it/32x32"))));

} else $profile = FALSE;

$exp = $r[0]['exp'];

// Хватаем опыт, вычисляем уровень учетки и пишем подпись
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
$achievements = $a->getAch($r[0]['id']); // Понятное дело: получаем список ачивок

// Пишем в массив
$info = array(
	  'email' => $r[0]['email'] // Мыло
	, 'nick' => $r[0]['nick'] // Ник
	, 'mcName' => $r[0]['mcname'] // Имя в майнкрафте
	, 'level' => $level['level'] // Уровень учетки
	, 'signature' => $signature // Подпись
	, 'percent' => $percent // Процент опыта
	, 'izum' => $r[0]['izumko'] // Баланс изюма
	, 'referrer' => $r[0]['referrer'] // Пригласивший
	, 'referral' => 'http://' . $_SERVER['HTTP_HOST'] . '/auth?action=reg&referrer=' . base64_encode($r[0]['id'] . '_' . $r[0]['nick']) // Реферральная ссылка
	, 'steamID' => $steamID // Steam ID, C.O.
	, 'achievements' => array_slice($achievements, 0, 5) // Последние 5 достижений
);

if ($profile) {
	// Добавляем инфу от Steam если к нам привязан их акк
	$info['steamName'] = $profile['response']['players'][0]['personaname'];
	$info['steamURL'] = $profile['response']['players'][0]['profileurl'];
	$info['avatar'] = $profile['response']['players'][0]['avatar'];
}
