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
}