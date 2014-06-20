CREATE DATABASE  IF NOT EXISTS `srv44030_elysium` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `srv44030_elysium`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: mysql-srv44030.ht-systems.ru    Database: srv44030_elysium
-- ------------------------------------------------------
-- Server version	5.5.25-log

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
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `desc` text NOT NULL,
  `xpcost` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `class` tinyint(4) NOT NULL,
  `grade` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100501 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievements`
--

LOCK TABLES `achievements` WRITE;
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
INSERT INTO `achievements` VALUES (0,'Welcome to Alpha!','Создать аккаунт на стадии закрытого альфа-теста',30000,0,0,3),(1,'Попаримся?','Привязать к учетной записи аккаунт Steam',500,0,0,0),(2,'Попутал','Отвязать аккаунт Steam от учетной записи',0,0,0,0),(3,'Давай строить вместе','Пригласить друга на портал',100,0,0,0),(4,'Это - моя Бригада','Пригласить 5 друзей на портал',500,1,0,0),(5,'Дай пять!','Достичь 5 уровня аккаунта',50,1,0,0),(6,'А нас орда','Пригласить 10 друзей на портал',1000,1,0,0),(7,'А нас рать','Пригласить 15 друзей на портал',1500,1,0,0),(8,'Затянуло','Быть приглашенным на портал',500,0,0,3),(9,'Secret what?','In my silence where no one else can hear<br/>\nwhat is right, what is wrong.<br/>\nIn my silence where no one else forgives,<br/>\nwhere the sane and insane strikes together as one...',1000,0,1,0),(10,'Первая десятюня','Достичь 10 уровня аккаунта',150,1,0,0),(11,'К бабкам на рынок','Купить Изюма',0,2,0,1),(12,'Кулхацкер','Попытаться взломать сайт',100,0,1,0),(13,'Чертова дюжина','Достичь 13 уровня аккаунта',250,1,0,0),(14,'Я сам пришел','Не быть приглашенным на портал',0,0,0,3),(21,'Очко!','Остановись, хватит, ты уже победил',1000,1,0,0),(30,'Я уже нагибаю','Достичь 30 уровня аккаунта',1250,1,0,0),(42,'Смысл жизни','The Ultimate Question of Life, the Universe, and Everything',4200,1,1,0),(50,'Полтюня','Достичь 50 уровня аккаунта',5000,1,0,2),(70,'MaxLevel','Достичь 70 и последнего уровня аккаунта',0,1,0,2),(100500,'Стопицот','Накопить 100500 опыта',3000,1,0,0);
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;
UNLOCK TABLES;

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
  `ban` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `unban` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banlist`
--

