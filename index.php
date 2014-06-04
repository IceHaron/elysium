<?
/* Получение статуса сервера */
function status($ip, $port){
	if ($fp = @stream_socket_client("tcp://".$ip.":".$port,$e, $e1, 10)) {
		@stream_set_timeout($fp, 10);
		fwrite($fp,chr(0xFE));
		$shiza = fread($fp, 2048);
		$status = explode('§', substr($shiza,1));
		return $status;
		fclose ($fp);
	} else return null;
}

// Получаем статусы всех серверов
$ip='78.46.52.181';
$kernel = status($ip,25565);
$backtrack = status($ip,25566);
$gentoo = status($ip,25567);

REQUIRE_ONCE('php/classes/db.php');
REQUIRE_ONCE('php/classes/achievement.php');
$db = new db();

session_start();
// Логинимся
if (isset($_SESSION['login'])) {
	$cid = $_SESSION['id'];
	$clogin = $_SESSION['login'];
	$cemail = $_SESSION['email'];
} else $clogin = '';

// Определяем нужный модуль
$module = preg_replace('/\/|\?.+$/', '', $_SERVER['REQUEST_URI']);
if ($module == '') $module = 'news';
if (glob("php/controllers/$module.php")) INCLUDE_ONCE("php/controllers/$module.php");

// Подключаем основной макет
REQUIRE_ONCE('template/main.html');