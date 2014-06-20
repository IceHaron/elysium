<?
/**
* 
* Контроллер страницы /admin
* 
**/

// Trolling overwhelming
$a = new achievement();
$mess = $a->earn($user->info['id'], 12);
$postfix .= $mess;
