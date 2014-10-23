<?
/**
* 
* Топы голосующих
* 
**/

giftForVoting($cid, 'fairtop', 'test');

$week = 604800;
$month = 2592000;
$weekly = array();
$monthly = array();
$alltime = array();

$q = "SELECT * FROM `ratings`";
$r = $db->query($q);

foreach ($r as $rating) $ratings[ $rating['id'] ] = $rating['name'];

$q = "
	SELECT `ololousers`.`nick`, `ololousers`.`group`, `votes`.`user`, `votes`.`date`, `votes`.`rating`
	FROM `votes`
	JOIN `ololousers` ON (`ololousers`.`id` = `votes`.`user`);";
$r = $db->query($q);

foreach ($r as $vote) {
	$players[ $vote['user'] ] = array('nick' => $vote['nick'], 'group' => $vote['group']);
	addVote('alltime', $vote['user'], $vote['rating']);

	if (strtotime($vote['date']) > time()-$month) addVote('monthly', $vote['user'], $vote['rating']);
	if (strtotime($vote['date']) > time()-$week) addVote('weekly', $vote['user'], $vote['rating']);

}

foreach ($players as $userID => $userInfo) {
	$orderalltime[ $alltime[$userID]['votes']['summary'] ][] = $userID;
	$ordermonthly[ $monthly[$userID]['votes']['summary'] ][] = $userID;
	$orderweekly[ $weekly[$userID]['votes']['summary'] ][] = $userID;
}

krsort($orderalltime);
krsort($ordermonthly);
krsort($orderweekly);

function addVote($type, $player, $rating) {
	GLOBAL ${$type};
	$arr = &${$type};

	if (!isset($arr[$player]['votes'][$rating])) $arr[$player]['votes'][$rating] = 1;
	else $arr[$player]['votes'][$rating]++;

	if (!isset($arr[$player]['votes']['summary'])) $arr[$player]['votes']['summary'] = 1;
	else $arr[$player]['votes']['summary']++;

	return TRUE;
}