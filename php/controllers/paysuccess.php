<?
$noTemplate = TRUE; // Вырубаем шаблон
$InvId = $db->escape($_POST['InvId']);
$OutSum = $_POST['OutSum'];
$SignatureValue = strtolower($_POST['SignatureValue']);
$q = "SELECT `user`, `topay`, `paid`, `togrant` FROM `acquiring` WHERE `id` = $InvId";
$r = $db->query($q);

if ($r !== NULL) {
	$player = $r[0]['user'];
	$q = "SELECT `izumko` FROM `ololousers` WHERE `id` = $player";
	$p = $db->query($q);
	$izum = $p[0]['izumko'];
	$want = $r[0]['togrant'];
	$signPost = roboSignature(array($OutSum, $InvId), 'receive');
	$signBase = roboSignature(array(number_format($r[0]['topay'], 6, '.', ''), $InvId), 'receive');
	$checkSum = ((float)$OutSum === (float)$r[0]['topay']);
	$checkPaid = ($r[0]['paid'] == 0 || $r[0]['paid'] == 1);
	$checkSignature = (/*$signPost == $signBase && */$signPost == $SignatureValue/* && $signBase == $SignatureValue*/);
	$message = 'Транзакция найдена';

	if ($checkSum && $checkPaid && $checkSignature) {
		$q = "UPDATE `acquiring` SET `paid` = 2 WHERE `id` = $InvId";
		$r = $db->query($q);
		$totalIzum = $izum + $want;
		$q = "UPDATE `ololousers` SET `izumko` = $totalIzum WHERE `id` = $player";
		$r = $db->query($q);
		// writeHistory($player, 'purchase', array('izum' => time()));
		echo 'OK' . $InvId;
		$html = $achievement->earn($player, 11);
		$message .= '<br/>Платеж проведен успешно';
		if ($want >= 100000) giveBonus($player, $want, 'buy', "Бонус за покупку $want Изюма");
		$q = "SELECT sum(`togrant`) AS `sum` FROM `acquiring` WHERE `user` = $player AND `paid` = 2";
		$r = $db->query($q);
		if ($r[0]['sum'] >= 1000000) $achievement->earn($player, 31);
		if ($r[0]['sum'] >= 1500000) $achievement->earn($player, 32);

	} else {
		// var_dump($OutSum, $r[0]['topay']);
		if (!$checkSum) $message .= '<br/>Не совпадают суммы платежа';
		// var_dump($r[0]['paid']);
		if (!$checkPaid) $message .= '<br/>Транзакция уже оплачена';
		// var_dump($signBase, $signPost, $SignatureValue);
		if (!$checkSignature) $message .= '<br/>Не совпадают контрольные суммы';
	}

} else {
	$message = 'Такой транзакции не найдено';
}


// echo '<h1>' . $message . '</h1>';