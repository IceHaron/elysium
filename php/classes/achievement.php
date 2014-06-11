<?php

/**
*
* Класс управления достижениями
*
* Ачивки делятся по трем полям:
* type
* 	0 - Standard / Стандартная, что-то сделал - получил
* 	1 - Progressive / Прогрессивная, для ее получения нужно что-то накопить
* 	2 - Reclaimable / Многоразовая, можно получить несколько раз
* class
* 	0 - Normal / Обычная, показывается в списке достижений еще до получения
* 	1 - Ninja / Полускрытая, показывается в списке достижений, но описание скрыто
* 	2 - Hidden / Скрытая ачивка, не отображается в списке достижений пока игрок ее не получит
* grade
* 	0 - Basic / Обычная, в серо-черной рамке
* 	1 - Improved / Улучшенная, в фиолетовой рамке, дает нефиксированное количество опыта
* 	2 - Elite / Элитная, в золотой рамке, получить сложно.
* 	3 - Platinum / Платиновая, в светлой рамке, раз просрав, никогда больше не получишь (если не удалось получить, у нее красный фон)
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

	public function earn($user, $ach, $achExp = FALSE) {
		$q = "SELECT * FROM `user_achievs` WHERE `user` = $user AND `achievement` = $ach";
		$r = $this->db->query($q);
		if (!$r) {
			$q = "SELECT `exp` FROM `ololousers` WHERE `id` = $user";
			$r = $this->db->query($q);
			$exp = (int)$r[0]['exp'];
			if ($achExp === FALSE) {
				$q = "SELECT `xpcost` FROM `achievements` WHERE `id` = $ach";
				$r = $this->db->query($q);
				$gift = (int)$r[0]['xpcost'];
			} else {
				$q = "SELECT `grade` FROM `achievements` WHERE `id` = $ach";
				$r = $this->db->query($q);
				$grade = $r[0]['grade'];
				if ($grade == '1') $gift = $achExp;
				else $gift = 0;
			}
			$exp += $gift;
			$q = "UPDATE `ololousers` SET `exp` = $exp WHERE `id` = $user";
			$r = $this->db->query($q);
			$time = time();
			$q = "INSERT INTO `user_achievs` (`user`, `achievement`, `ts`) VALUES ($user, $ach, $time)";
			$r = $this->db->query($q);
			return '<script>showAchievement(' . $ach . ');</script>';
		}
	}

	public function getAch($user) {
		$q = "SELECT `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`ua`.`ts`) as `ts`, `a`.`type`, `a`.`class`, `a`.`grade`
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
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, `a`.`type`, `a`.`class`, `a`.`grade`, FROM_UNIXTIME(`my`.`ts`) AS `ts`, round(count(*) / $userCount * 100) AS `perc`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = $user AND `ua`.`achievement` = `my`.`achievement`)
						GROUP BY `a`.`id`;";
		else
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, `a`.`type`, `a`.`class`, `a`.`grade`, round(count(*) / $userCount * 100) AS `perc`
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
		$q = "SELECT count(*) AS `count` FROM `ololousers` WHERE `referrer` = $id";
		$r = $this->db->query($q);
		$count = $r[0]['count'];
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
				case '3':
					if ($count >= 1) {
						$this->earn($id, 3);
						$output[] = $ach['id'];
					}
					break;
				case '4':
					if ($count >= 5) {
						$this->earn($id, 4);
						$output[] = $ach['id'];
					}
					break;
				case '5':
					if ($level['level'] >= 5) {
						$this->earn($id, 5);
						$output[] = $ach['id'];
					}
					break;
				case '6':
					if ($count >= 10) {
						$this->earn($id, 6);
						$output[] = $ach['id'];
					}
					break;
				case '7':
					if ($count >= 15) {
						$this->earn($id, 7);
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