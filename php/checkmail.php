<?

REQUIRE_ONCE('settings.php');
REQUIRE_ONCE('/classes/mail.php'); // ...для почтамта
$mailer = new mail();
$mailer->receive();