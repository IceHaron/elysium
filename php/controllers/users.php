<?
$referrer = $cid ? $user->info['referrer'] : '';
if ($cid) $standing = 'reg';
else $standing = 'all';

if (isset($_GET['id'])) {
	$info = $user->getFullInfo($_GET['id']);

	if ($info['referrer']['id'] == $cid || $_GET['id'] == $user->info['referrer']) $standing = 'friends';

	$pagetype = 'unit';

} else {

	$q = "SELECT `id`, `nick`, `mcname`, `exp`, `steamid`, `email`, `privacy`, `referrer` FROM `ololousers` WHERE `id` != 0;";
	$r = $db->query($q);

	foreach ($r as $k => $player) {

		if ($player['referrer'] == $cid || $player['id'] == $referrer) $standing = 'friends';

		$player['level'] = $level = $user->getLevel($player['exp']);
		$player['levelInfo'] = $user->getLevelHTML($level);
		$player['privacy'] = json_decode($player['privacy'], TRUE);

		$playerList[$k] = $player;
	}
	// var_dump($playerList);
	$pagetype = 'list';
}