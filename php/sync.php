<?

ini_set('display_errors', 0);
$rootfolder = isset($_SERVER['HOME']) ? $_SERVER['HOME'].'/elysiumgame' : $_SERVER['DOCUMENT_ROOT'];
REQUIRE_ONCE($rootfolder . '/settings.php');
REQUIRE_ONCE('classes/db.php');
REQUIRE_ONCE('functions.php');
$db = new db();
syncAccs();