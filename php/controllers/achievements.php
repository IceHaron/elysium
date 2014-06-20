<?
/**
* 
* Контроллер страницы ачивок
* 
**/

$a = new achievement();

if (isset($cid)) {
	// Если залогинились, ставим флажок
	$achievs = $a->getAll($cid);
	$nouser = FALSE;

} else {
	// Если не залогинились, ставим другой флажок
	$achievs = $a->getAll();
	$nouser = TRUE;
}