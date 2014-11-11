<?
ini_set('display_errors', 0);
$rootfolder = isset($_SERVER['HOME']) ? $_SERVER['HOME'].'/elysiumgame' : $_SERVER['DOCUMENT_ROOT'];
REQUIRE_ONCE($rootfolder . '/settings.php');
REQUIRE_ONCE('classes/db.php');
REQUIRE_ONCE('classes/mail.php');
$db = new db();
$mailer = new mail();
$mailer->receive();
$db->close();