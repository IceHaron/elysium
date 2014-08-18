<?php
/**
* 
* Невероятно сложный, просто огроменный, гигантский контроллер новостей
* 
**/

if (!isset($_GET['page'])) $page = 1;
else $page = (int)$_GET['page'];

if ($page <= 0) exit('Вы что, пытаетесь нас затралиравать как лалак?');

$p = $db->query("SELECT ceil(count(*)/5) AS `pages` FROM `news`;");
$pages = $p[0]['pages'];
$pager = '';
for ($i = 1; $i <= $pages; $i++) {
	if ($i != $page) $pager .= '<a href="?page=' . $i . '"><div class="page">' . $i . '</div></a>';
	else $pager .= '<div class="page">' . $i . '</div>';
}
$start = ($page - 1) * 5;
$end = $page * 5;
$news = $db->query("SELECT `id`, `title`, `intro`, unix_timestamp(`date`) as `ts` FROM `news` ORDER BY `ts` DESC LIMIT $start,$end;");

if (count($news) == 0) exit('Нет, ну вы точно пытаетесь нас затралиравать');
