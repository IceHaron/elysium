<?

/**
* 
* Класс для работы с юзером
* 
**/

class user {

	public $info; // Информация о пользователе

/**
* 
* Конструктор, сэр.
* Из овсянки, сэр.
* Получаем инфу о пользователе и запиливаем ее в параметр класса
* 
**/
	public function user($id = '') {

		if ($id != '') {
			$this->info = $this->getInfo($id);
			if ($this->info['group'] == '0') header("Location: /auth?action=off"); 

		} else if (isset($_SESSION['login'])) {
			$clogin = $_SESSION['login'];
			$cemail = $_SESSION['email'];
			$logged = $this->logIn($clogin, $cemail);

			if (!$logged) {
				unset($_SESSION['login']);
				unset($_SESSION['email']);
				header("Location: /");

			}

		}

	}

/**
* 
* Авторизация
* @param nick - ник юзверя
* @param mail - мыло юзверя
* @return bool - пропускаем или нет
* 
**/
	public function logIn($nick, $mail) {

		GLOBAL $db;
		$q = "SELECT `id` FROM `ololousers` WHERE `nick` = '$nick' AND `email` = '$mail'";
		$r = $db->query($q);

		if (count($r) == 1 && gettype($r) == 'array') {
			$this->info = $this->getInfo($r[0]['id']);
			return true;

		} else return false;
		
	}

/**
* 
* Получение информации о юзере
* @param user - айдишник юзверя
* @return array - инфа о юзере
* 
**/
	public function getInfo($user) {

		GLOBAL $db;
		$q = "SELECT * FROM `ololousers` WHERE `id` = '$user'";
		$r = $db->query($q);
		// Убираем пароль из массива, защита дохера
		if (isset($r[0]['pw'])) {
			unset ($r[0]['pw']);
			return $r[0];
		} else return array();
	}

/**
* 
* Получение информации о юзере
* @param user - ник или почта юзера
* @return array - инфа о юзере
* 
**/
	public function getFullInfo($user) {
		GLOBAL $db;
		$u = $this->getInfo($user);

		if ($u['referrer'] != 1) $referrer = $this->getInfo($u['referrer']);
		else $referrer = array('id' => 0, 'nick' => '');

		$q = "SELECT * FROM `usergroups` WHERE `id` = {$u['group']}";
		$r = $db->query($q);
		$groupName = $r[0]['name'];
		$groupPrefix = $r[0]['server_prefix'];

		switch ($u['group']) {
			case 777:
				$purchGroup = array(20000);
			break;
			
			default:
				$purchGroup = array();
			break;
		}

		$groupEnd = time();
		$q = "
			SELECT `purchases`.`id`, `purchases`.`cost`, `purchases`.`item`, `purchases`.`start`, `purchases`.`end`, `donuts`.`name`
			FROM `purchases`
			JOIN `donuts` ON (`purchases`.`item` = `donuts`.`id`)
			WHERE `user` = $user AND `start` < NOW() AND (`end` > NOW() OR `end` = '0000-00-00 00:00:00')
			ORDER BY `start` DESC;";
		$pur = $db->query($q);
		$i = 0;

		if (!empty($pur)) {
			foreach ($pur as $purchase) {
				if (array_search($purchase['item'], $purchGroup) !== FALSE && strtotime($purchase['end']) > $groupEnd) $groupEnd = strtotime($purchase['end']);
				if ($i++ > 10) continue;
				$purchases[] = $purchase;
			}
		} else $purchases = [];

		// $q = "SELECT `action`, count(*) AS `count` FROM `tokens` WHERE `user` = {$user} GROUP BY `action`";
		// $r = $db->query($q);

		// if (count($r) != 0)
			// foreach ($r as $token) {
				// $tokens[ $token['action'] ] = $token['count'];
			// }
		// else $tokens = array('changename' => 0);

		$q = "SELECT * FROM `purchases` WHERE `user` = $user AND `item` IN (10001) AND `start` < now() AND `end` > now();";
		$r = $db->query($q);
		$allowPrefix = count($r);

		$q = "SELECT * FROM `purchases` WHERE `user` = $user AND `item` IN (10002) AND `start` < now() AND `end` > now();";
		$r = $db->query($q);
		$allowNameColor = count($r);

		preg_match('/\&([0-9a-f])$/', $u['prefix'], $nameColor);
		if (!count($nameColor)) $nameColor = 'f';
		else $nameColor = $nameColor[1];
		$clearPrefix = trim(preg_replace('/\&r\]\s?(&[0-9a-f])?$/', '', preg_replace('/^\&r\[/', '', $u['prefix'])));

		$a = new achievement();
		// $a->check($r[0]['id']);
		$achievements = array_slice($a->getAch($u['id']), 0, 5); // Понятное дело: получаем список ачивок
		$achHTML = '';

		foreach ($achievements as $ach) {
			$achHTML .= $a->getHTML($ach);
		}

		$privacy = json_decode($u['privacy'], TRUE);
		// Собираем инфу о привязанном Steam-аккаунте, если он, конечно, есть
		$steamID = isset($steamUser['uid']) ? $steamUser['uid'] : $u['steamid'];
		// Profile
		if ($steamID) $profile = $this->getSteam($steamID);
		else $profile = FALSE;

		$coupons = $this->getCoupons($user);

		$q = "SELECT * FROM `acquiring` WHERE `user` = $user AND `paid` != -1 ORDER BY `date` DESC LIMIT 0,10";
		$orders = $db->query($q);


		$output = array(
			  'id' => $u['id'] // Айдишник
			, 'email' => $u['email'] // Мыло
			, 'nick' => $u['nick'] // Ник
			, 'allowPrefix' => $allowPrefix // Разрешен ли префикс
			, 'prefix' => $clearPrefix // Префикс
			, 'allowNameColor' => $allowNameColor // Разрешен ли цветной ник
			, 'nameColor' => $nameColor // Цвет ника
			, 'mcName' => $u['mcname'] // Имя в майнкрафте
			, 'levelInfo' => $this->getLevelHTML($this->getLevel($u['exp'])) // Уровень учетки
			, 'izum' => $u['izumko'] // Баланс изюма
			, 'referrer' => $referrer // Пригласивший
			, 'referral' => 'http://' . $_SERVER['HTTP_HOST'] . '/auth?action=reg&referrer=' . base64_encode($u['id'] . '_' . $u['nick']) // Реферральная ссылка
			, 'steamID' => $steamID // Steam ID, C.O.
			, 'achievements' => $achHTML // Последние 5 достижений
			, 'privacy' => $privacy // Настройки приватности в виде ассоциативного массива
			, 'groupName' => $groupName // Название группы, в которой состоит пользователь
			, 'groupPrefix' => $groupPrefix // Групповой префикс
			, 'groupEnd' => $groupEnd // Истечение группы
			// , 'tokens' => $tokens // Токены
			, 'coupons' => $coupons // Купоны
			, 'orders' => $orders ? $orders : [] // Покупки Изюма
			, 'purchases' => $purchases // Покупки всего
		);

		if ($profile) {
			// Добавляем инфу от Steam если к нам привязан их акк
			$output['steamName'] = $profile['response']['players'][0]['personaname'];
			$output['steamURL'] = $profile['response']['players'][0]['profileurl'];
			$output['avatar'] = $profile['response']['players'][0]['avatar'];
		}

		return $output;
	}

/**
* 
* Высчитать уровень по опыту, реюзабельность кода дофига
* @param exp - Количество опыта
* @return array - Уровень, количество экспы сверх уровня и требуемый опыт для получения следующего
* 
**/
	public function getLevel($exp) {

		$remain = $exp;
		$expForLevel = 100;
		$multiplier = 1.15; // Каждый следующий уровень требует на 15% больше опыта, чем предыдущий
		$level = 1;

		while ($remain > $expForLevel && $level < 70) {
			$level++;
			$remain = $exp - $expForLevel;
			$exp = $remain;
			$expForLevel = ceil($expForLevel * $multiplier);
		};

		$ret = array(
			  'level' => $level
			, 'exp' => $remain
			, 'need' => $expForLevel
		);

		return $ret;
	}

/**
* 
* Получение информации о юзере
* @param user - ник или почта юзера
* @return array - инфа о юзере
* 
**/
	public function getSteam($steamID) {
		$profile_str = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=0BE85074D210A01F70B48205C44D1D56&steamids=' . $steamID);

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////Нужно будет обязательно после регистрации домена получить API-Key для стима//////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		$profile = json_decode($profile_str, TRUE);
		// Вот эта строка - и есть заглушка на случай если Steam недоступен
		if (!isset($profile['response']['players'][0])) $profile = array('response'=> array('players' => array(0 => array("personaname" => "Dummy", "profileurl" => "http://google.com", "avatar" => "http://placehold.it/32x32"))));

		return $profile;
	}

/**
* 
* Получение HTML уровня
* @param levelArr - информация об уровне массивом из функции getLevel
* @return string - HTML
* 
**/
	public function getLevelHTML($levelArr) {

		if ($levelArr['level'] < 70) {
			$percent = floor($levelArr['exp'] / $levelArr['need'] * 100);
			$signature = $levelArr['exp'] . ' / ' . $levelArr['need'] . ' exp (' . $percent . '%)';

		} else {
			$percent = 100;
			$signature = $levelArr['exp'] . ' exp';
		}

		$output = '
			<div class="level">' . $levelArr['level'] . '</div>
			<div class="exp">
				' . $signature . '
				<div class="expBarEmpty"></div>
				<div class="expBarFull" style="width: ' . (int)$percent*2 . 'px"></div>
			</div>';

		return $output;
	}

/**
* 
* Получение купонов пользователя
* @param user - айдишник пользователя
* @return array - массив с купонами
* 
**/
	public function getCoupons($user) {
		GLOBAL $db;
		$q = "
			SELECT `coupons`.`id` AS `firstID`, `discounts`.`type`, `discounts`.`name` AS `ruName`, `discounts`.`effect`, `discounts`.`group`, count(*) AS `count`, `coupons`.`until` AS `firstEnd`
			FROM `coupons`
			JOIN `discounts` ON `coupons`.`discount` = `discounts`.`id`
			WHERE `coupons`.`active` = 1 AND `coupons`.`user` = $user GROUP BY `type`;";
		$r = $db->query($q);

		if (count($r)) {
			foreach ($r as $coupon) {
				if ($coupon['type'] == 'votediscount') $coupon['effect'] = (float)$coupon['effect'] * (int)$coupon['count'];
				$coupons[ $coupon['type'] ] = $coupon;
			}
		} else $coupons = [];

		return $coupons;
	}

}