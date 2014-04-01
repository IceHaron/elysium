<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<title>Elysium Game | Портал игровых серверов Minecraft</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="game, mine, minecraft, elysium, майнкрафт, майн, сервер, сервера, лучший" />
<link href="style.css" rel="stylesheet" type="text/css" />
<link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#back-top").hide();
		$(function () {
			$(window).scroll(function () {
				if ($(this).scrollTop() > 50) {
					$('#back-top').fadeIn();
				} else {
					$('#back-top').fadeOut();
				}
			});
			$('#back-top a').click(function () {
				$('body,html').animate({
					scrollTop: 0
				}, 800);
				return false;
			});
		});
	});
</script>
<link href='http://fonts.googleapis.com/css?family=Cuprum&subset=cyrillic,latin' rel='stylesheet' type='text/css'>
</link>
<!--[if lt IE 7]>
	<script type="text/javascript" src="js/ie_png.js"></script>
	<script type="text/javascript">
		 ie_png.fix('.png, #header .row-2 ul li a, #content, .list li');
	</script>
<![endif]-->
</head>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter16123105 = new Ya.Metrika({id:16123105, enableAll: true, webvisor:true});
        } catch(e) {}
    });
    
    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/16123105" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<?
include "template/status.php";
?>
<body id="page2">
<div class="tail-top">
	<div class="tail-bottom">
		<div class="body-bg">
			<!-- Шапка -->
			<div id="header">
				<div class="extra"><img src="images/header-img.png" alt="" /></div>
				<div class="row-1">
				</div>
				<div class="row-2">
					<ul>
						<li class="m1"><a href="index.php">Главная</a></li>
						<li class="m2"><a href="play.php">Играть</a></li>
						<li class="m3"><a href="http://elysium-game.ru/forum/">Форум</a></li>
						<li class="m2"><a href="rules.php">Правила</a></li>
						<li class="m2"><div id='basic-modal'>
			<a href='#' class='basic'>Банлист</a>
		</div></li>
						<li class="m2"><a href="donate.php">Донат</a></li>
						<li class="m2"><a href="maps.php">Карты</a></li>
						<li class="m2"><a href="http://elysium-game.ru/project.php">Проекты</a></li>
					</ul>
				</div>
				<div class="row-3"><img src="images/slogan.png" alt="" />
				</div>
			</div>