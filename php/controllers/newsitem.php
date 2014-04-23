<?php

$news = $db->query("SELECT `title`, `text`, unix_timestamp(`date`) as `date` FROM `news` WHERE `id` = " . intval($_GET['id']));
