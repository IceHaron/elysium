<?
if (isset($user)) {
	$postfix .= $achievement->earn($user->info['id'], 23);
	if ($user->info['group'] < 5) $postfix .= $achievement->earn($user->info['id'], 13);
}