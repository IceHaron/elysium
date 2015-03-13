<?php
namespace m1;

/**
 * Class Autoloader
 * @package m1
 */
class Autoloader
{
	/**
	 * @var array пути от которых будет вестись поиск файлов
	 */
	private static $classMap = array(
		'/',
	);

	/**
	 *инициализируем автолодер
	 */
	public static function init()
	{
		\spl_autoload_register(array(__CLASS__ , 'autoload'), true, true);
	}

	/**
	 * добавляет путь в $classMap
	 * @param $path
	 */
	public static function addPath($path) {
		if (!in_array($path, self::$classMap)) {
			self::$classMap[] = $path;
		}

		return;
	}

	/**
	 * регистрируем дополнительную функцию обработчик
	 * @param $func
	 */
	public static function registerFunction($func)
	{
		\spl_autoload_register($func);

		return;
	}

	/**
	 * метод зарегестрированный при выполнении init в spl_autoload_register
	 * @param $class
	 */
	public static function autoload($class)
	{
		foreach (self::$classMap as $root) {
			$filePath = $_SERVER['DOCUMENT_ROOT'] . $root;
			$filePath .=  str_replace('\\', '/', $class) . '.php';
			if (file_exists($filePath)) {
				/** @noinspection PhpIncludeInspection */
				include_once $filePath;
				break;
			}
		}
	}

	/**
	 *не даем создавать экземпляры Autoloader
	 */
	private function __construct() {}

}


