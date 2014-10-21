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
if (isset($_POST['prefix'])) {
	$prefix = preg_replace('/\&amp\;/', '&', $db->escape($_POST['prefix']));
	$nameColor = preg_replace('/\&amp\;/', '&', $db->escape($_POST['nameColor']));
	if (!$nameColor) $nameColor = 'f';
	$q = "UPDATE `ololousers` SET `prefix` = '&r[{$prefix}&r] &{$nameColor}' WHERE `id` = $cid;";
	$r = $db->query($q);
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

} else {
	$nick = $info['nick'];
}

$mcnick = $info['mcName'];
$q = "SELECT * FROM `acquiring` WHERE `user` = $cid AND `paid` != -1 ORDER BY `date` DESC LIMIT 0,10";
$orders = $db->query($q);
$statusList = array(
	  '0' => '<span style="color: blue">Ожидает вашего подтверждения</span>'
	, '1' => '<span style="color: red">Подтвержден, не оплачен</span>'
	, '2' => '<span style="color: green">Оплачен</span>'
);