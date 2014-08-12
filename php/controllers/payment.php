<?
/**
* 
* Оплата, покупка Изюма
* 
**/

$izum = $user->info['izumko'];

if (isset($_POST['was']) && isset($_POST['want'])) {
	$achievement->earn($user->info['id'], 18, 0);
	exit('В данный момент раздача и продажа изюма не работает, хитрец');
	// Если с постом, дак еще и с пополнением, то ты знаешь, что делать.
	if ((int)$_POST['want'] > 99999) $want = 99999;
	else if ((int)$_POST['want'] < 100) $want = 100;
	else $want = (int)$_POST['want'];

	$amount = (int)$izum + $want;
	// $q = "UPDATE `ololousers` SET `izumko` = $amount WHERE `id` = {$user->info['id']}";
	// $r = $db->query($q);

	if (!$r) exit('Произошла какая-то хрень <a href="/lk">Уйти в ЛК</a>');
	
	else {
		$achievement->earn($user->info['id'], 11, 0); // Добавил выдачу опыта за покупку Изюма, обязательно нужно будет убрать
		$message = 'Покупка прошла успешно <a href="/lk">Уйти в ЛК</a>';
	}
}
