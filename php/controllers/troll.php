<?
/**
* 
* Контроллер админки
* 
**/

$action = isset($_GET['action']) ? $_GET['action'] : NULL;

if (!isset($_GET['mod'])) {
	$mod = 'index';

} else if ($_GET['mod'] == 'news') {

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

			if ($r === TRUE) echo '<h3>Новость добавлена</h3>';

		} else if ($_POST['action'] == 'edit') {
			$title = str_replace("'", '&prime;', $_POST['title']);
			$intro = str_replace("'", '&prime;', $_POST['intro']);
			$text = str_replace("'", '&prime;', $_POST['text']);
			$id = $_POST['id'];
			$q = "UPDATE `news` SET `title` = '$title', `intro` = '$intro', `text` = '$text' WHERE `id` = $id";
			$r = $db->query($q);

			if ($r === TRUE) echo '<h3>Новость сохранена</h3>';

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
	$mod = 'news';

} else if ($_GET['mod'] == 'users') {

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

			if ($r === TRUE) echo '<h3>Сохранено</h3>';

		} else if ($_POST['action'] == 'Delete' && isset($_POST['item'])) {

			foreach ($_POST['item'] as $itemID => $on) {
				$delArr[] = $itemID;
			}

			$delIDs = implode(',', $delArr);
			$q = "DELETE FROM `ololousers` WHERE `id` IN ($delIDs)";
			$r = $db->query($q);
		}

	}

	$q = "SELECT * FROM `ololousers`";
	$users = $db->query($q);
	$mod = 'users';

} else if ($_GET['mod'] == 'mail') {
	$mailer->receive();
	$mod = 'mail';

} else if ($_GET['mod'] == 'changenametokens') {
	$q = "SELECT * FROM `ololousers`";
	$users = $db->query($q);
	$values = '';

	foreach ($users as $u) {
		$values .= ",('{$u['id']}', 'changename')";
	}
	
	$q = "INSERT INTO `tokens` (`user`, `action`) VALUES " . substr($values, 1);
	$db->query($q);
	$mod = 'changenametokens';
}