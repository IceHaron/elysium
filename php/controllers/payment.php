<?
/**
* 
* Оплата
* 
**/

$izum = $user->info['izumko'];

if (isset($_POST['izum']) && isset($_POST['want']) && $cid) {
	$achievement->earn($user->info['id'], 18, 0);
	// exit('В данный момент раздача и продажа изюма не работает, хитрец');
	// Если с постом, дак еще и с пополнением, то ты знаешь, что делать.
	if ((int)$_POST['want'] > 9999999) $want = 9999999;
	else if ((int)$_POST['want'] < 100) $want = 100;
	else $want = (int)$_POST['want'];

	$amount = (int)$izum + $want;
	$q = "UPDATE `ololousers` SET `izumko` = $amount WHERE `id` = {$user->info['id']}";
	$r = $db->query($q);

	if (!$r) exit('Произошла какая-то хрень <a href="/lk">Уйти в ЛК</a>');
	
	else {
		$html = $achievement->earn($user->info['id'], 11, $want);
		$message = 'Покупка прошла успешно <a href="/lk">Уйти в ЛК</a>' . $html;
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

	$izum = $user->info['izumko'];

	if ($izum < $sum) {
		$message = 'Нехватает изюма. <a href="/donate">Вернуться</a>';

	} else {
		$remain = $izum - $sum;
		$insert = '';

		foreach ($items as $item) {
			$duration = $durations[$item];
			
			if ($duration == 0) $insert .= ",($cid, $item, now(), 0)";
			else {
				$end = time() + $duration;
				$insert .= ",($cid, $item, now(), $end)";
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

			if (isset($durations[10000])) {
				$html = $achievement->earn($cid, 25);
				$message .= '<br/>Большое вам спасибо за подарок! В качестве благодарности мы начислили вам символические 10 единиц опыта и выдали достижение' . $html;
			}

		} else $message = 'Что-то пошло не так.';
	}

} else $message = "Ничего не собираешься покупать? =(";
