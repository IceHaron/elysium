<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 09.03.15
 * Time: 12:51
 */
namespace m1\traits;

/**
 * Trait Singleton
 * @package m1\traits
 */
trait Singleton
{
	/**
	* статическая переменная с экземпляром класса.
	 * @var
	 */
	protected static $instance;

	/**
	 * возвращает инстанс
	 * @return static
	 */
	final public static function getInstance()
	{
		return isset(static::$instance)
			? static::$instance
			: static::$instance = new static;
	}

	/**
	 *закрытый конструктор
	 */
	final private function __construct()
	{
		$this->init();
	}

	/**
	 *выполняется при создании экземпляра
	 */
	protected function init() {}

	/**
	 *запрещаем распаковывать объекты
	 */
	final private function __wakeup() {}

	/**
	 *запрещаем клонировать объекты
	 */
	final private function __clone() {}
}