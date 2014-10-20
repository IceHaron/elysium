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
		$nickname = htmlspecialchars($_POST['nickname']);
		$token = $_POST['token'];
		echo $Query->{$_GET['mode']}($nickname, $token);
	break;

	case 'test':
	var_dump('expression');
		$ch = curl_init('http://elysium.ice/ajax?mode=voteMCTop');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "nickname=Ice_Haron&token=Ololo Tr0|oIo");
		$res = curl_exec($ch);
		curl_close($ch);
		var_dump($res);
	break;

	default:
		// stealDiamond - Спереть алмаз
		// achCheck - Просмотреть неполученные достижения
		// checkBL - Получаем банлист
		echo $Query->{$_GET['mode']}();
	break;
}