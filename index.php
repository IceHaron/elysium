<?
/* Получение статуса сервера, наследие прошлой жизни */
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

// Получаем статусы всех серверов, это тоже - наследие
$ip='78.46.52.181';
$kernel = status($ip,25565);
$backtrack = status($ip,25566);
$gentoo = status($ip,25567);
$postfix = '';

// Подключаем классы...
REQUIRE_ONCE('php/classes/achievement.php'); // ...для работы с ачивками
REQUIRE_ONCE('php/classes/db.php'); // ...для работы с базой
REQUIRE_ONCE('php/classes/mail.php'); // ...для почтамта
REQUIRE_ONCE('php/classes/user.php'); // ...для работы с пользователями
$db = new db(); // ...создаем экземпляр
$mailer = new mail(); // ...создаем экземпляр

session_start();
// Логинимся
if (isset($_SESSION['login'])) {
	$user = new user();
	$cid = $user->info['id'];
	$clogin = $user->info['nick'];
	$cemail = $user->info['email'];
} else $clogin = '';

// Определяем нужный модуль, переменная используется прямо в макете /templates/main.html
$module = preg_replace('/\/|\?.+$/', '', $_SERVER['REQUEST_URI']);
if ($module == '') $module = 'news';
// Подгружаем контроллер, если таковой существует
if (glob("php/controllers/$module.php")) INCLUDE_ONCE("php/controllers/$module.php");

// Подключаем основной макет
if (strpos($_SERVER['REQUEST_URI'], '/ajax') === FALSE) REQUIRE_ONCE('template/main.html');

// Выводим постфикс - код, который нужно выполнить после всего, он, естественно, заполняется в контроллерах.
echo $postfix;