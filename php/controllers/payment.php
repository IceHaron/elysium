<?
/**
* 
* Оплата
* 
**/

$izum = $user->info['izumko'];
$rubCost = 1000;

if (isset($_POST['izum']) && isset($_POST['want']) && $clogin) {
	$achievement->earn($user->info['id'], 18, 0);
	// exit('В данный момент раздача и продажа изюма не работает, хитрец');
	// Если с постом, дак еще и с пополнением, то ты знаешь, что делать.
	if ((int)$_POST['want'] > 9999999) $want = 9999999;
	else if ((int)$_POST['want'] < 100) $want = 100;
	else $want = (int)$_POST['want'];

	$toPay = number_format(ceil($want / $rubCost * 100) / 100, 2, '.', '');

	$q = "INSERT INTO `acquiring` (`user`, `topay`, `togrant`) VALUES ($cid, $toPay, $want);";
	$r = $db->query($q);
	// $r = TRUE;

	// $q = "UPDATE `ololousers` SET `izumko` = $amount WHERE `id` = {$user->info['id']}";
	// $r = $db->query($q);

	if ($r === FALSE) exit('Произошла какая-то хрень <a href="/lk">Уйти в ЛК</a>');
	
	else {
		$q = "SELECT `id` FROM `acquiring` WHERE `user` = $cid AND `paid` = 0 ORDER BY `id` DESC LIMIT 1;";
		$r = $db->query($q);
		$transactionID = $r[0]['id'];
		$signature = roboSignature(array("Elysium", $toPay, $transactionID), 'pay');

		$acquiring = array(
			  'MrchLogin' => 'Elysium'
			, 'OutSum' => $toPay
			, 'InvId' => $transactionID
			, 'Desc' => "Покупка $want izum"
			, 'SignatureValue' => $signature
			, 'Culture' => 'ru'
		);
		// print_r($acquiring);
		$target = "https://merchant.roboxchange.com/Index.aspx?MerchantLogin=Elysium&OutSum=$toPay&InvId=$transactionID&Desc=Покупка%20$want%20izum&SignatureValue=$signature&Culture=ru";
		$message = 'Ваш заказ.';
		$izumform = '
			<p>Внимательно проверьте все данные и нажмите "согласен, оплатить" если все верно.</p>
			<table id="cheque">
				<tr>
					<th>Вы покупаете izum в количестве</th>
					<td>' . $want . '</td>
				</tr>
				<tr>
					<th>Стоимость без учета комиссий, собираемых платежной системой</th>
					<td>' . $toPay . ' руб</td>
				</tr>
			</table>
			<form action="https://merchant.roboxchange.com/Index.aspx" method="POST">
				<input type="hidden" name="MerchantLogin" value="Elysium">
				<input type="hidden" name="OutSum" value="' . $toPay . '">
				<input type="hidden" name="InvId" value="' . $transactionID . '">
				<input type="hidden" name="Desc" value="Покупка ' . $want . ' izum">
				<input type="hidden" name="SignatureValue" value="' . $signature . '">
				<input type="hidden" name="Culture" value="ru">
				<input type="submit" value="Согласен, оплатить">
			</form>';
		// header("Location: $target");
		// $ch = curl_init('http://test.robokassa.ru/Index.aspx');
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, "MerchantLogin=Elysium&OutSum=$toPay&InvId=$transactionID&Desc=Покупка%20$want%20izum&SignatureValue=$signature&Culture=ru");
		// curl_exec($ch);
		// curl_close($ch);
		// $message = 'Что-то пошло не так';
	}

} else if (isset($_POST['goods']) && isset($_POST['donut']) && $clogin) {

	foreach ($_POST['donut'] as $id => $donut) {
		$items[] = intval($id);
	}

	$idStr = implode(',', $items);
	$q = "SELECT `id`, `cost`, `duration` FROM `donuts` WHERE `id` IN ($idStr);";
	$r = $db->query($q);
	$sum = 0;

	foreach ($r as $item) {
		$sum += $item['cost'];
		$durations[ $item['id'] ] = $item['duration'];
	}

	if ($izum < $sum) {
		$message = 'Не хватает изюма. <a href="/donate">Вернуться</a>';

	} else {
		$remain = $izum - $sum;
		$insert = '';

		foreach ($items as $item) {
			$duration = $durations[$item];
			
			if ($duration == 0) $insert .= ",($cid, $item, now(), 0)";
			else {
				$end = time() + $duration;
				$insert .= ",($cid, $item, now(), from_unixtime($end))";
			}

		}

		$q = "UPDATE `ololousers` SET `izumko` = $remain WHERE `id` = $cid;";
		$paid = $db->query($q);

		if ($paid === TRUE) {
			writeHistory($cid, 'purchase', array($idStr => time()));
			$q = "INSERT INTO `purchases` (`user`, `item`, `start`, `end`) VALUES " . substr($insert, 1);
			$purchase = $db->query($q);
		}
		
		if ($purchase === TRUE && $paid === TRUE) {

			$message = 'Спасибо за покупку! Оплаченные товары будут активированы в ближайшее время';

			if (isset($durations[10000])) {
				$html = $achievement->earn($cid, 25);
				$message .= '<br/>Большое вам спасибо за подарок! В качестве благодарности мы начислили вам символические 10 единиц опыта и выдали достижение' . $html;
			}

		} else $message = 'Что-то пошло не так.';
	}

} else $message = "Ничего не собираешься покупать? =(";
