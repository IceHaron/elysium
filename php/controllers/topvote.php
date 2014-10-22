<?
/**
* 
* Топы голосующих
* 
**/

$week = 604800;
$month = 2592000;

$q = "SELECT * FROM `ratings`";
$r = $db->query($q);

foreach ($r as $rating) $ratings[ $rating['id'] ] = $rating['name'];

$q = "
	SELECT `ololousers`.`nick`, `votes`.`user`, `votes`.`date`, `votes`.`rating`
	FROM `votes`
	JOIN `ololousers` ON (`ololousers`.`id` = `votes`.`user`);";
$r = $db->query($q);

foreach ($r as $vote) {

	$votes[ $vote['user'] ]['nick'] = $vote['nick'];
	$monthly[ $vote['user'] ]['nick'] = $vote['nick'];
	$weekly[ $vote['user'] ]['nick'] = $vote['nick'];

	if (!isset($votes[ $vote['user'] ]['votes'][ $vote['rating'] ])) $votes[ $vote['user'] ]['votes'][ $vote['rating'] ] = 1;
	else $votes[ $vote['user'] ]['votes'][ $vote['rating'] ]++;

	if (!isset($votes[ $vote['user'] ]['votes']['summary'])) $votes[ $vote['user'] ]['votes']['summary'] = 1;
	else $votes[ $vote['user'] ]['votes']['summary']++;

	if (strtotime($vote['date']) > time()-$month) {

		if (!isset($monthly[ $vote['user'] ]['votes'][ $vote['rating'] ])) $monthly[ $vote['user'] ]['votes'][ $vote['rating'] ] = 1;
		else $monthly[ $vote['user'] ]['votes'][ $vote['rating'] ]++;

		if (!isset($monthly[ $vote['user'] ]['votes']['summary'])) $monthly[ $vote['user'] ]['votes']['summary'] = 1;
		else $monthly[ $vote['user'] ]['votes']['summary']++;

	}

	if (strtotime($vote['date']) > time()-$week) {

		if (!isset($weekly[ $vote['user'] ]['votes'][ $vote['rating'] ])) $weekly[ $vote['user'] ]['votes'][ $vote['rating'] ] = 1;
		else $weekly[ $vote['user'] ]['votes'][ $vote['rating'] ]++;

		if (!isset($weekly[ $vote['user'] ]['votes']['summary'])) $weekly[ $vote['user'] ]['votes']['summary'] = 1;
		else $weekly[ $vote['user'] ]['votes']['summary']++;
		
	}

}

foreach ($votes as $userID => $userInfo) {
	$orderVotes[ $userInfo['votes']['summary'] ] = $userID;
}

foreach ($monthly as $userID => $userInfo) {
	$orderMonth[ $userInfo['votes']['summary'] ] = $userID;
}

foreach ($weekly as $userID => $userInfo) {
	$orderWeek[ $userInfo['votes']['summary'] ] = $userID;
}

krsort($orderVotes);
krsort($orderMonth);
krsort($orderWeek);
