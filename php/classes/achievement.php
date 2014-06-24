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
		$q = "SELECT `ua`.*, `a`.`type` FROM `user_achievs` AS `ua` JOIN `achievements` AS `a` ON (`a`.`id` = $ach) WHERE `user` = $user AND `achievement` = $ach";
		$r = $this->db->query($q);

		if (!$r) $pass = TRUE;

		else if ($r[0]['type'] == '2') $pass = TRUE;

		else $pass = FALSE;

		if ($pass) {
			// Запоминаем типа ачивки
			$type = $r[0]['type'];

			// Вытаскиваем экспу пользователя
			$q = "SELECT `exp` FROM `ololousers` WHERE `id` = $user";
			$r = $this->db->query($q);
			$exp = (int)$r[0]['exp'];
			$powah = 0;

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

				if ($grade == '1') $gift = $powah = $achExp + $r[0]['xpcost'];

				else $gift = $r[0]['xpcost'];
			}

			// Дарим пользователю определенную экспу
			$exp += $gift;
			$q = "UPDATE `ololousers` SET `exp` = $exp WHERE `id` = $user";
			$r = $this->db->query($q);

			// И записываем в таблицу инфу
			$time = time();
			$q = "INSERT INTO `user_achievs` (`user`, `achievement`, `ts`, `powah`) VALUES ($user, $ach, $time, $powah)";
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

		$q = "SELECT `ua`.`achievement` AS `ach`, `a`.`name`, `a`.`desc`, FROM_UNIXTIME(`ua`.`ts`) as `ts`, `a`.`type`, `a`.`class`, `a`.`grade`, `a`.`req`, `ua`.`powah`
					FROM `user_achievs` AS `ua`
					LEFT JOIN `achievements` AS `a` ON (`ua`.`achievement` = `a`.`id`)
					WHERE `ua`.`user` = $user ORDER BY `ua`.`ts` DESC;";
		$r = $this->db->query($q);
		$outArr = array();

		foreach ($r as $ach) {
			if ($ach['type'] == '1') $ach['progress'] = $this->prep($ach['ach']);

			if (!isset($outArr[ $ach['ach'] ])) $outArr[ $ach['ach'] ] = $ach;

			else if (!isset($outArr[ $ach['ach'] ][0])) $outArr[ $ach['ach'] ] = array(0 => $outArr[ $ach['ach'] ], 1 => $ach);

			else $outArr[ $ach['ach'] ] = array_merge($outArr[ $ach['ach'] ], array($ach));
		}

		return $outArr;
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
	public function getAll() {

		// Считаем пользователей чтоб узнать проценты получения ачивок
		$q = "SELECT count(*) AS `count` FROM `ololousers`";
		$r = $this->db->query($q);
		$userCount = $r[0]['count'];

		$q = "SELECT `a`.`id`, `ua`.`user`, `a`.`name`, `a`.`desc`, `a`.`type`, `a`.`class`, `a`.`grade`, `ua`.`ts`, `ua`.`powah`
					FROM `user_achievs` AS `ua`
					RIGHT JOIN `achievements` AS `a` ON (`a`.`id` = `ua`.`achievement`)
					ORDER BY `a`.`id` ASC , `user` ASC , `ts` DESC";
		$r = $this->db->query($q);

		foreach ($r as $ach) {

			if (!isset($output[ $ach['id'] ])) {
				$output[ $ach['id'] ] = array(
					  'name' => $ach['name']
					, 'desc' => $ach['desc']
					, 'type' => $ach['type']
					, 'class' => $ach['class']
					, 'grade' => $ach['grade']
					, 'users' => array()
				);

				if (isset($ach['user'])) $output[ $ach['id'] ]['users'][ $ach['user'] ][ $ach['ts'] ] = $ach['powah'];

				if ($ach['type'] == '1') $output[ $ach['id'] ]['progress'] = $this->prep($ach['id']);

			} else $output[ $ach['id'] ]['users'][ $ach['user'] ][ $ach['ts'] ] = $ach['powah'];

		}

		foreach ($output as $id => $ach) {
			$perc = round(count($ach['users']) / $userCount * 100);
			$output[$id]['perc'] = $perc;
		}

		return $output;
	}

