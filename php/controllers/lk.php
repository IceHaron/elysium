<?
/**
* 
* Личный кабинет, обитель юзверей
* 
**/

if (!isset($cemail)) {
	header('Location: /'); // Как бы сложно посмотреть свой ЛК, не залогинившись, выбрасываем на главную.
	exit;
}
$info = $user->getFullInfo($cid);

$translate = array(
		  'friends' => 'Друзья (пока что пригласивший и те, кого пригласили вы)'
		, 'reg' => 'Зарегистрированные пользователи'
		, 'all' => 'Все в интернете'
		, 'exp' => 'Опыт'
		, 'ach' => 'Достижения'
		, 'steam' => 'Привязанный аккаунт Steam'
	);

if ((int)$info['tokens']['changename'] > 0) {
	$nick = $info['nick'] . '&nbsp;<a href="/auth?action=sitenick">Сменить</a>';
	$mcnick = $info['mcName'] . '&nbsp;<a href="/auth?action=mcnick">Сменить</a>';
} else {
	$nick = $info['nick'];
	$mcnick = $info['mcName'];
}

$forumnick = $info['nick'];
$forumemail = $info['email'];
$p = $db->query("SELECT `pw` FROM `ololousers` WHERE `nick` = '$forumnick' AND `email` = '$forumemail'");
$forumpw = $p[0]['pw'];
$salt = '9034u3ui';
$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));

$ch = curl_init('http://srv.elysiumgame.ru/');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$forumnick&email=$forumemail&pw=$forumpw&key=$key&salt=$salt");
$res = curl_exec($ch);
curl_close($ch);

