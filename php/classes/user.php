<?

class user {

	public $info;

	public function user($id) {
		$this->info = $this->getInfo($id);
	}

	public function getInfo($user) {
		GLOBAL $db;
		$q = "SELECT * FROM `ololousers` WHERE `id` = $user OR `nick` = '$user' OR `email` = $user";
		$r = $db->query($q);
		unset ($r[0]['pw']);
		return $r[0];
	}

	public function getLevel($exp) {
		$remain = $exp;
		$expForLevel = 100;
		$multiplier = 1.15;
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