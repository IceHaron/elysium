<?
$InvId = $db->escape($_POST['InvId']);
$OutSum = $db->escape($_POST['OutSum']);
$Culture = $db->escape($_POST['Culture']);
$q = "SELECT `topay`, `paid` FROM `acquiring` WHERE `id` = $InvId";
$r = $db->query($q);

if ($r !== NULL) {
	$checkSum = ((float)$OutSum === (float)$r[0]['topay']);
	$message = 'Транзакция найдена';

	if ($checkSum) {
		$q = "UPDATE `acquiring` SET `paid` = -1 WHERE `id` = $InvId";
		$r = $db->query($q);
		$message .= '<br/>Вы отказались от покупки, транзакция уничтожена. <a href="/lk">Уйти в ЛК</a>';

	} else {
		// var_dump($OutSum, $r[0]['topay']);
		$message .= '<br/>Не совпадают суммы платежа';
	}

} else {
	$message = 'Такой транзакции не найдено';
}