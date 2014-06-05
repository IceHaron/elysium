<?
$a = new achievement();
if (isset($cid)) {
	$achievs = $a->getAll($cid);
	$nouser = FALSE;
} else {
	$achievs = $a->getAll();
	$nouser = TRUE;
}