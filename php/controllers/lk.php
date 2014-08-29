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
