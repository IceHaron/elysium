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

			} else if ($this->info['group'] == '0') header("Location: /auth?action=off");

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
		$q = "SELECT `action`, count(*) AS `count` FROM `tokens` WHERE `user` = {$user} GROUP BY `action`";
		$r = $db->query($q);

		if (count($r) != 0)
			foreach ($r as $token) {
				$tokens[ $token['action'] ] = $token['count'];
			}
		else $tokens = array('changename' => 0);

		$q = "SELECT * FROM `ololousers` WHERE `id` = '$user'";
		$r = $db->query($q);
		// Убираем пароль из массива, защита дохера
		if (isset($r[0]['pw'])) {
			unset ($r[0]['pw']);
			$r[0]['tokens'] = $tokens;
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

		$output = array(
			  'id' => $u['id'] // Айдишник
			, 'email' => $u['email'] // Мыло
			, 'nick' => $u['nick'] // Ник
			, 'mcName' => $u['mcname'] // Имя в майнкрафте
			, 'levelInfo' => $this->getLevelHTML($this->getLevel($u['exp'])) // Уровень учетки
			, 'izum' => $u['izumko'] // Баланс изюма
			, 'referrer' => $referrer // Пригласивший
			, 'referral' => 'http://' . $_SERVER['HTTP_HOST'] . '/auth?action=reg&referrer=' . base64_encode($u['id'] . '_' . $u['nick']) // Реферральная ссылка
			, 'steamID' => $steamID // Steam ID, C.O.
			, 'achievements' => $achHTML // Последние 5 достижений
			, 'privacy' => $privacy // Настройки приватности в виде ассоциативного массива
			, 'groupName' => $groupName // Название группы, в которой состоит пользователь
			, 'tokens' => $u['tokens'] // Токены
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

}