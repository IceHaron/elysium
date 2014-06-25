<?

if ($cid) {
	$referrer = $user->info['referrer'];
	$q = "SELECT `id`, `nick`, `mcname`, `exp`, `steamid`, `email`, `privacy`, `referrer` FROM `ololousers` WHERE `privacy` != '000';";
	$r = $db->query($q);
	foreach ($r as $k => $player) {
		$pass = FALSE;

		if ($player['referrer'] == $cid || $player['id'] == $referrer) {
			if (in_array($player['privacy'][0], array(1, 3, 7))) $pass = TRUE;
		} else if (in_array($player['privacy'][1], array(1, 3, 7))) $pass = TRUE;

		if ($pass === TRUE) {
			$level = $user->getLevel($player['exp']);
			$player['level']['level'] = $level['level'];

			if ($level['level'] < 70) {
				$player['level']['percent'] = floor($level['exp'] / $level['need'] * 100);
				$player['level']['signature'] = $level['exp'] . ' / ' . $level['need'] . ' exp (' . $player['level']['percent'] . '%)';

			} else {
				$player['level']['percent'] = 100;
				$player['level']['signature'] = $level['exp'] . ' exp';
			}

		} else $player['level'] = 'hidden';

		$playerList[$k] = $player;
	}
	// var_dump($playerList);
}