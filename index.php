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

$noTemplate = FALSE;

if ($module == '') $module = 'news';
// Подгружаем контроллер, если таковой существует
if (glob("php/controllers/$module.php")) INCLUDE_ONCE("php/controllers/$module.php");
else if (!glob("template/$module.html")) $module = '404';

// Подключаем основной макет
if (!$noTemplate) REQUIRE_ONCE('template/main.html');

// Выводим постфикс - код, который нужно выполнить после всего, он, естественно, заполняется в контроллерах.
echo $postfix;
