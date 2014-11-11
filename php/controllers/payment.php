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
	if ((int)$_POST['want'] > 9999999) $want = 9999999;
	else if ((int)$_POST['want'] < 100) $want = 100;
	else $want = (int)$_POST['want'];

	$discountID = intval($_POST['izumDiscount']);
	$q = "SELECT `discounts`.`effect` FROM `coupons` JOIN `discounts` ON (`coupons`.`discount` = `discounts`.`id`) WHERE `coupons`.`id` = $discountID;";
	$r = $db->query($q);
	$discount = (float)$r[0]['effect'];

	$toPay = number_format(ceil($want / $rubCost * (100 - $discount)) / 100, 2, '.', '');

	$q = "INSERT INTO `acquiring` (`user`, `topay`, `togrant`, `discount`) VALUES ($cid, $toPay, $want, $discountID);";
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
			  'MerchantLogin' => 'Elysium'
			, 'OutSum' => $toPay
			, 'InvId' => $transactionID
			, 'Desc' => "Покупка $want izum"
			, 'SignatureValue' => $signature
			, 'Culture' => 'ru'
		);
		// print_r($acquiring);
		$target = "https://merchant.roboxchange.com/Index.aspx?MerchantLogin=Elysium&OutSum=$toPay&InvId=$transactionID&Desc=Покупка%20$want%20izum&SignatureValue=$signature&Culture=ru";
		if ((float)$toPay != 0) $action = 'https://merchant.roboxchange.com/Index.aspx';
		else $action = "/payaccept";
		$message = 'Ваш заказ';
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
			<form action="' . $action . '" method="POST">
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

} else if (isset($_POST['goods']) && (isset($_POST['donut']) || isset($_POST['status'])) && $clogin) {

	$message = '';
	$discount = 0;
	$stackDisc = array(0 => 1);
	$discGroup = 0;
	$discUsed = array();
	$disc = $db->escape($_POST['goodDiscount']);
	$sDisc = $_POST['stackDiscount'];

	if ($disc != '0') {
		if ($disc == 'votediscount') {
			$q = "
				SELECT sum(`discounts`.`effect`) AS `effect`
				FROM `coupons`
				JOIN `discounts` ON (`coupons`.`discount` = `discounts`.`id`)
				WHERE `coupons`.`user` = $cid AND `coupons`.`discount` = 1 AND `coupons`.`active` = 1;";
			$r = $db->query($q);
			if (isset($r[0]['effect'])) {
				$discount = $r[0]['effect'];
				$discGroup = 0;
			}
		} else {
			$q = "
				SELECT `discounts`.`group`, `discounts`.`effect`
				FROM `coupons`
				JOIN `discounts` ON (`coupons`.`discount` = `discounts`.`id`)
				WHERE `coupons`.`user` = $cid AND `coupons`.`id` = $disc AND `coupons`.`active` = 1;";
			$r = $db->query($q);
			if (isset($r[0]['effect'])) {
				$discount = $r[0]['effect'];
				$discGroup = $r[0]['group'];
			}
		}
	}

	$multiplier = 1 - (float)$discount / 100;

	if (!empty($sDisc)) {
		$s = implode(',', $sDisc);
		$q = "
			SELECT `coupons`.`id`, `discounts`.`group`, `discounts`.`effect`
			FROM `coupons`
			JOIN `discounts` ON (`coupons`.`discount` = `discounts`.`id`)
			WHERE `coupons`.`user` = $cid AND `coupons`.`id` IN ($s) AND `coupons`.`active` = 1;";
		$r = $db->query($q);
		foreach ($r as $coupon) {
			if (!isset($stackDisc[ $coupon['group'] ])) $stackDisc[ $coupon['group'] ] = 1 - (float)$coupon['effect'] / 100;
			else $stackDisc[ $coupon['group'] ] *= 1 - (float)$coupon['effect'] / 100;
			$discUsed[] = $coupon['id'];
		}
	}

	$q = "SELECT * FROM `donuts`";
	$r = $db->query($q);

	foreach ($r as $donut) {
		$donuts[ $donut['id'] ] = $donut;
	}

	if (isset($_POST['donut'])) {
		foreach ($_POST['donut'] as $id => $donut) {
			$items[] = intval($id);
		}
	}
	
	if (isset($_POST['status'])) $items[] = intval($_POST['status']);

	$hasStatus = FALSE;
	$q = "SELECT `item`, `end` FROM `purchases` WHERE `user` = $cid ORDER BY `end` ASC";
	$r = $db->query($q);
	
	foreach ($r as $purchase) {
		$purchases[ $purchase['item'] ] = $purchase;
		
		if ($donuts[ $purchase['item'] ]['group'] == 2 && strtotime($purchase['end']) > time()) {
			$hasStatus = TRUE;
			$statusEnd = $purchase['end'];
		}
		
	}
	
	$sum = 0;

	foreach ($items as $item) {

		if ($discGroup == 0 || $discGroup == $donuts[$item]['group']) {
			$cost = ceil($multiplier * $donuts[$item]['cost']);
		} else $cost = $donuts[$item]['cost'];

		$cost *= $stackDisc[0] * (isset($stackDisc[ $donuts[$item]['group'] ]) ? $stackDisc[ $donuts[$item]['group'] ] : 1);
		$cost = ceil($cost);

		$sum += $cost;
	}

	if ($izum < $sum) {
		$message .= 'Не хватает изюма. <a href="/donate">Вернуться</a>';

	} else {
		$insert = '';
		$coupons = '';
		$notgiven = '';
		$sum = 0;

		foreach ($items as $item) {
			$duration = $donuts[$item]['duration'];
			$group = $donuts[$item]['group'];

			if ($discGroup == 0 || $discGroup == $group) $cost = ceil($multiplier * $donuts[$item]['cost']);
			else $cost = $donuts[$item]['cost'];

			$cost *= $stackDisc[0] * (isset($stackDisc[$group]) ? $stackDisc[$group] : 1);
			$cost = ceil($cost);
			
			if ($duration == 0) {
			
				if (($item == 10003 || $item == 10004) && isset($purchases[$item])) {
					$notgiven .= $donuts[$item]['name'] . ' &mdash; <span style="color: red">Уже куплено</span><br/>';
				} else {
					$insert .= ",($cid, $item, $cost, now(), 0)";
					$sum += $cost;
				}
				
			} else {
				if ($group == 2 && $hasStatus) {
					$start = $statusEnd;

				} else if (isset($purchases[$item]) && strtotime($purchases[$item]['end']) > time()) {
					$start = $purchases[$item]['end'];
					
				} else {
					$start = date('Y-m-d H:i:s', time());
				}
				
				$end = strtotime($start);
				$end += $duration;
				$insert .= ",($cid, $item, $cost, '$start', from_unixtime($end))";
				giveCoupon($cid, 2, NULL, date('Y-m-d H:i:s', $end));
				$sum += $cost;
			}

		}

		if ($notgiven && !$insert) {
			$message .= 'Все выбранные товары уже приобретены, покупка не совершена.';
		} else {
			$remain = $izum - $sum;
			$q = "UPDATE `ololousers` SET `izumko` = $remain WHERE `id` = $cid;";
			$paid = $db->query($q);
			$killedCoupon = FALSE;
			$killedStackCoupon = FALSE;

			if ($disc == 'votediscount') {
				$q = "UPDATE `coupons` SET `active` = 0, `until` = now() WHERE `user` = $cid AND `discount` = 1;";
				$killedCoupon = $db->query($q);
			} else {
				$discUsed[] = $disc;
			}

			$ids = implode(',', $discUsed);
			$q = "UPDATE `coupons` SET `active` = 0, `until` = now() WHERE `id` IN ($ids);";
			$killedStackCoupon = $db->query($q);

			if ($paid === TRUE && ($killedCoupon === TRUE || $killedStackCoupon === TRUE)) {
				$q = "INSERT INTO `purchases` (`user`, `item`, `cost`, `start`, `end`) VALUES " . substr($insert, 1);
				$purchase = $db->query($q);
			}
			
			if ($purchase === TRUE && $paid === TRUE) {
				$message .= 'Спасибо за покупку! Оплаченные товары будут активированы в ближайшее время';
				if ($notgiven) $message .= '<br/>Следующие товары исключены из покупки:<br/>' . $notgiven;
				if (in_array(10000, $items)) {
					$html = $achievement->earn($cid, 25);
					$message .= '<br/>Большое вам спасибо за подарок! В качестве благодарности мы начислили вам символические 10 единиц опыта и выдали достижение' . $html;
				}
				
				if (($killedCoupon !== TRUE && $killedStackCoupon !== TRUE)) $message .= '<br/>Скидка не использована.<br/>';

				$message .= '<br/><br/>Сумма вашей покупки: ' . $sum . ' Izum';

			} else $message .= 'Что-то пошло не так.';
		}
	}

} else $message = "Ничего не собираешься покупать? =(";
