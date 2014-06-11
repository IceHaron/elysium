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
		<div class="achievementWrapper" id="ach-' . $_GET['id'] . '">
			<div class="achievement grade_' . $achievement['grade'] . '">
				<span class="achTitle">' . $achievement['name'] . '</span>
				<span class="achDate">' . date('Y-m-d H:i:s') . '</span>
				<span class="achDesc">' . $achievement['desc'] . '</span>
			</div>
		</div>';
		echo $output;
	break;
	default:
		echo 'wrong mode';
	break;
}