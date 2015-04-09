<?

if ($_SERVER['HTTP_HOST'] == 'elysiumgame.ru') ini_set('display_errors', 0);

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

ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);
session_start();
// session_regenerate_id();
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
		REQUIRE_ONCE("php/controllers/troll.php");
		REQUIRE_ONCE("template/troll.html");
		exit;
	}

}

$noTemplate = FALSE;

if ($module == '') $module = 'news';
// Подгружаем контроллер, если таковой существует
if (glob("php/controllers/$module.php")) REQUIRE_ONCE("php/controllers/$module.php");
else if (!glob("template/$module.html")) $module = '404';

if ($module == '404') {
	$noTemplate = TRUE;
	REQUIRE_ONCE('template/404.html');
}

// Подключаем основной макет
if (!$noTemplate) {

	$adsArr = ['fairtop' => 'fairtop', 'real_pepper' => 'youtube'];

	if (!empty($_GET['ad']) && (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $adsArr[base64_decode($_GET['ad'])]) !== FALSE)) {
		$platform = base64_decode($_GET['ad']);
		setcookie('ad', base64_decode($_GET['ad']), time()+3600*24*30, '/');
		$db->query("INSERT INTO `ads` (`platform`, `url`, `ip`) VALUES ('" . base64_decode($_GET['ad']) . "', '{$_SERVER['HTTP_REFERER']}', '{$_SERVER['REMOTE_ADDR']}')");
	}

	REQUIRE_ONCE('template/main.html');
}


// Выводим постфикс - код, который нужно выполнить после всего, он, естественно, заполняется в контроллерах.
echo $postfix;

// И закрываем подключение к базе
$db->close();
