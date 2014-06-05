<?
switch ($_GET['mode']) {
	case 'achCheck':
		$ach = new achievement();
		echo $ach->check();
	break;
	case 'getAchHtml':
		$q = "SELECT * FROM `achievements` WHERE `id` = {$_GET['id']}";
		$r = $db->query($q);
		$achievement = $r[0];
		$output = '
			<div class="achievement" id="ach-' . $_GET['id'] . '">
				<span class="achTitle">' . $achievement['name'] . '</span>
				<span class="achDate">' . date('Y-m-d H:i:s') . '</span>
				<span class="achDesc">' . $achievement['desc'] . '</span>
			</div>
		';
		echo $output;
	break;
	default:
		echo 'wrong parameter "mode"';
	break;
}