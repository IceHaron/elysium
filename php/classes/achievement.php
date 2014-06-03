<?php

/**
*
* Класс управления достижениями
*
**/

class achievement {

	public function achievement() {
	}

	public function earn($user, $ach) {
		GLOBAL $db;
		$q = "SELECT * FROM `user_achievs` WHERE `user` = $user AND `achievement` = $ach";
		$r = $db->query($q);
		if (!$r) {
			$q = "SELECT `exp` FROM `ololousers` WHERE `id` = $user";
			$r = $db->query($q);
			$exp = (int)$r[0]['exp'];
			$q = "SELECT `xpcost` FROM `achievements` WHERE `id` = $ach";
			$r = $db->query($q);
			$gift = (int)$r[0]['xpcost'];
			$exp += $gift;
			$q = "UPDATE `ololousers` SET `exp` = $exp WHERE `id` = $user";
			$r = $db->query($q);
			$time = time();
			$q = "INSERT INTO `user_achievs` (`user`, `achievement`, `ts`) VALUES ($user, $ach, $time)";
			$r = $db->query($q);
		}
	}

}