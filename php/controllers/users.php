<?
$referrer = isset($cid) ? $user->info['referrer'] : '';
$standing = 'all';
$user = new user();

if (isset($_GET['id'])) {
	$info = $user->getFullInfo($_GET['id']);

	if (isset($cid) && ($info['referrer']['id'] == $cid || $_GET['id'] == $user->info['referrer'])) $standing = 'friends';
	else if (isset($cid)) $standing = 'reg';

	$pagetype = 'unit';

} else {

	$q = "SELECT `id`,`alias` FROM `usergroups`";
	$r = $db->query($q);

	foreach ($r as $group) {
		$alias[ $group['id'] ] = $group['alias'];
	}

	$q = "SELECT `id`, `nick`, `mcname`, `exp`, `privacy`, `referrer`, `group` FROM `ololousers` WHERE `group` NOT IN (0, 100);";
	$r = $db->query($q);

	foreach ($r as $k => $player) {
		$privacy = $player['privacy'];
		$groupid = $player['group'];

		if (isset($cid) && ($player['referrer'] == $cid || $player['id'] == $referrer)) $standing = 'friends';
		else if (isset($cid)) $standing = 'reg';

		$player['level'] = $level = $user->getLevel($player['exp']);
		$player['levelInfo'] = $user->getLevelHTML($level);
		$player['privacy'] = json_decode($player['privacy'], TRUE);
		$player['hidden'] = $privacy == '{"friends":{"exp":0,"ach":0,"steam":0},"reg":{"exp":0,"ach":0,"steam":0},"all":{"exp":0,"ach":0,"steam":0}}';
		$player['group'] = $alias[$groupid];

		if ($user->info['group'] >= 50 || $privacy != '{"friends":{"exp":0,"ach":0,"steam":0},"reg":{"exp":0,"ach":0,"steam":0},"all":{"exp":0,"ach":0,"steam":0}}')
			$playerList[$k] = $player;
	}
	// var_dump($playerList);
	$pagetype = 'list';
}