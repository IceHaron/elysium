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
	public function user($id) {

		$this->info = $this->getInfo($id);
	}

/**
* 
* Получение информации о юзере
* @param user - ник или почта юзера
* @return array - инфа о юзере
* 
**/
	public function getInfo($user) {

		GLOBAL $db;
		$q = "SELECT * FROM `ololousers` WHERE `id` = $user OR `nick` = '$user' OR `email` = $user";
		$r = $db->query($q);
		// Убираем пароль из массива, защита дохера
		unset ($r[0]['pw']);
		$r[0]['levelInfo'] = $this->getLevel($r[0]['exp']); // Добавляем в массив уровень
		return $r[0];
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

}