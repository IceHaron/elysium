CREATE DATABASE  IF NOT EXISTS `elysium` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `elysium`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: localhost    Database: elysium
-- ------------------------------------------------------
-- Server version	5.6.15-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `banlist`
--

DROP TABLE IF EXISTS `banlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banlist` (
  `id` int(10) unsigned NOT NULL,
  `type` int(11) NOT NULL DEFAULT '9',
  `reason` tinytext,
  `admin` int(10) unsigned NOT NULL DEFAULT '0',
  `ban` int(10) unsigned NOT NULL,
  `unban` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banlist`
--

LOCK TABLES `banlist` WRITE;
/*!40000 ALTER TABLE `banlist` DISABLE KEYS */;
INSERT INTO `banlist` VALUES (2,9,'test',1,0,0);
/*!40000 ALTER TABLE `banlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elysadmins`
--

DROP TABLE IF EXISTS `elysadmins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elysadmins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `superiority` varchar(3) DEFAULT '000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elysadmins`
--

LOCK TABLES `elysadmins` WRITE;
/*!40000 ALTER TABLE `elysadmins` DISABLE KEYS */;
INSERT INTO `elysadmins` VALUES (1,'777');
/*!40000 ALTER TABLE `elysadmins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `intro` text NOT NULL,
  `text` mediumtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'Воскрешение','<p>Добрый день, Дорогие Друзья!!! <br/>\nСпешим сообщить Вам о том, что началась разработка старого доброго Elysium Game! Вас ждут новый сайт и форум, новые сервера, новые возможности. Открываются новые горизонты для тех, кто был с нами и присоединится в скором времени! </p>','<p>Добрый день, Дорогие Друзья!!! <br/>\nСпешим сообщить Вам о том, что началась разработка старого доброго Elysium Game! Вас ждут новый сайт и форум, новые сервера, новые возможности. Открываются новые горизонты для тех, кто был с нами и присоединится в скором времени! </p>\n<p>Следите за новостями! Позже будут объявлены сроки ЗБТ и ОБТ. Ну и конечно же море конкурсов и фана! </p>','2014-04-05 09:16:53'),(2,'О сайте','<p>Всем Доброго дня! С Вами снова Zaidik. Начну сразу с важного!<br/>\nСайт скоро будет запущен для общего доступа. Мы переехали на новый домен, который позже будет объявлен в группе.</p>','<p>Всем Доброго дня! С Вами снова Zaidik. Начну сразу с важного!<br/>\nСайт скоро будет запущен для общего доступа. Мы переехали на новый домен, который позже будет объявлен здесь, в группе. Через пару дней после сайта запустим форум, где вы уже сможете заранее зарегистрироваться и обсудить все важные вопросы как с другими игроками, так и с членами администрации. Ну и конечно же ЗБТ, который так все ждут! Ориентировочное время проведения - майские праздники! Самым активным достанутся различные \"плюшки\" к открытию сервера! </p>\n<p>Оставайтесь с нами и мы Вас не разочаруем!</p>','2014-04-22 11:50:55'),(3,'Срываем покровы','<p>Всем привет! Мы с коллективом подумали и решили открыть несколько секретов разработки будущего сервера.<br/>\nПолагаю, что все играли в MMO игры. Да и наш любимый Minecraft относится к этой категории игр. Но концепции Minecraft\'a и, например, ArcheAge принципиально отличаются...</p>','<p>Всем привет! Мы с коллективом подумали и решили открыть несколько секретов разработки будущего сервера. </p>\n<p>Полагаю, что все играли в MMO игры. Да и наш любимый Minecraft относится к этой категории игр. Но концепции Minecraft\'a и, например, ArcheAge принципиально отличаются. Разные жанры, разные движки... Да к чему, собственно, углубляться в технические особенности. Скажу вот что - мы приблизим наш \"Майн\" к жанру MMORPG. Как именно? Введем уровни. Это будут не те уровни, которые вы тратите на зачарование вещей. Нет, это другое. Они позволят вам покупать большие участки территорий в городе. Или, скажем, стать руководителем завода по переработке дерева (да-да, Jobs теперь будет выглядеть совсем иначе, но об этом позже).</p>\n<p>Вы спросите: \"А как же все это будет реализовано?\". Наш ответ: Через Steam! Привязка аккаунта Steam будет необязательна, но она существенно упростит ваше существование на сервере.</p>\n<p>Интересно? Продолжим. Введение ачивок - это тоже одна из составляющих концепции будущего сервера. Убил дракона? Получил миллион игровой валюты? Отработал 3 смены подряд в шахте? Получи зачарованную кирку. Приятно получать такие сюрпризы, верно?</p>','2014-04-30 11:26:01');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ololousers`
--

DROP TABLE IF EXISTS `ololousers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ololousers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nick` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `pw` varchar(45) NOT NULL,
  `steamid` varchar(45) DEFAULT NULL,
  `exp` int(11) NOT NULL DEFAULT '0',
  `history` longtext,
  PRIMARY KEY (`id`,`email`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `nick_UNIQUE` (`nick`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ololousers`
--

LOCK TABLES `ololousers` WRITE;
/*!40000 ALTER TABLE `ololousers` DISABLE KEYS */;
INSERT INTO `ololousers` VALUES (1,'Ice_Haron','ice-haron@rambler.ru','c91ffe86fa9c1be66e92552288e4829c','76561197991665605',100500,NULL),(2,'dummy','1','',NULL,0,NULL),(3,'admin','2','',NULL,0,NULL),(4,'ololosh','3','a619d974658f3e749b2d88b215baea46',NULL,0,'{\"created\":1398776655}'),(11,'a','a','92eb5ffee6ae2fec3ad71c777531578f',NULL,0,'{\"created\":1398777640}');
/*!40000 ALTER TABLE `ololousers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'elysium'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-30 16:00:55
