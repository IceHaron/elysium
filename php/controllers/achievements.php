<?
/**
* 
* Контроллер страницы ачивок
* 
**/

$achievs = $achievement->getAll();

if (isset($cid)) {
	// Если залогинились, ставим флажок и модифицируем ачивки
	$nouser = FALSE;

	foreach ($achievs as $k => $ach) {
		if (!isset($ach['users'][$cid]) && $ach['class'] == '2') $toDel[] = $k;
		if (!isset($ach['users'][$cid]) && $ach['class'] == '1') $achievs[$k]['desc'] = '???';
	}

	if (isset($toDel)) foreach ($toDel as $del) unset($achievs[$del]);

	if (isset($_GET['compare'])) $compid = $_GET['compare'];

} else {
	// Если не залогинились, ставим другой флажок
	$nouser = TRUE;

	foreach ($achievs as $k => $ach) {
		if ($ach['class'] == '2') $toDel[] = $k;
		if ($ach['class'] == '1') $achievs[$k]['desc'] = '???';
	}

	if (isset($toDel)) foreach ($toDel as $del) unset($achievs[$del]);

}