/**
* 
* Ну это зерофилл, что тут можно сказать?
* @param str - строка/число
* @param outLen - требуемая длина
* @return string - отформатированная строка
* 
**/
	public function zerofill($str, $outLen) {
		$inpLen = strlen((string)$str);
		$addLen = $outLen - $inpLen;
		$addStr = '';
		for ($i = 0; $i < $addLen; $i++) {
			$addStr .= '0';
		}
		$addStr .= $str;
		return $addStr;
	}

/**
* 
* Узнать готовность достижения
* @param id - айдишник достижения
* 
**/
	public function prep($id) {
		$q = "SELECT `req` FROM `achievements` WHERE `id` = $id";
		$r = $this->db->query($q);
		$requirement = $r[0]['req'];

		switch ($id) {
			case 5: case 10: case 13: case 21: case 30: case 42: case 50: case 70: // Уровни
				$progress = $this->user->info['levelInfo']['level'];
			break;

			case 4: case 6: case 7:
				$q = "SELECT count(*) AS `count` FROM `ololousers` WHERE `referrer` = $id";
				$r = $this->db->query($q);
				$progress = $r[0]['count'];
			break;

			case 100500: // Опыт
				$progress = $this->user->info['exp'];
			break;

			default: $progress = 0;
		}

		if ($progress <= $requirement) $percentage = round($progress / $requirement * 100);
		else $percentage = 100;

		$output = array(
			  'perc' => $percentage
			, 'req' => $requirement
			, 'prog' => $progress
		);
		
		return $output;
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
		$q = "SELECT `a`.`id`, `a`.`req`
					FROM `achievements` AS `a`
					LEFT JOIN `user_achievs` AS `ua` ON (`a`.`id` = `ua`.`achievement`)
					LEFT JOIN `user_achievs` as `my` ON (`my`.`user` = $id AND `ua`.`achievement` = `my`.`achievement`)
					WHERE `my`.`ts` is null
					GROUP BY `a`.`id`;";
		$r = $this->db->query($q);

		/* Тут мы определяем все необходимые переменные для последующих условий */
		//////////////////////////////////////////////////////////////////////////
		// Узнаем свой уровень
		$level = $this->user->info['levelInfo']['level'];
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
					if ($count >= $ach['req']) {
						$this->earn($id, 4);
						$output[] = $ach['id'];
					}
					break;

				case '5':
					// Достигнут 5 уровень
					if ($level['level'] >= $ach['req']) {
						$this->earn($id, 5);
						$output[] = $ach['id'];
					}
					break;
				case '6':
					// Приглашено 10 друзей
					if ($count >= $ach['req']) {
						$this->earn($id, 6);
						$output[] = $ach['id'];
					}
					break;

				case '7':
					// Приглашено 15 друзей
					if ($count >= $ach['req']) {
						$this->earn($id, 7);
						$output[] = $ach['id'];
					}
					break;

				case '9':
					// Хитрая ачивка: дается рандомный образом
					if (rand(0, 1000) == 352) {
						$this->earn($id, 9);
						$output[] = $ach['id'];
					}
					break;

				case '10':
					// Достигнут 10 уровень
					if ($level['level'] >= $ach['req']) {
						$this->earn($id, 10);
						$output[] = $ach['id'];
					}
					break;

				case '13':
					// Достигнут 13 уровень
					if ($level['level'] >= $ach['req']) {
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
					if ($level['level'] >= $ach['req']) {
						$this->earn($id, 21);
						$output[] = $ach['id'];
					}
					break;

				case '30':
					// Достигнут 30 уровень
					if ($level['level'] >= $ach['req']) {
						$this->earn($id, 30);
						$output[] = $ach['id'];
					}
					break;

				case '42':
					// Достигнут 42 уровень
					if ($level['level'] >= $ach['req']) {
						$this->earn($id, 42);
						$output[] = $ach['id'];
					}
					break;

				case '50':
					// Достигнут 50 уровень
					if ($level['level'] >= $ach['req']) {
						$this->earn($id, 50);
						$output[] = $ach['id'];
					}
					break;

				case '70':
					// Достигнут 70 уровень
					if ($level['level'] == $ach['req']) {
						$this->earn($id, 70);
						$output[] = $ach['id'];
					}
					break;

				case '100500':
					// Накоплено 100500 опыта
					if ($info['exp'] >= $ach['req']) {
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