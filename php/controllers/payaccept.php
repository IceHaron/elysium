<?
$InvId = $db->escape($_POST['InvId']);
$OutSum = $db->escape($_POST['OutSum']);
$SignatureValue = strtolower($_POST['SignatureValue']);
$Culture = $db->escape($_POST['Culture']);
$q = "SELECT `topay`, `paid` FROM `acquiring` WHERE `id` = $InvId";
$r = $db->query($q);

if ($r !== NULL) {
	$signPost = roboSignature(array($OutSum, $InvId), 'pay');
	$signBase = roboSignature(array($r[0]['topay'].'0000', $InvId), 'pay');
	$checkSum = ((float)$OutSum === (float)$r[0]['topay']);
	$checkPaid = ($r[0]['paid'] == 0);
	$checkSignature = ($signPost == $signBase && $signPost == $SignatureValue && $signBase == $SignatureValue);
	$message = 'Транзакция найдена';

	if ($checkSum && $checkPaid && $checkSignature) {
		$q = "UPDATE `acquiring` SET `paid` = 1 WHERE `id` = $InvId";
		$r = $db->query($q);
		$message .= '<br/>Спасибо за покупку изюма! Он будет выдан вам как только платеж будет завершен. <a href="/lk">Уйти в ЛК</a>';

	} else {
		// var_dump(trim($OutSum,'0'), $OutSum, $r[0]['topay']);
		if (!$checkSum) $message .= '<br/>Не совпадают суммы платежа';
		// var_dump($r[0]['paid']);
		if (!$checkPaid) $message .= '<br/>Транзакция уже оплачена';
		// var_dump($signBase, $signPost, $SignatureValue);
		if (!$checkSignature) $message .= '<br/>Не совпадают контрольные суммы';
	}

} else {
	$message = 'Такой транзакции не найдено';
}