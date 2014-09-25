<?
/**
* 
* AJAX-контроллер, сюда мы будем прилетать только через AJAX-запросы
* 
**/

$noTemplate = TRUE; // Вырубаем шаблон

// Смотрим, с чем к нам пришли
switch ($_GET['mode']) {

	case 'achCheck':
		// Просмотреть неполученные достижения
		echo $achievement->check();
	break;

	case 'getAchHtml':
		// Получить HTML-код для отображения всплывающей ачивки
		$q = "SELECT * FROM `achievements` WHERE `id` = {$_GET['id']}";
		$r = $db->query($q);
		$achievement = $r[0];
		$output = '
		<div class="achievementWrapper" id="ach-' . $_GET['id'] . '">
			<div class="achievement grade_' . $achievement['grade'] . '">
				<span class="achTitle">' . $achievement['name'] . '</span>
				<span class="achDate">' . date('Y-m-d H:i:s') . '</span>
				<span class="achDesc">' . $achievement['desc'] . '</span>
			</div>
		</div>';
		echo $output;
	break;

	case 'stealDiamond':
		if (isset($cid)) $achievement->earn($cid, 17);
		echo 'Diamond stolen! RUN!';
	break;

	case 'pingServer':
		$server = $db->escape($_GET['server']);
		// Получаем статусы всех серверов
		$ip = '144.76.111.114';
		$port = array('kernel' => 25565, 'backtrack' => 25566, 'gentoo' => 25567);
		$status = pingMCServer($ip, $port[$server]);
		// $backtrack = pingMCServer($ip,25566);
		// $gentoo = pingMCServer($ip,25567);
		$output = json_encode(array('players' => (int)str_replace(chr(0), '', $status[3]), 'limit' => (int)str_replace(chr(0), '', $status[4])));
		echo $output;
	break;

	case 'checkBL':
		$l = mysqli_connect('144.76.111.114', 'site', 'u94fmE4KrxeLP5Pe', 'server', '3306');
		mysqli_set_charset($l, "utf8");
		$q = "SELECT * FROM `banlist` LIMIT 0,10";
		$b = mysqli_query($l, $q);
		while ($ban = mysqli_fetch_assoc($b)) {
			$bans[] = array('player' => $ban['name'], 'reason' => $ban['reason'], 'admin' => $ban['admin'], 'ban' => date("d-m-Y H:i", $ban['time']), 'unban' => ($ban['temptime'] ? date("d-m-Y H:i", $ban['temptime']) : 'Навечно'));
		}
		echo json_encode($bans);
	break;

	default:
		// Какая-то хрень
		echo 'wrong mode';
	break;
}