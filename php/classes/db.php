<?php

/**
* 
* Класс для работы с базой
* 
**/

class db {

	public $link; // Ссылка на подключение к базе

/**
* 
* Конструктор, сэр.
* Из овсянки, сэр.
* Подключаемся к базе и если что-то не так, выводим сообщение
* 
**/
	public function db() {

		REQUIRE_ONCE('settings.php');
	
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

/**
* 
* Запрос в базу, собираем результат в ассоциативный массив или возвращаем сообщение об ошибке
* @return mixed - результат выполнения запроса: массив данных или текст ошибки
* 
**/
	public function query($query) {

		$query_result = mysqli_query($this->link, $query);

		if (gettype($query_result) !== 'boolean') { // Результат не булевый (это селект)
			while ($row = mysqli_fetch_assoc($query_result)) $res[] = $row; // Собираем данные в ассоциативный массив
		}

		else $res = ($query_result === FALSE) ? mysqli_error($this->link) : $query_result; // Результат булевый, если не ошибка, то ответ базы на запрос

		if (mysqli_error($this->link) != '') return mysqli_error($this->link); // Если все-таки ошибка, то возвращаем ее текст

		if (!isset($res)) $res = NULL; // Если же в итоге массив пустой, обращаем его в NULL

		return $res;
	}

/**
* 
* Функция эскейпа
* Создана для того, чтоб не гонять туда-сюда линку на базу, да и лишние слои защиты можно впилить сразу сюда
* Централизация и реюзабельность кода, короче
* @param str - сырые данные
* @return string - защищенные и облупленне данные
* 
**/
	public function escape($str='') {
		$str = htmlspecialchars($str);
		return mysqli_real_escape_string($this->link, $str);
	}

}