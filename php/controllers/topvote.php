<?
/**
* 
* Топы голосующих
* 
**/

$week = 604800;
$month = 2592000;
$q = "SELECT `ololousers`.`nick`, `votes`.`user`, `votes`.`date`, `votes`.`rating` FROM `votes` JOIN `ololousers` ON (`ololousers`.`id` = `votes`.`user`);";
$r = $db->query($q);

foreach ($r as $vote) {

	if (!isset($votes[ $vote['user'] ][ $vote['rating'] ])) $votes[ $vote['user'] ][ $vote['rating'] ] = 1;
	else $votes[ $vote['user'] ][ $vote['rating'] ]++;

	if (!isset($votes[ $vote['user'] ]['summary'])) $votes[ $vote['user'] ]['summary'] = 1;
	else $votes[ $vote['user'] ]['summary']++;

	if (strtotime($vote['date']) > time()-$week) {

		if (!isset($weekly[ $vote['user'] ][ $vote['rating'] ])) $weekly[ $vote['user'] ][ $vote['rating'] ] = 1;
		else $weekly[ $vote['user'] ][ $vote['rating'] ]++;

		if (!isset($weekly[ $vote['user'] ]['summary'])) $weekly[ $vote['user'] ]['summary'] = 1;
		else $weekly[ $vote['user'] ]['summary']++;
		
	}

	if (strtotime($vote['date']) > time()-$month) {

		if (!isset($monthly[ $vote['user'] ][ $vote['rating'] ])) $monthly[ $vote['user'] ][ $vote['rating'] ] = 1;
		else $monthly[ $vote['user'] ][ $vote['rating'] ]++;

		if (!isset($monthly[ $vote['user'] ]['summary'])) $monthly[ $vote['user'] ]['summary'] = 1;
		else $monthly[ $vote['user'] ]['summary']++;

	}
}