LOCK TABLES `banlist` WRITE;
/*!40000 ALTER TABLE `banlist` DISABLE KEYS */;
INSERT INTO `banlist` VALUES (2,9,'test',1,'2014-04-30 12:28:12',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'Воскрешение','<p>Добрый день, Дорогие Друзья!!! <br/>\nСпешим сообщить Вам о том, что началась разработка старого доброго Elysium Game! Вас ждут новый сайт и форум, новые сервера, новые возможности. Открываются новые горизонты для тех, кто был с нами и присоединится в скором времени! </p>','<p>Добрый день, Дорогие Друзья!!! <br/>\nСпешим сообщить Вам о том, что началась разработка старого доброго Elysium Game! Вас ждут новый сайт и форум, новые сервера, новые возможности. Открываются новые горизонты для тех, кто был с нами и присоединится в скором времени! </p>\n<p>Следите за новостями! Позже будут объявлены сроки ЗБТ и ОБТ. Ну и конечно же море конкурсов и фана! </p>','2014-04-05 09:16:53'),(2,'О сайте','<p>Всем Доброго дня! С Вами снова Zaidik. Начну сразу с важного!<br/>\nСайт скоро будет запущен для общего доступа. Мы переехали на новый домен, который позже будет объявлен в группе.</p>','<p>Всем Доброго дня! С Вами снова Zaidik. Начну сразу с важного!<br/>\nСайт скоро будет запущен для общего доступа. Мы переехали на новый домен, который позже будет объявлен здесь, в группе. Через пару дней после сайта запустим форум, где вы уже сможете заранее зарегистрироваться и обсудить все важные вопросы как с другими игроками, так и с членами администрации. Ну и конечно же ЗБТ, который так все ждут! Ориентировочное время проведения - майские праздники! Самым активным достанутся различные \"плюшки\" к открытию сервера! </p>\n<p>Оставайтесь с нами и мы Вас не разочаруем!</p>','2014-04-22 11:50:55'),(3,'Срываем покровы','<p>Всем привет! Мы с коллективом подумали и решили открыть несколько секретов разработки будущего сервера.<br/>\nПолагаю, что все играли в MMO игры. Да и наш любимый Minecraft относится к этой категории игр. Но концепции Minecraft\'a и, например, ArcheAge принципиально отличаются...</p>','<p>Всем привет! Мы с коллективом подумали и решили открыть несколько секретов разработки будущего сервера. </p>\n<p>Полагаю, что все играли в MMO игры. Да и наш любимый Minecraft относится к этой категории игр. Но концепции Minecraft\'a и, например, ArcheAge принципиально отличаются. Разные жанры, разные движки... Да к чему, собственно, углубляться в технические особенности. Скажу вот что - мы приблизим наш \"Майн\" к жанру MMORPG. Как именно? Введем уровни. Это будут не те уровни, которые вы тратите на зачарование вещей. Нет, это другое. Они позволят вам покупать большие участки территорий в городе. Или, скажем, стать руководителем завода по переработке дерева (да-да, Jobs теперь будет выглядеть совсем иначе, но об этом позже).</p>\n<p>Вы спросите: \"А как же все это будет реализовано?\". Наш ответ: Через Steam! Привязка аккаунта Steam будет необязательна, но она существенно упростит ваше существование на сервере.</p>\n<p>Интересно? Продолжим. Введение ачивок - это тоже одна из составляющих концепции будущего сервера. Убил дракона? Получил миллион игровой валюты? Отработал 3 смены подряд в шахте? Получи зачарованную кирку. Приятно получать такие сюрпризы, верно?</p>','2014-04-30 11:26:01'),(4,'Проблемы','<p>Всем привет, меня зовут Харон и я - веб-программист проекта Elysium Game.\nДело в том, что я периодически захожу в группу и поглядываю на комментарии, тенденция обсуждений мне не очень нравится, а растормошить кого-то из коллектива для официального заявления у меня не получилось, так что пишу сам.</p>','<p>Всем привет, меня зовут Харон и я - веб-программист проекта Elysium Game.\nДело в том, что я периодически захожу в группу и поглядываю на комментарии, тенденция обсуждений мне не очень нравится, а растормошить кого-то из коллектива для официального заявления у меня не получилось, так что пишу сам.</p>\n<p>Мы столкнулись с непредвиденными проблемами.<br/>\nНе буду вдаваться в подробности и выносить решение \"кто прав, кто виноват\", но скажу очень важные слова:<br/>\nРабота ведется.</p>\n<p>Медленно, разрозненно, продвижения практически не наблюдается, но работа идет. Мы планировали в начале мая запустить форум и сайт, а к лету запустить сервер. Как говорится, не фортануло.</p>\n<p>Уверяю вас: проект будет жить. Обещать ничего не могу и не буду, но сам лично надеюсь, что в июне уже запустимся.</p>\n<p>Мы про вас не забыли и не забили, в ближайшие дни будет назначен ответственный по связям с общественностью, который будет постоянно держать вас в курсе событий.<br/>\nА если и не будет назначен, то держать вас в курсе событий буду я.</p>\n<p>Огромное спасибо вам всем за терпение и понимание.</p>','2014-05-29 10:43:00');
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
  `email` varchar(45) NOT NULL,
  `nick` varchar(45) NOT NULL,
  `mcname` varchar(45) DEFAULT NULL,
  `pw` varchar(45) NOT NULL,
  `steamid` varchar(45) DEFAULT NULL,
  `exp` int(11) NOT NULL DEFAULT '0',
  `history` longtext NOT NULL,
  `referrer` int(11) NOT NULL DEFAULT '1',
  `izumko` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `nick_UNIQUE` (`nick`),
  UNIQUE KEY `mcname_UNIQUE` (`mcname`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ololousers`
--

LOCK TABLES `ololousers` WRITE;
/*!40000 ALTER TABLE `ololousers` DISABLE KEYS */;
INSERT INTO `ololousers` VALUES (1,'ice-haron@rambler.ru','Ice_Haron','Ice_Haron','c91ffe86fa9c1be66e92552288e4829c',NULL,11015400,'{\"steamBindingBroken\":{\"76561197991665605\":1401710402},\"steamBindingSet\":{\"76561197991665605\":1401711466}}',0,0),(2,'ololosh@ololo.com','ololosh','Trololosh','a619d974658f3e749b2d88b215baea46','76561197991665605',32550,'{\"created\":1398776655,\"changedPw\":[1401368613,1401368662],\"steamBindingSet\":{\"76561197991665605\":1401802462},\"steamBindingBroken\":{\"76561197991665605\":1401802448}}',1,0),(3,'krein91@mail.ru','Krein',NULL,'d0a1630705ca1f5382606c1380b66e23','76561198071516961',33600,'{\"created\":1401459471,\"steamBindingBroken\":{\"76561198071516961\":1401802754},\"steamBindingSet\":{\"76561198071516961\":1401802780}}',1,10500),(4,'salat@moloko','salat',NULL,'16a15b1533173a4de94b63864fadd1b6',NULL,30000,'{\"created\":1401463584}',1,0),(5,'qponar@rambler.ru','pahom',NULL,'7649bcb6f84d004d4b923faed31be9da',NULL,30000,'{\"created\":1401883236}',1,0),(6,'dmc2391@gmail.com','DeathMetal',NULL,'1f008ed5d54ae35cdd6c1e8263ac2dcb','76561198031256083',32450,'{\"created\":1401883638,\"steamBindingSet\":{\"76561198031256083\":1401886809}}',1,0),(16,'ya@genk0.ru','genk0',NULL,'23bab067b365cc379d474f24caa4cb44',NULL,31450,'{\"created\":1401891149}',1,0),(18,'qponap@yahoo.com','SHiKaRo',NULL,'9ecaf4f02f8ed3a600d15a38cbb9b78c','76561198056801918',32450,'{\"created\":1403095877,\"steamBindingSet\":{\"76561198056801918\":1403095917}}',1,0);
/*!40000 ALTER TABLE `ololousers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_achievs`
--

DROP TABLE IF EXISTS `user_achievs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_achievs` (
  `user` int(10) unsigned NOT NULL,
  `achievement` int(10) unsigned NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user`,`achievement`,`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_achievs`
--

LOCK TABLES `user_achievs` WRITE;
/*!40000 ALTER TABLE `user_achievs` DISABLE KEYS */;
INSERT INTO `user_achievs` VALUES (1,0,1398776655),(1,1,1401970335),(1,2,1401970335),(1,3,1401967557),(1,4,1402041957),(1,5,1401970335),(1,9,1402404726),(1,10,1401970335),(1,13,1401970335),(1,21,1401970335),(1,30,1401970335),(1,42,1401970335),(1,50,1401970335),(1,70,1401970335),(1,100500,1401970335),(2,0,1398776655),(2,1,1401802429),(2,2,1401802448),(2,5,1401969627),(2,10,1401969627),(2,12,1403154434),(2,13,1401969627),(2,21,1401969627),(3,0,1401459471),(3,1,1401802722),(3,2,1401802754),(3,5,1401975559),(3,10,1401975559),(3,11,1403095342),(3,12,1403095202),(3,13,1401975559),(3,21,1401975559),(4,0,1401463584),(4,9,1402404726),(5,0,1401883236),(6,0,1401883638),(6,1,1401886809),(6,5,1402041985),(6,10,1402041985),(6,13,1402041985),(6,21,1402041985),(16,0,1401891149),(16,5,1401974810),(16,10,1401974810),(16,13,1401974810),(16,21,1401974810),(18,0,1403095877),(18,1,1403095917),(18,5,1403095891),(18,10,1403095891),(18,13,1403095891),(18,21,1403095891);
/*!40000 ALTER TABLE `user_achievs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-06-20 16:25:36
