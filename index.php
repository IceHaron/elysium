<?

if ($_SERVER['HTTP_HOST'] == 'elysiumgame.ru') ini_set('display_errors', 0);

function pingMCServer($server,$port=25565,$timeout=2){
	$fp = fsockopen($server, $port, $errno, $errstr, 5);
	if (!$fp) exit();
	$socket=socket_create(AF_INET,SOCK_STREAM,getprotobyname('tcp')); // set up socket holder
	$con = socket_connect($socket,$server,$port); // connect to minecraft server on port 25565
	socket_send($socket,chr(254).chr(1),2,null); // send 0xFE 01 -- tells the server we want pinglist info
	socket_recv($socket,$buf,3,null); // first 3 bytes indicate the len of the reply. not necessary but i'm not one for hacky socket read loops
	$buf=substr($buf,1,2); // always pads it with 0xFF to indicate an EOF message
	$len=unpack('n',$buf); // gives us 1/2 the length of the reply
	socket_recv($socket,$buf,$len[1]*2,null); // read $len*2 bytes and hang u[
	$data=explode(chr(0).chr(0),$buf); // explode on nul-dubs
	array_shift($data); // remove separator char
	return $data; // boom sucka
}

$postfix = '';

REQUIRE_ONCE('settings.php');
REQUIRE_ONCE('php/functions.php'); // самопальные функции
// Подключаем классы...
REQUIRE_ONCE('php/classes/achievement.php'); // ...для работы с ачивками
REQUIRE_ONCE('php/classes/db.php'); // ...для работы с базой
REQUIRE_ONCE('php/classes/mail.php'); // ...для почтамта
REQUIRE_ONCE('php/classes/user.php'); // ...для работы с пользователями
$db = new db(); // ...создаем экземпляр
$mailer = new mail(); // ...создаем экземпляр

session_set_cookie_params(604800);
session_start();
session_regenerate_id();
// Логинимся
if (isset($_SESSION['login'])) {
	$user = new user();
	$cid = $user->info['id'];
	$clogin = $user->info['nick'];
	$cemail = $user->info['email'];
} else $clogin = '';

$achievement = new achievement(); // ...создаем экземпляр

if (isset($cid)) $diamond = $achievement->look($cid, 17) ? FALSE : TRUE;
else $diamond = TRUE;

// Определяем нужный модуль, переменная используется прямо в макете /templates/main.html
$module = preg_replace('/\/|\?.+$/', '', $_SERVER['REQUEST_URI']);

if ($module == 'troll') {

	if (!isset($cid) || $user->info['group'] != '777') $module = '404';
	else {
		REQUIRE_ONCE("php/controllers/$module.php");
		REQUIRE_ONCE("template/$module.html");
		exit;
	}

}

$q = "
	SELECT `players`.`nick` AS `player`, `reason`, `banners`.`nick` AS `admin`, `ban`, `unban`
	FROM `banlist`
	JOIN `ololousers` AS `players` ON (`banlist`.`player` = `players`.`id`)
	JOIN `ololousers` AS `banners` ON (`banlist`.`admin` = `banners`.`id`)
	WHERE `ban` < NOW() AND (`unban` > NOW() OR `unban` IS NULL);";
$banlist = $db->query($q);

if ($module == '') $module = 'news';
// Подгружаем контроллер, если таковой существует
if (glob("php/controllers/$module.php")) INCLUDE_ONCE("php/controllers/$module.php");
else if (!glob("template/$module.html")) $module = '404';

// Подключаем основной макет
if (strpos($_SERVER['REQUEST_URI'], '/ajax') === FALSE) REQUIRE_ONCE('template/main.html');

// Выводим постфикс - код, который нужно выполнить после всего, он, естественно, заполняется в контроллерах.
echo $postfix;