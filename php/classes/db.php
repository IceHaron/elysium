<?php

/**
* 
* Класс для работы с базой
* 
**/

class db {
	public $link;

	public function db() {
		$server = "localhost";
		$dbuser = "root";
		// $dbpass = "olokari";
		$dbpass = "230105";
		$dbname = "elysium";

		$link = mysqli_connect($server, $dbuser, $dbpass, $dbname);
		if (!$link) {
			printf("<h2>Невозможно подключиться к базе данных.</h2> Код ошибки: %s\n", mysqli_connect_error());
			exit;
		} else $this->link = $link;
		
		if (!mysqli_set_charset($link, "utf8")) {
			printf(mysqli_error($link));
		} else {
			// printf("Current character set: " . mysqli_character_set_name($link));
		}
	}

	public function query($query) {
		$query_result = mysqli_query($this->link, $query);
		if (gettype($query_result) !== 'boolean') {
			while ($row = mysqli_fetch_assoc($query_result)) $res[] = $row;
		}
		else $res = ($query_result === FALSE) ? mysqli_error($this->link) : $query_result;
		if (mysqli_error($this->link) != '') return mysqli_error($this->link);
		return $res;
	}

}