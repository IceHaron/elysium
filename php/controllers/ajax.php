<?
/**
* 
* AJAX-контроллер, сюда мы будем прилетать только через AJAX-запросы
* 
**/

$noTemplate = TRUE; // Вырубаем шаблон
$rootfolder = isset($_SERVER['HOME']) ? $_SERVER['HOME'].'/elysiumgame' : $_SERVER['DOCUMENT_ROOT'];
require_once($rootfolder . '/php/classes/query.php');
$Query = new Query();

// Смотрим, с чем к нам пришли
switch ($_GET['mode']) {

	case 'getAchHtml':
		// Получить HTML-код для отображения всплывающей ачивки
		$achid = $_GET['id'];
		echo $Query->{$_GET['mode']}($achid);
	break;

	case 'pingServer':
		// Пингуем сервер
		$server = $db->escape($_GET['server']);
		echo $Query->{$_GET['mode']}($server);
	break;

	case 'voteTopCraft':
		// Получаем голос с topcraft.ru
		$timestamp = $_POST['timestamp']; // Передает время, когда человек проголосовал за проект
		$username = htmlspecialchars($_POST['username']); // Передает Имя проголосовавшего за проект
		echo $Query->{$_GET['mode']}($username, $timestamp);
	break;

	case 'voteFairTop':
		// Получаем голос с fairtop.ru
		$username = htmlspecialchars($_POST['player']); // Передает Имя проголосовавшего за проект
		$hash = $_POST['hash'];
		echo $Query->{$_GET['mode']}($username, $hash);
	break;

	case 'voteMCTop':
		// Получаем голос с mctop.im
		$nickname = $_POST['nickname'];
		$token = $_POST['token'];
		echo $Query->{$_GET['mode']}($nickname, $token);
	break;

	case 'MCRate':
		// Получаем голос с mcrate.su
		if (isset($_POST['nick']) && isset($_POST['hash'])) {
			$nickname = $db->escape(strip_tags($_POST['nick']));
			$token = $db->escape($_POST['hash']);
		}
		echo 'Nick: ' . $nickname . '<br/>';
		echo 'Your Hash: ' . $token . '<br/>';
		echo 'My Hash_: ' . md5(md5($nickname.'NUXGl04WfyhE0xugmcrate')) . '<br/>';
		echo $Query->{$_GET['mode']}($nickname, $token);
	break;

	case 'test':
		$ch = curl_init('http://elysiumgame.ru/ajax?mode=MCRate');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "nick=Ice_Haron&hash=638c2a9dd926d7fbd2ef188ec6cd5435");
		$res = curl_exec($ch);
		curl_close($ch);
		echo($res);
	break;

	default:
		// stealDiamond - Спереть алмаз
		// achCheck - Просмотреть неполученные достижения
		// checkBL - Получаем банлист
		echo $Query->{$_GET['mode']}();
	break;
}