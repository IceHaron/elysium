<?
/**
* 
* AJAX-контроллер, сюда мы будем прилетать только через AJAX-запросы
* 
**/

$noTemplate = TRUE; // Вырубаем шаблон
require_once('/php/classes/query.php');
$Query = new Query();

// Смотрим, с чем к нам пришли
switch ($_GET['mode']) {

	case 'achCheck':
		// Просмотреть неполученные достижения
		echo $Query->{$_GET['mode']}();
	break;

	case 'getAchHtml':
		// Получить HTML-код для отображения всплывающей ачивки
		$achid = $_GET['id'];
		echo $Query->{$_GET['mode']}($achid);
	break;

	case 'stealDiamond':
		// Спереть алмаз
		echo $Query->{$_GET['mode']}();
	break;

	case 'pingServer':
		// Пингуем сервер
		$server = $db->escape($_GET['server']);
		echo $Query->{$_GET['mode']}($server);
	break;

	case 'checkBL':
		// Получаем банлист
		echo $Query->{$_GET['mode']}();
	break;

	case 'voteTopCraft':
		// Получаем голос с topcraft.ru
		$timestamp = $_POST['timestamp']; // Передает время, когда человек проголосовал за проект
		$username = htmlspecialchars($_POST['username']); // Передает Имя проголосовавшего за проект
		echo $Query->{$_GET['mode']}($username, $timestamp);
	break;

	case 'voteFairTop':
		// Получаем голос с fairtop.ru
		$timestamp = $_POST['timestamp']; // Передает время, когда человек проголосовал за проект
		$username = htmlspecialchars($_POST['player']); // Передает Имя проголосовавшего за проект
		$hash = $_POST['hash'];
		echo $Query->{$_GET['mode']}($username, $timestamp, $hash);
	break;

	default:
		// Какая-то хрень
		echo 'wrong mode';
	break;
}