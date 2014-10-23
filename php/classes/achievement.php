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
			$q = "SELECT `grade`, `xpcost` FROM `achievements` WHERE `id` = $ach";
			$r = $this->db->query($q);
			$grade = $r[0]['grade'];

			if ($achExp === FALSE) {
				// Если не дано количество экспы параметром, вытягиваем его из базы
				$gift = (int)$r[0]['xpcost'];

				if ($grade == '1') $powah = $gift;

			} else {
				// Если экспу нам сообщили и грейд ачивки правильный, плюсуем экспу к той, что мы вытянули из базы

				if ($grade == '1' && $ach != 11) $gift = $powah = $achExp + $r[0]['xpcost'];
				
				else if ($grade == '1' && $ach == 11) { $gift = 0; $powah = $achExp; } // Индивидуальный подход к ачивке "К бабкам на рынок"

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
		} else return FALSE;

	}

/**
* 
* Получаем ачивки юзера
* @param user - Айдишник юзера
* @return array - ачивки, КЭП
* 
**/
	public function getAch($user) {
		$all = $this->getAll();

		$outArr = array();

		foreach ($all as $achID => $ach) {
			if (isset($ach['users'][$user])) {
				$last = max(array_keys($ach['users'][$user]));
				$outArr[ $last ] = $ach;
			}
		}

		krsort($outArr);

		return $outArr;
	}

