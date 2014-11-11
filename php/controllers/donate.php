<?
$q = "
	SELECT `d`.`id`, `d`.`name`, `d`.`desc`, `d`.`cost`, `d`.`duration`, `dg`.`id` AS `groupID`, `dg`.`name` AS `groupName`
	FROM `donuts` AS `d`
	JOIN `donutgroups` AS `dg` ON (`d`.`group` = `dg`.`id`);";
$r = $db->query($q);

foreach ($r as $donut) {
	$donuts[ $donut['groupName'] ][ $donut['id'] ] = $donut;
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
	$goodCoupons = '';

	if (isset($coupons['admindiscount'])) {
		$coupon = $coupons['admindiscount'];
		$izumCoupons .= '
			<li>
				<label><input type="radio" name="izumDiscount" value="' . $coupon['firstID'] . '" data-type="admindiscount" data-effect="' . $coupon['effect'] . '">
				' . $coupon['ruName'] . ' (осталось ' . $coupon['count'] . ')</label>
			</li>';
	}

	if (isset($coupons['votediscount'])) {
		$coupon = $coupons['votediscount'];
		$goodCoupons .= '
			<li>
				<label><input type="radio" name="goodDiscount" value="votediscount" data-type="votediscount" data-effect="' . $coupon['effect'] . '" data-group="' . $coupon['group'] . '">
				' . $coupon['ruName'] . ' (x' . $coupon['count'] . ')</label>
			</li>';
	}

	if (isset($coupons['votecap'])) {
		$coupon = $coupons['votecap'];
		$goodCoupons .= '
			<li>
				<label><input type="radio" name="goodDiscount" value="' . $coupon['firstID'] . '" data-type="votecap" data-effect="' . $coupon['effect'] . '" data-group="' . $coupon['group'] . '">
				' . $coupon['ruName'] . ' (осталось ' . $coupon['count'] . ')</label>
			</li>';
	}

	if (isset($coupons['reactivation'])) {
		$coupon = $coupons['reactivation'];
		$goodCoupons .= '
			<li>
				<label><input type="checkbox" name="stackDiscount[]" value="' . $coupon['firstID'] . '" data-type="reactivation" data-effect="' . $coupon['effect'] . '" data-group="' . $coupon['group'] . '">
				' . $coupon['ruName'] . ' (осталось ' . $coupon['count'] . ', первая истекает ' . $coupon['firstEnd'] . ')</label>
			</li>';
	}

}