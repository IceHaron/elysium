<?
/**
* 
* Контроллер страницы ачивок
* 
**/

$a = new achievement();
$achievs = $a->getAll();

if (isset($cid)) {
	// Если залогинились, ставим флажок и модифицируем ачивки
	$nouser = FALSE;

	foreach ($achievs as $k => $ach) {
		if (!isset($ach['users'][$cid]) && $ach['class'] == '2') $toDel[] = $k;
		if (!isset($ach['users'][$cid]) && $ach['class'] == '1') $achievs[$k]['desc'] = '???';
	}

	foreach ($toDel as $del) unset($achievs[$del]);

} else {
	// Если не залогинились, ставим другой флажок
	$nouser = TRUE;
}