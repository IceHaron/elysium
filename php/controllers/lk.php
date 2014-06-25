<?
/**
* 
* Личный кабинет, обитель юзверей
* 
**/

if (!isset($cemail)) header('Location: /'); // Как бы сложно посмотреть свой ЛК, не залогинившись, выбрасываем на главную.

$r = $db->query("SELECT `user`.`id`, `user`.`email`, `user`.`nick`, `user`.`mcname`, `user`.`steamid`, `user`.`exp`, `user`.`izumko`, `user`.`privacy`, `ref`.`nick` AS `referrer`
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
$privacy = privacyParse($r[0]['privacy']);

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
	, 'privacy' => $privacy // Настройки приватности в виде ассоциативного массива
);

if ($profile) {
	// Добавляем инфу от Steam если к нам привязан их акк
	$info['steamName'] = $profile['response']['players'][0]['personaname'];
	$info['steamURL'] = $profile['response']['players'][0]['profileurl'];
	$info['avatar'] = $profile['response']['players'][0]['avatar'];
}


/**
* 
* Функция разбора настроек приватности
* 
* Настройки приватности указаны в стиле Unix: трехзначным числом от 000 до 777, каждый знак означает уровень доступности сведений для:
* - Друзья пользователя
* - Зарегистрированные пользователи
* - Все в интернете
* 
* Информация бывает следующая:
* (0) Ник
* (0) Ник в игре
* (1) Накопленный опыт
* (1) Достижения
* (2) Привязанный Steam-аккаунт
* (4) Адрес электронной почты
* 
* Соответственно, Ник на сайте и Ник в майнкрафте показываются всегда, а видимость остального устанавливается в стиле Unix:
* 
* 0 - Видны только ники
* 1 - Видны ники, опыт и достижения
* 2 - Видны ники, привязанный аккаунт Steam
* 3 - Видны ники, опыт, достижения и привязанный Steam-аккаунт
* 4 - Видны ники и мыло
* 6 - Видны ники, привязанный Steam-аккаунт и мыло
* 7 - Видно все
* 
* Если пользователь устанавливает приватность 000, то он не виден в списке пользователей
* 
**/
function privacyParse($string) {
	$privacy = array('friends' => $string[0], 'registered' => $string[1], 'all' => $string[2]);

	foreach ($privacy as $key => $val) {
		switch ($val) {

			case 0:
				$privacy[$key] = array('exp_ach' => FALSE, 'steam' => FALSE, 'email' => FALSE);
			break; 

			case 1:
				$privacy[$key] = array('exp_ach' => TRUE, 'steam' => FALSE, 'email' => FALSE);
			break; 

			case 2:
				$privacy[$key] = array('exp_ach' => FALSE, 'steam' => TRUE, 'email' => FALSE);
			break; 

			case 3:
				$privacy[$key] = array('exp_ach' => TRUE, 'steam' => TRUE, 'email' => FALSE);
			break; 

			case 4:
				$privacy[$key] = array('exp_ach' => FALSE, 'steam' => FALSE, 'email' => TRUE);
			break; 

			case 6:
				$privacy[$key] = array('exp_ach' => FALSE, 'steam' => TRUE, 'email' => TRUE);
			break; 

			case 7:
				$privacy[$key] = array('exp_ach' => TRUE, 'steam' => TRUE, 'email' => TRUE);
			break; 

			default:
		}
	}
	return $privacy;
}