<?
$q = "
	SELECT `d`.`id`, `d`.`name`, `d`.`desc`, `d`.`cost`, `d`.`duration`, `dg`.`name` AS `group`
	FROM `donuts` AS `d`
	JOIN `donutgroups` AS `dg` ON (`d`.`group` = `dg`.`id`);";
$r = $db->query($q);

foreach ($r as $donut) {
	$donuts[ $donut['group'] ][ $donut['id'] ] = $donut;
}

$rubCost = 1000;

if ($clogin) {
	$izum = $user->info['izumko'];
	$q = "SELECT `id` FROM `acquiring` WHERE `user` = $cid AND `paid` IN (0,1);";
	$unpaid = $db->query($q);
	if (count($unpaid))	$debt = TRUE;
	else $debt = FALSE;
}
