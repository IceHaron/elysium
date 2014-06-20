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

	private $db; // Класс БД, чтоб в каждом методе не писать GLOBAL $db, это вымораживает.
	private $user; // Класс пользователя, нутыпонил

/**
* 
* Конструктор, сэр.
* Из овсянки, сэр.
* 
**/
	public function achievement() {

		// Собственно, заполняем наши переменные
		GLOBAL $db, $user;
		$this->db = $db;
		$this->user = $user;
	}

/**
* 
* Получить ачивку
* @param user - Айдишник юзверя, кому даем ачивку
* @param ach - Айдишник ачивки
* @param achExp - Количество экспы, выдаваемое за получение этой ачивки (только если grade = 1 = Improved, смотри коммент в самом начале)
* @return string - код для отображения всплывающей ачивки
* 
**/
	public function earn($user, $ach, $achExp = FALSE) {

		// Проверяем, получена ли эта ачивка пользователем
		$q = "SELECT * FROM `user_achievs` WHERE `user` = $user AND `achievement` = $ach";
		$r = $this->db->query($q);

		if (!$r) {

			// Вытаскиваем экспу пользователя
			$q = "SELECT `exp` FROM `ololousers` WHERE `id` = $user";
			$r = $this->db->query($q);
			$exp = (int)$r[0]['exp'];

			if ($achExp === FALSE) {
				// Если не дано количество экспы параметром, вытягиваем его из базы
				$q = "SELECT `xpcost` FROM `achievements` WHERE `id` = $ach";
				$r = $this->db->query($q);
				$gift = (int)$r[0]['xpcost'];

			} else {
				// Если экспу нам сообщили, тогда проводим проверку, а правильный ли тип ачивки, если нет, то опять же экспу вытягиваем из базы
				$q = "SELECT `grade`, `xpcost` FROM `achievements` WHERE `id` = $ach";
				$r = $this->db->query($q);
				$grade = $r[0]['grade'];

				if ($grade == '1') $gift = $achExp + $r[0]['xpcost'];

				else $gift = $r[0]['xpcost'];
			}

			// Дарим пользователю определенную экспу
			$exp += $gift;
			$q = "UPDATE `ololousers` SET `exp` = $exp WHERE `id` = $user";
			$r = $this->db->query($q);

			// И записываем в таблицу инфу
			$time = time();
			$q = "INSERT INTO `user_achievs` (`user`, `achievement`, `ts`) VALUES ($user, $ach, $time)";
			$r = $this->db->query($q);

			// Возвращаем код для отображения всплывающей ачивки
			return '<script>showAchievement(' . $ach . ');</script>';
		}

	}

/**
* 
* Получаем ачивки юзера
* @param user - Айдишник юзера
* @return array - ачивки, КЭП
* 
**/
	public function getAch($user) {

		$q = "SELECT `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`ua`.`ts`) as `ts`, `a`.`type`, `a`.`class`, `a`.`grade`
					FROM `user_achievs` AS `ua`
					JOIN `achievements` AS `a` ON (`ua`.`achievement` = `a`.`id`)
					WHERE `ua`.`user` = $user ORDER BY `ts` DESC;";
		$r = $this->db->query($q);

		return $r;
	}

/**
* 
* Получаем юзеров, имеющих определенную ачивку (не реализовано, КЭП)
* @param ach - Айдишник ачивки
* 
**/
	public function getUser($ach) {
		var_dump('expression');
	}

/**
* 
* Получить все ачивки
* @param user - Айдишник юзера чтоб показывать полученные и неполученные
* @return array - ачивки
* 
**/
	public function getAll($user = NULL) {

		// Считаем пользователей чтоб узнать проценты получения ачивок
		$q = "SELECT count(*) AS `count` FROM `ololousers`";
		$r = $this->db->query($q);
		$userCount = $r[0]['count'];

		if (isset($user))
			// Если получаем юзверя, то узнаем, какие ачивки он получил
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, `a`.`type`, `a`.`class`, `a`.`grade`, FROM_UNIXTIME(`my`.`ts`) AS `ts`, round(count(*) / $userCount * 100) AS `perc`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = $user AND `ua`.`achievement` = `my`.`achievement`)
						GROUP BY `a`.`id`;";

		else
			// Если юзверя нам не сказали, то и узначать полученные ачивки нам не надо
			$q = "SELECT `a`.`id`, `a`.`name`, `a`.`desc`, `a`.`type`, `a`.`class`, `a`.`grade`, round(count(*) / $userCount * 100) AS `perc`
						FROM `achievements` AS `a`
						LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
						GROUP BY `a`.`id`;";
		$r = $this->db->query($q);

		return $r;
	}

