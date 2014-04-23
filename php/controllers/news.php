<?php

$news = $db->query("SELECT `id`, `title`, `intro`, unix_timestamp(`date`) as `ts` FROM `news` ORDER BY `ts` DESC;");
