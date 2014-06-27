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

	$q = "SELECT `id`, `nick`, `mcname`, `exp`, `steamid`, `email`, `privacy`, `referrer` FROM `ololousers` WHERE `id` NOT IN (0,1);";
	$r = $db->query($q);

	foreach ($r as $k => $player) {

		if (isset($cid) && ($player['referrer'] == $cid || $player['id'] == $referrer)) $standing = 'friends';
		else if (isset($cid)) $standing = 'reg';

		$player['level'] = $level = $user->getLevel($player['exp']);
		$player['levelInfo'] = $user->getLevelHTML($level);
		$player['privacy'] = json_decode($player['privacy'], TRUE);

		$playerList[$k] = $player;
	}
	// var_dump($playerList);
	$pagetype = 'list';
}