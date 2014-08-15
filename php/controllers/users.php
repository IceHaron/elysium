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

	$q = "SELECT `id`, `nick`, `mcname`, `exp`, `steamid`, `email`, `privacy`, `referrer`, `group` FROM `ololousers` WHERE `group` NOT IN (0, 100);";
	$r = $db->query($q);

	foreach ($r as $k => $player) {
		$privacy = $player['privacy'];

		if (isset($cid) && ($player['referrer'] == $cid || $player['id'] == $referrer)) $standing = 'friends';
		else if (isset($cid)) $standing = 'reg';

		$player['level'] = $level = $user->getLevel($player['exp']);
		$player['levelInfo'] = $user->getLevelHTML($level);
		$player['privacy'] = json_decode($player['privacy'], TRUE);
		$player['hidden'] = $privacy == '{"friends":{"exp":0,"ach":0,"steam":0},"reg":{"exp":0,"ach":0,"steam":0},"all":{"exp":0,"ach":0,"steam":0}}';

		if ($user->info['group'] > 1 || $privacy != '{"friends":{"exp":0,"ach":0,"steam":0},"reg":{"exp":0,"ach":0,"steam":0},"all":{"exp":0,"ach":0,"steam":0}}')
			$playerList[$k] = $player;
	}
	// var_dump($playerList);
	$pagetype = 'list';
}