<?
if (!$_POST) {
	$izum = $user->info['izumko'];
} else if (isset($_POST['was']) && isset($_POST['want'])) {
	$amount = (int)$_POST['was'] + (int)$_POST['want'];
	$q = "UPDATE `ololousers` SET `izumko` = $amount WHERE `id` = {$user->info['id']}";
	$r = $db->query($q);
	if (!$r) exit('Произошла какая-то хрень <a href="/lk">Уйти в ЛК</a>');
	else {
		$achievement = new achievement();
		$achievement->earn($user->info['id'], 11, floor((float)$_POST['want']/10)); // Добавил выдачу опыта за покупку Изюма, обязательно нужно будет убрать
		$message = 'Покупка прошла успешно <a href="/lk">Уйти в ЛК</a>';
	}
}
