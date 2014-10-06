<?
/**
* 
* Контроллер админки
* 
**/

$action = isset($_GET['action']) ? $_GET['action'] : NULL;

$mod = isset($_GET['mod']) ? $_GET['mod'] : 'index';

$output = '';

if ($mod == 'news') {

	if ($action == 'edit') {
		$r = $db->query("SELECT * FROM `news` WHERE `id` = {$_GET['id']}");
		$item = $r[0];
	}

	if (isset($_POST['action'])) {

		if ($_POST['action'] == 'add') {
			$title = str_replace("'", '&prime;', $_POST['title']);
			$intro = str_replace("'", '&prime;', $_POST['intro']);
			$text = str_replace("'", '&prime;', $_POST['text']);
			$q = "INSERT INTO `news` (`title`, `intro`, `text`) VALUES ('$title', '$intro', '$text')";
			$r = $db->query($q);

			if ($r === TRUE) $output .= '<h3>Новость добавлена</h3>';

		} else if ($_POST['action'] == 'edit') {
			$title = str_replace("'", '&prime;', $_POST['title']);
			$intro = str_replace("'", '&prime;', $_POST['intro']);
			$text = str_replace("'", '&prime;', $_POST['text']);
			$id = $_POST['id'];
			$q = "UPDATE `news` SET `title` = '$title', `intro` = '$intro', `text` = '$text' WHERE `id` = $id";
			$r = $db->query($q);

			if ($r === TRUE) $output .= '<h3>Новость сохранена</h3>';

		} else if ($_POST['action'] == 'Delete' && isset($_POST['item'])) {

			foreach ($_POST['item'] as $itemID => $on) {
				$delArr[] = $itemID;
			}

			$delIDs = implode(',', $delArr);
			$q = "DELETE FROM `news` WHERE `id` IN ($delIDs)";
			$r = $db->query($q);
		}

	}

	$q = "SELECT * FROM `news` ORDER BY `date` DESC";
	$news = $db->query($q);

} else if ($mod == 'users') {

	if ($action == 'edit') {
		$r = $db->query("SELECT * FROM `ololousers` WHERE `id` = {$_GET['id']}");
		foreach ($r[0] as $key => $value) {
			$item[$key] = $db->escape($value);
		}
	}

	if (isset($_POST['action'])) {

		if ($_POST['action'] == 'edit') {

			foreach ($_POST as $key => $value) {
				if (array_search($key, array('action', 'history', 'privacy')) === FALSE) $$key = $db->escape($_POST[$key]);
				else if (array_search($key, array('history', 'privacy')) !== FALSE) $$key = $_POST[$key];
			}
			$q = "
				UPDATE `ololousers` SET 
				  `email` = '$email'
				, `nick` = '$nick'
				, `mcname` = '$mcname'
				, `steamid` = '$steamid'
				, `exp` = '$exp'
				, `history` = '$history'
				, `referrer` = '$referrer'
				, `izumko` = '$izumko'
				, `privacy` = '$privacy'
				, `group` = '$group'
				WHERE `id` = $id";
			$r = $db->query($q);

			if ($r === TRUE) $output .= '<h3>Сохранено</h3>';

		} else if ($_POST['action'] == 'Delete' && isset($_POST['item'])) {

			foreach ($_POST['item'] as $itemID => $on) {
				$delArr[] = $itemID;
				$confirm = $db->query("SELECT `email`, `nick`, `pw` FROM `ololousers` WHERE `id` = $itemID");
				$forumnick = $confirm[0]['nick'];
				$forumemail = $confirm[0]['email'];
				$forumpw = $confirm['pw'];
				$salt = '9034u3ui';
				$key = str_replace(array('1','2','5','8','b','d','e','f'), '', md5($forumnick . substr($forumnick, 2)));

				$ch = curl_init('http://srv.elysiumgame.ru/');
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=del&user=$forumnick&email=$forumemail&pw=$forumpw&key=$key&salt=$salt");
				$res = curl_exec($ch);
				curl_close($ch);
			}

			$delIDs = implode(',', $delArr);
			$q = "DELETE FROM `ololousers` WHERE `id` IN ($delIDs);";
			$r = $db->query($q);
			$q = "DELETE FROM `tokens` WHERE `user` IN ($delIDs);";
			$r = $db->query($q);
			$q = "DELETE FROM `user_achievs` WHERE `user` IN ($delIDs);";
			$r = $db->query($q);

		}

	}

	$q = "SELECT * FROM `ololousers`";
	$users = $db->query($q);

} else if ($mod == 'mail') {
	$mailer->receive();

} else if ($mod == 'deleteusers') {
	$output .= deleteUsers();

} else if ($mod == 'changenametokens') {
	$q = "SELECT * FROM `ololousers`";
	$users = $db->query($q);
	$values = '';

	foreach ($users as $u) {
		$values .= ",('{$u['id']}', 'changename')";
	}
	
	$q = "INSERT INTO `tokens` (`user`, `action`) VALUES " . substr($values, 1);
	$db->query($q);
	$mod = 'changenametokens';

} else if ($mod == 'transfer') {
	$a = $db->query("DELETE FROM `user_achievs` WHERE `achievement` IN (5,10,11,13,21,30,42,50,70,100500);");
	$b = $db->query("UPDATE `ololousers` SET `izumko` = 0;");
	$c = $db->query("DELETE FROM `ololousers` WHERE `group` = 0;");
	$q = "SELECT `user_achievs`.`user` FROM `user_achievs` LEFT JOIN `ololousers` ON (`user_achievs`.`user` = `ololousers`.`id`) WHERE `ololousers`.`id` IS NULL GROUP BY `user`;";
	$r = $db->query($q);
	$str = '5';
	foreach ($r as $u) {
		$str .= ',' . $u['user'];
	}
	$d = $db->query("DELETE FROM `user_achievs` WHERE `user` IN ($str);");
	$q = "
		SELECT `ololousers`.`id`, SUM(`achievements`.`xpcost`) AS `xp`
		FROM `ololousers`
		JOIN `user_achievs` ON (`ololousers`.`id` = `user_achievs`.`user`)
		JOIN `achievements` ON (`achievements`.`id` = `user_achievs`.`achievement`)
		GROUP BY `ololousers`.`id`;";
	$r = $db->query($q);
	$e = $db->query('TRUNCATE TABLE `tokens`;');
	foreach ($r as $u) {
		$f = $db->query("UPDATE `ololousers` SET `exp` = {$u['xp']} WHERE `id` = {$u['id']};");
		$g = $db->query("INSERT INTO `tokens` VALUES ({$u['id']}, 'changename');");
		$h = $db->query("SELECT count(*) as `c` FROM `mail` WHERE `userid` = {$u['id']} AND `action` = 'refer'");
		$i = $db->query("SELECT count(*) as `c` FROM `ololousers` WHERE `referrer` = {$u['id']}");
		if ($h[0]['c'] >= 3 && $i[0]['c'] == 0) $achievement->earn($u['id'], 20);
	}

} else if ($mod == 'grantachievement') {
	$message = '';
	$q = "SELECT * FROM `achievements`;";
	$r = $db->query($q);
	$htmlach = '';

	foreach ($r as $ach) {
		$title = $ach['id'] . ' - ' . $ach['name'] . ' (' . $ach['xpcost'] . ')';
		// $aarr[ $ach['id'] ] = $title;
		$htmlach .= '<option value="' . $ach['id'] . '">' . $title . '</option>';
	}

	$q = "SELECT * FROM `ololousers`;";
	$r = $db->query($q);
	$htmluser = '';

	foreach ($r as $u) {
		$title = $u['id'] . ' - ' . $u['nick'] . ' (' . $u['mcname'] . ')';
		// $uarr[ $u['id'] ] = $title;
		$htmluser .= '<option value="' . $u['id'] . '">' . $title . '</option>';
	}

	if (isset($_POST['user']) && isset($_POST['ach']) && isset($_POST['exp'])) {
		$exp = intval($_POST['exp']);
		$result = $achievement->earn($_POST['user'], $_POST['ach'], $exp);
		$message = ($result === FALSE) ? 'Ачивка уже есть у пользователя' : 'Ачивка выдана.';
	}

} else if ($mod == 'grantizum') {

	$q = "SELECT * FROM `ololousers`;";
	$r = $db->query($q);
	$htmluser = '';

	foreach ($r as $u) {
		$title = $u['id'] . ' - ' . $u['nick'] . ' (' . $u['mcname'] . ')';
		// $uarr[ $u['id'] ] = $title;
		$htmluser .= '<option value="' . $u['id'] . '">' . $title . '</option>';
	}

	if (isset($_POST['user']) && isset($_POST['izum']) && isset($_POST['reason'])) {
		$izum = intval($_POST['izum']);
		$player = intval($_POST['user']);
		$reason = $db->escape($_POST['reason']);
		$q = "UPDATE `ololousers` SET `izumko` = `izumko` + $izum WHERE `id` = $player";
		$r = $db->query($q);
		if ($r) {
			$q = "INSERT INTO `gifts` (`admin`, `user`, `izum`, `reason`) VALUES ($cid, $player, $izum, '$reason')";
			$r = $db->query($q);
			if ($r) $message = 'Успех!';
			else $message = 'WTF?!';
		} else $message = 'WTF?!';
	} else $message = '';

} else if ($mod == 'syncSiteForumServer') {
	$output .= syncAccs();
}