<?
$InvId = $db->escape($_POST['InvId']);
$OutSum = $db->escape($_POST['OutSum']);
$SignatureValue = strtolower($_POST['SignatureValue']);
$Culture = $db->escape($_POST['Culture']);
$q = "SELECT `user`, `topay`, `paid`, `togrant`, `discount` FROM `acquiring` WHERE `id` = $InvId";
$r = $db->query($q);
$discountID = $r[0]['discount'];

if ($r !== NULL && (float)$OutSum != 0) {
	$signPost = roboSignature(array($OutSum, $InvId), 'pay');
	$signBase = roboSignature(array(number_format($r[0]['topay'], 6, '.', ''), $InvId), 'pay');
	$checkSum = ((float)$OutSum === (float)$r[0]['topay']);
	$checkPaid = ($r[0]['paid'] == 0);
	$checkSignature = (/*$signPost == $signBase && */$signPost == $SignatureValue/* && $signBase == $SignatureValue*/);
	$message = 'Транзакция найдена';

	if ($checkSum && $checkPaid && $checkSignature) {
		$q = "UPDATE `acquiring` SET `paid` = 1 WHERE `id` = $InvId";
		$r = $db->query($q);
		$message .= '<br/>Спасибо за покупку изюма! Он будет выдан вам как только платеж будет завершен. <a href="/lk">Уйти в ЛК</a>';

	} else {
		// var_dump($OutSum, $r[0]['topay']);
		if (!$checkSum) $message .= '<br/>Не совпадают суммы платежа';
		// var_dump($r[0]['paid']);
		if (!$checkPaid) $message .= '<br/>Транзакция уже оплачена';
		// var_dump($signBase, $signPost, $SignatureValue);
		if (!$checkSignature) $message .= '<br/>Не совпадают контрольные суммы';
	}

} else if ($r !== NULL && (float)$OutSum == 0) {
	$player = $r[0]['user'];
	$q = "SELECT `izumko` FROM `ololousers` WHERE `id` = $player";
	$p = $db->query($q);
	$izum = $p[0]['izumko'];
	$want = $r[0]['togrant'];
	$message = 'Транзакция найдена';

	$q = "SELECT `name`, `active` FROM `coupons` WHERE `id` = $discountID";
	$c = $db->query($q);
	$checkPaid = ($r[0]['paid'] == 0);
	$checkActiveDiscount = ($c[0]['active'] == 1);

	if ($checkPaid && $checkActiveDiscount) {
		if ($c[0]['name'] != 'admindiscount') {
			$q = "UPDATE `coupons` SET `active` = 0, `until` = now() WHERE `id` = $discountID";
			$d = $db->query($q);
		}

		$q = "UPDATE `acquiring` SET `paid` = 2 WHERE `id` = $InvId";
		$r = $db->query($q);
		$totalIzum = $izum + $want;
		$q = "UPDATE `ololousers` SET `izumko` = $totalIzum WHERE `id` = $player";
		$r = $db->query($q);
		// writeHistory($player, 'purchase', array('izum' => time()));
		$html = $achievement->earn($player, 11);
		$message .= '<br/>Платеж проведен успешно';
		giveBonus($player, $want, 'buy', "Бонус за покупку $want Изюма");

	} else {
		if (!$checkPaid) $message .= '<br/>Транзакция уже оплачена';
		if (!$checkActiveDiscount) $message .= '<br/>Скидка уже недействительна';
	}

} else {
	$message = 'Такой транзакции не найдено';
}