<?php

/**
*
* Класс управления достижениями
*
**/

class achievement {

	private $db;
	private $user;

	public function achievement() {
		GLOBAL $db, $user;
		$this->db = $db;
		$this->user = $user;
	}

	public function earn($user, $ach) {
		$q = "SELECT * FROM `user_achievs` WHERE `user` = $user AND `achievement` = $ach";
		$r = $this->db->query($q);
		if (!$r) {
			$q = "SELECT `exp` FROM `ololousers` WHERE `id` = $user";
			$r = $this->db->query($q);
			$exp = (int)$r[0]['exp'];
			$q = "SELECT `xpcost` FROM `achievements` WHERE `id` = $ach";
			$r = $this->db->query($q);
			$gift = (int)$r[0]['xpcost'];
			$exp += $gift;
			$q = "UPDATE `ololousers` SET `exp` = $exp WHERE `id` = $user";
			$r = $this->db->query($q);
			$time = time();
			$q = "INSERT INTO `user_achievs` (`user`, `achievement`, `ts`) VALUES ($user, $ach, $time)";
			$r = $this->db->query($q);
		}
	}

	public function getAch($user) {
		$q = "SELECT `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`ua`.`ts`) as `ts`
					FROM `user_achievs` AS `ua`
					JOIN `achievements` AS `a` ON (`ua`.`achievement` = `a`.`id`)
					WHERE `ua`.`user` = $user ORDER BY `ts` DESC;";
		$r = $this->db->query($q);
		return $r;
	}

	public function getUser($ach) {
		var_dump('expression');
	}

	public function getAll($user = NULL) {
		$q = "SELECT count(*) AS `count` FROM `ololousers`";
		$r = $this->db->query($q);
		$userCount = $r[0]['count'];

		if (isset($user))
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`my`.`ts`) AS `ts`, round(count(*) / $userCount * 100) AS `perc`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = $user AND `ua`.`achievement` = `my`.`achievement`)
						GROUP BY `a`.`id`;";
		else
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, round(count(*) / $userCount * 100) AS `perc`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						GROUP BY `a`.`id`;";
		$r = $this->db->query($q);
		return $r;
	}

	public function check() {
		GLOBAL $user;
		$info = $user->info;
		$id = $info['id'];
		$q = "SELECT `a`.`id`
					FROM `achievements` AS `a`
					LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
					LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = $id AND `ua`.`achievement` = `my`.`achievement`)
					WHERE `my`.`ts` is null
					GROUP BY `a`.`id`;";
		$r = $this->db->query($q);
		$level = $this->user->getLevel($info['exp']);
		$output = array();
		foreach ($r as $ach) {
			switch ($ach['id']) {
				case '5':
					if ($level['level'] >= 5) {
						$this->earn($id, 5);
						$output[] = $ach['id'];
					}
					break;
				case '10':
					if ($level['level'] >= 10) {
						$this->earn($id, 10);
						$output[] = $ach['id'];
					}
					break;
				case '13':
					if ($level['level'] >= 13) {
						$this->earn($id, 13);
						$output[] = $ach['id'];
					}
					break;
				case '21':
					if ($level['level'] >= 21) {
						$this->earn($id, 21);
						$output[] = $ach['id'];
					}
					break;
				case '30':
					if ($level['level'] >= 30) {
						$this->earn($id, 30);
						$output[] = $ach['id'];
					}
					break;
				case '42':
					if ($level['level'] >= 42) {
						$this->earn($id, 42);
						$output[] = $ach['id'];
					}
					break;
				case '50':
					if ($level['level'] >= 50) {
						$this->earn($id, 50);
						$output[] = $ach['id'];
					}
					break;
				case '70':
					if ($level['level'] == 70) {
						$this->earn($id, 70);
						$output[] = $ach['id'];
					}
					break;
				case '100500':
					if ($info['exp'] >= 100500) {
						$this->earn($id, 100500);
						$output[] = $ach['id'];
					}
					break;
			}
		}
		$output = json_encode($output);
		return $output;
	}

}