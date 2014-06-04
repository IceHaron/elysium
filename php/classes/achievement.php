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

	public function getAch($user) {
		GLOBAL $db;
		$q = "SELECT `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`ua`.`ts`) as `ts`
					FROM `user_achievs` AS `ua`
					JOIN `achievements` AS `a` ON (`ua`.`achievement` = `a`.`id`)
					WHERE `ua`.`user` = $user;";
		$r = $db->query($q);
		return $r;
	}

	public function getUser($ach) {
		var_dump('expression');
	}
	public function getAll($user = NULL) {
		GLOBAL $db;
		$q = "SELECT count(*) FROM `ololousers`";
		$r = $db->query($q);
		
		if (isset($user))
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`my`.`ts`) AS `ts`, count(*) AS `earned`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = 4 AND `ua`.`achievement` = `my`.`achievement`)
						GROUP BY `a`.`id`;";
		else
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, count(*) AS `earned`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						GROUP BY `a`.`id`;";
		$r = $db->query($q);
		return $r;
	}

}