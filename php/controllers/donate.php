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

	$coupons = $user->getCoupons($cid);
	$izumCoupons = '';
	$goodsCoupons = '';

	if (isset($coupons['admindiscount'])) {
		foreach ($coupons['admindiscount'] as $effect => $params) {
			$izumCoupons .= '
				<li>
					<label><input type="radio" name="izumDiscount" value="' . $params['firstID'] . '" data-name="admindiscount" data-effect="' . $effect . '">
					' . $params['ruName'] . ' (осталось ' . $params['count'] . ')</label>
				</li>';
		}
	}
	if (isset($coupons['testdiscount'])) {
		foreach ($coupons['testdiscount'] as $effect => $params) {
			$izumCoupons .= '
				<li>
					<label><input type="radio" name="izumDiscount" value="' . $params['firstID'] . '" data-name="testdiscount" data-effect="' . $effect . '">
					' . $params['ruName'] . ' (осталось ' . $params['count'] . ')</label>
				</li>';
		}
	}

}