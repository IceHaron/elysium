<?

$rootfolder = isset($_SERVER['HOME']) ? $_SERVER['HOME'].'/elysium' : $_SERVER['DOCUMENT_ROOT'];
REQUIRE_ONCE($rootfolder . '/settings.php');
REQUIRE_ONCE('classes/db.php');
REQUIRE_ONCE('classes/mail.php');
$mailer = new mail();
$mailer->receive();