/**
* 
* Смотрим, есть ли определенное достижение у пользователя
* @param userID - Айдишник пользователя
* @param achID - Айдишник ачивки
* @return bool - Наличие достижения у пользователя
* 
**/
	public function look($userID, $achID) {

		$q = "SELECT * FROM `user_achievs` WHERE `user` = $userID AND `achievement` = $achID";
		$r = $this->db->query($q);

		if (!$r) return FALSE;

		else return TRUE;

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
* @return array - ачивки
* 
**/
	public function getAll() {

		// Считаем пользователей чтоб узнать проценты получения ачивок
		$q = "SELECT `id` FROM `ololousers`";
		$r = $this->db->query($q);
		$userCount = count($r);

		$q = "SELECT `a`.`id`, `ua`.`user`, `a`.`name`, `a`.`desc`, `a`.`type`, `a`.`class`, `a`.`grade`, `ua`.`ts`, `ua`.`powah`
					FROM `user_achievs` AS `ua`
					RIGHT JOIN `achievements` AS `a` ON (`a`.`id` = `ua`.`achievement`)
					ORDER BY `a`.`id` ASC , `user` ASC , `ts` DESC";
		$r = $this->db->query($q);

		foreach ($r as $ach) {

			if (!isset($output[ $ach['id'] ])) {
				$output[ $ach['id'] ] = array(
					  'id' => $ach['id']
					, 'name' => $ach['name']
					, 'desc' => $ach['desc']
					, 'type' => $ach['type']
					, 'class' => $ach['class']
					, 'grade' => $ach['grade']
					, 'users' => array()
				);

				if (isset($ach['user'])) $output[ $ach['id'] ]['users'][ $ach['user'] ][ $ach['ts'] ] = $ach['powah'];

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
* Узнать готовность достижения
* @param achID - айдишник достижения
* 
**/
	public function prep($achID, $userID) {
		if ($userID) {
			$userInfo = $this->user->getInfo($userID);
			$q = "SELECT `req` FROM `achievements` WHERE `id` = $achID";
			$r = $this->db->query($q);
			$requirement = $r[0]['req'];

			switch ($achID) {
				case 5: case 10: case 13: case 21: case 30: case 42: case 50: case 70: // Уровни
					$l = $this->user->getLevel($userInfo['exp']);
					$progress = $l['level'];
				break;

				case 4: case 6: case 7: // Приглашенные
					$q = "SELECT count(*) AS `count` FROM `ololousers` WHERE `referrer` = $userID";
					$r = $this->db->query($q);
					$progress = $r[0]['count'];
				break;

				case 26: case 27: case 28: case 29: // Голоса
					$q = "SELECT count(*) AS `count` FROM `votes` WHERE `user` = $userID";
					$r = $this->db->query($q);
					$progress = $r[0]['count'];
				break;

				case 100500: // Опыт
					$progress = $userInfo['exp'];
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
		} else $output = FALSE;
		return $output;
	}

/**
* 
* Получить HTML-код ачивок
* @param achInfo - массив с информацией об ачивке
* @param userID - айдишник пользователя для получения полной инфы: готовность, прогресс, факт получения
* @return array - полученные ачивки
* 
**/
	public function getHTML($achInfo, $userID = FALSE) {
		$output = '';
		$add = '';

		if ($userID === FALSE) $userID = isset($this->user) ? $this->user->info['id'] : FALSE;

		if ($userID !== FALSE) {

			if ($achInfo['type'] == 1) {
				$progress = $this->prep($achInfo['id'], $userID);
				if ($progress) $add .= '
					<div class="achProgress">
						Завершено на ' . $progress['perc'] . '% (' . $progress['prog'] . ' / ' . $progress['req'] . ')
						<div class="expBarEmpty"></div>
						<div class="expBarFull" style="width: ' . (int)$progress['perc']*2 . 'px"></div>
					</div>';
			}

			if ($achInfo['type'] != '2') {
				// Если мы залогинены, то нужно определить, какую ачивку мы получили, а какую - нет
				if (!isset($achInfo['users'][$userID])) {
					$class = 'achievementWrapper unclaimed';
					$date = '';

				} else {
					$class = 'achievementWrapper';
					$last = max(array_keys($achInfo['users'][$userID]));
					$date = date('Y-m-d H:i:s', $last);

					if ($achInfo['grade'] == 1) $add .= '<div><span class="achPowah">Мощь: ' . $achInfo['users'][$userID][$last] . '</span><div class="clear"></div></div>';

				}

				$output .= '
					<div class="' . $class . '">
						<div class="achievement grade_' . $achInfo['grade'] . '">
							<span class="achTitle">' . $achInfo['name'] . '</span>
							<span class="achDate">' . $date . '</span>
							<span class="achDesc">' . $achInfo['desc'] . '</span>
							' . $add . '
						</div>
					</div>';
			} else {
				if (isset($achInfo['users'][$userID])) {
					krsort($achInfo['users'][$userID]);
					$achi = array_slice($achInfo['users'][$userID], 0, 5, TRUE);
					$output .= '<div class="achievementStack">';
					$counter = 0;
					foreach ($achi as $ts => $powah) {
						$add = '';
						$z_index = 5 - $counter;
						$margin = count($achi)*5 - $counter * 5 - 5;
						$date = date('Y-m-d H:i:s', $ts);
						if ($achInfo['grade'] == 1) $add .= '<div><span class="achPowah">Мощь: ' . $powah . '</span><div class="clear"></div></div>';
						$add .= '<div><span class="achCount">Всего таких ачивок: ' . count($achInfo['users'][$userID]) . '</span><div class="clear"></div></div>';
						$counter++;
						$output .= '
							<div class="achievementWrapper improved" style="z-index: ' . $z_index . '; margin: ' . $margin . 'px;">
								<div class="achievement grade_' . $achInfo['grade'] . '">
									<span class="achTitle">' . $achInfo['name'] . '</span>
									<span class="achDate">' . $date . '</span>
									<span class="achDesc">' . $achInfo['desc'] . '</span>
									' . $add . '
								</div>
							</div>';
					}

					$output .= '
							<div class="achPlaceholder" style="margin: 0 0 ' . ($counter*10) . 'px 0;">
								<div class="achievement grade_1">
									<span class="achTitle">' . $achInfo['name'] . '</span>
									<span class="achDate">null</span>
									<span class="achDesc">' . $achInfo['desc'] . '</span>
									<div><span class="achPowah">null</span><div class="clear"></div></div>
								</div>
							</div>
						</div>';
				} else {
					$date = '';
					$output .= '
						<div class="achievementWrapper unclaimed">
							<div class="achievement grade_' . $achInfo['grade'] . '">
								<span class="achTitle">' . $achInfo['name'] . '</span>
								<span class="achDate">' . $date . '</span>
								<span class="achDesc">' . $achInfo['desc'] . '</span>
							</div>
						</div>';
				}

			}

		} else {
			echo '
				<div class="achievementWrapper" style="float: left;">
					<div class="achievement grade_' . $achInfo['grade'] . '">
						<span class="achTitle">' . $achInfo['name'] . '</span>
						<span class="achDesc">' . $achInfo['desc'] . '</span>
					</div>
				</div>';

		}

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

				case '16':
					// Я по делу
					if ($info['group'] == '5') {
						$this->earn($id, 16);
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

				case '24':
					// Мы - тестер
					if ($info['group'] == 5) {
						$this->earn($id, 24);
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