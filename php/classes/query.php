<?
/**
*
* Класс для обработки внешних запросов
* Никаких инклудов и реквайров здесь нет, ибо доступ к классу невозможно получить напрямую, только через страницу сайта.
*
**/

if (!isset($db)) exit("Bad Include");

class Query {

	private $ach;
	private $db;
	private $mailer;
	private $cid;
	private $clogin;
	private $cemail;
	private $user;
	private $anon = FALSE;
	
	public function __construct() {
		GLOBAL $achievement, $db, $mailer, $user, $clogin;
		$this->ach = $achievement;
		$this->db = $db;
		$this->mailer = $mailer;
		if ($clogin != '') {
			$this->user = $user;
			$this->cid = $this->user->info['id'];
			$this->clogin = $this->user->info['nick'];
			$this->cemail = $this->user->info['email'];
		} else $this->anon = TRUE;
	}

/**
*
* Проверка и получение неполученных достижений
* @return string - список полученных ачивок в JSON виде
*
**/
	public function achCheck() {
		if ($this->anon) exit('Need to log in');

		return $this->ach->check();
	}

/**
*
* Получить HTML-код для отображения всплывающей ачивки
* @param achid - айдишник ачивки
* @return string - HTML-код блока ачивки
*
**/
	public function getAchHtml($achid) {
		$q = "SELECT * FROM `achievements` WHERE `id` = $achid";
		$r = $db->query($q);
		$achievement = $r[0];
		$output = '
		<div class="achievementWrapper" id="ach-' . $achid . '">
			<div class="achievement grade_' . $achievement['grade'] . '">
				<span class="achTitle">' . $achievement['name'] . '</span>
				<span class="achDate">' . date('Y-m-d H:i:s') . '</span>
				<span class="achDesc">' . $achievement['desc'] . '</span>
			</div>
		</div>';

		return $output;
	}

/**
*
* Спереть алмаз
* @return string - предупреждение
*
**/
	public function stealDiamond() {
		if (!$this->anon) $this->ach->earn($this->cid, 17);

		return 'Diamond stolen! RUN!';
	}

/**
*
* Проверка статусов серверов
* @param server - название сервера
* @return array - статус сервера
*
**/
	public function pingServer($server) {
		// Получаем статусы всех серверов
		$ip = '144.76.111.114';
		$port = array('kernel' => 25565, 'backtrack' => 25566, 'gentoo' => 25567);
		$status = pingMCServer($ip, $port[$server]);
		$output = json_encode(array('players' => (int)str_replace(chr(0), '', $status[3]), 'limit' => (int)str_replace(chr(0), '', $status[4])));

		return $output;
	}

/**
*
* Получение банлиста
* @return string - список банов в формате JSON
*
**/
	public function checkBL() {
		$l = mysqli_connect('144.76.111.114', 'site', 'u94fmE4KrxeLP5Pe', 'server', '3306');
		mysqli_set_charset($l, "utf8");
		$q = "SELECT * FROM `banlist` LIMIT 0,10";
		$b = mysqli_query($l, $q);
		while ($ban = mysqli_fetch_assoc($b)) {
			$bans[] = array('player' => $ban['name'], 'reason' => $ban['reason'], 'admin' => $ban['admin'], 'ban' => date("d-m-Y H:i", $ban['time']), 'unban' => ($ban['temptime'] ? date("d-m-Y H:i", $ban['temptime']) : 'Навечно'));
		}

		return json_encode($bans);
	}

/**
*
* Получение голоса от topcraft.ru
* @param username - ник пользователя
* @param timestamp - время голоса
* @return string - статус
*
**/
	public function voteTopCraft($username, $timestamp) {
		$gift = 1000; // Количество денег, которое получит игрок за голосование.

		$secretkey = '59c40a85aae924c47f7208ea4ea1f038'; // Ваш секретный ключ на TopCraft.Ru (Настраивается в Настройках проектов --> Поощрения)
		
		//Далее идёт код отвечающий за выдачу поощрений!

		$q = "SELECT `id` FROM `ololousers` WHERE `mcname` = '$username'";
		$r = $this->db->query($q);
		
		if (!count($r)) return "Bad login";
		else $userid = $r[0]['id'];
		
		if ($_POST['signature'] != sha1($username.$timestamp.$secretkey)) return "hash mismatch";

		$bonus = giveBonus($userid, $gift, 'vote', 'Голос на topcraft.ru');
		$coupon = giveCoupon($userid, 'votediscount', 0.1);

		if ($check && $coupon) return 'OK<br />';
		else return "Shit happened";

		//Конец скрипта.

		//Last update: 28.03.2013
	}

/**
*
* Получение голоса от fairtop.ru
* @param username - ник пользователя
* @param timestamp - время голоса
* @param hash - проверочная строка
* @return string - статус
*
**/
	public function voteFairTop($username, $timestamp, $hash) {
		if (empty($username) or empty($_POST['hash'])) return "Empty query";
		$gift = 1000; // Количество денег, которое получит игрок за голосование.

		$secretkey = 'ee2b68a81106100e41332d7c6936d0dd'; // Ваш секретный ключ на TopCraft.Ru (Настраивается в Настройках проектов --> Поощрения)
		
		//Далее идёт код отвечающий за выдачу поощрений!

		$q = "SELECT `id` FROM `ololousers` WHERE `mcname` = '$username'";
		$r = $this->db->query($q);
		
		if (!count($r)) return "Bad login";
		else $userid = $r[0]['id'];
		
		if ($hash != md5(sha1($username.$secretkey))) return "Invalid hash";

		$bonus = giveBonus($userid, $gift, 'vote', 'Голос на fairtop.ru');
		$coupon = giveCoupon($userid, 'votediscount', 0.1);

		if ($bonus && $coupon) return 'Success';
		else return "Shit happened";

	}

/**
*
* 
*
**/
	public function void() {

	}

}