/**
* 
* Проверить, какие ачивки получил пользователь с момента последней такой проверки
* @return array - полученные ачивки
* 
**/
	public function check() {

		// Можно было бы юзера передавать параметром, только нахрен это надо, если чужие ачивки мы все равно не получим
		$info = $this->user->info;
		$id = $info['id'];
		// Считаем приглашенных
		$q = "SELECT count(*) AS `count` FROM `ololousers` WHERE `referrer` = $id";
		$r = $this->db->query($q);
		$count = $r[0]['count'];
		// Получаем список неполученных ачивок
		$q = "SELECT `a`.`id`
					FROM `achievements` AS `a`
					LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
					LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = $id AND `ua`.`achievement` = `my`.`achievement`)
					WHERE `my`.`ts` is null
					GROUP BY `a`.`id`;";
		$r = $this->db->query($q);

		/* Тут мы определяем все необходимые переменные для последующих условий */
		//////////////////////////////////////////////////////////////////////////
		// Узнаем свой уровень
		$level = $this->user->getLevel($info['exp']);
		//////////////////////////////////////////////////////////////////////////

		// Создаем массивчик под полученные ачивки и в путь, по условиям.
		$output = array();

		foreach ($r as $ach) {

			switch ($ach['id']) {

				case '3':
					// Приглашен 1 друг
					if ($count >= 1) {
						$this->earn($id, 3);
						$output[] = $ach['id'];
					}
					break;

				case '4':
					// Приглашено 5 друзей
					if ($count >= 5) {
						$this->earn($id, 4);
						$output[] = $ach['id'];
					}
					break;

				case '5':
					// Достигнут 5 уровень
					if ($level['level'] >= 5) {
						$this->earn($id, 5);
						$output[] = $ach['id'];
					}
					break;
				case '6':
					// Приглашено 10 друзей
					if ($count >= 10) {
						$this->earn($id, 6);
						$output[] = $ach['id'];
					}
					break;

				case '7':
					// Приглашено 15 друзей
					if ($count >= 15) {
						$this->earn($id, 7);
						$output[] = $ach['id'];
					}
					break;

				case '10':
					// Достигнут 10 уровень
					if ($level['level'] >= 10) {
						$this->earn($id, 10);
						$output[] = $ach['id'];
					}
					break;

				case '13':
					// Достигнут 13 уровень
					if ($level['level'] >= 13) {
						$this->earn($id, 13);
						$output[] = $ach['id'];
					}
					break;

				case '14':
					// Нас никто не приглашал
					if ($info['referrer'] == '1') {
						$this->earn($id, 14);
						$output[] = $ach['id'];
					}
					break;

				case '21':
					// Достигнут 21 уровень
					if ($level['level'] >= 21) {
						$this->earn($id, 21);
						$output[] = $ach['id'];
					}
					break;

				case '30':
					// Достигнут 30 уровень
					if ($level['level'] >= 30) {
						$this->earn($id, 30);
						$output[] = $ach['id'];
					}
					break;

				case '42':
					// Достигнут 42 уровень
					if ($level['level'] >= 42) {
						$this->earn($id, 42);
						$output[] = $ach['id'];
					}
					break;

				case '50':
					// Достигнут 50 уровень
					if ($level['level'] >= 50) {
						$this->earn($id, 50);
						$output[] = $ach['id'];
					}
					break;

				case '70':
					// Достигнут 70 уровень
					if ($level['level'] == 70) {
						$this->earn($id, 70);
						$output[] = $ach['id'];
					}
					break;

				case '100500':
					// Накоплено 100500 опыта
					if ($info['exp'] >= 100500) {
						$this->earn($id, 100500);
						$output[] = $ach['id'];
					}
					break;
			}
		}

		// Кодируем в JSON массив и отправляем наверх
		$output = json_encode($output);
		return $output;
	}

}