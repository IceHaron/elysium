var servers = new Array('kernel', 'backtrack', 'gentoo');

$(document).ready(function(){
	var pid = $("#w2v_vote_banner").attr("rel");
	$("#w2v_vote_banner").html('<a href="http://www.want2vote.com/project/id'+pid+'/" target="_blank" title="www.want2vote.com!"><img src="http://www.want2vote.com/_status/pictures/status_votebanner/'+pid+'.jpg" alt="want2vote" width="88" height="31"></a>');
});

$(document).ready(function(){

	$("#back-top").hide(); // Скрываем банлист

	// Пингуем серваки раз в пять минут и один раз при загрузке страницы
	// for (i in servers) {
	// 	server = servers[i];
	// 	pingServer(server);
	// }
	pingServer('kernel');
	$('.backtrack .status').css('color','darkred').text('Timeout (5s)');
	$('.gentoo .status').css('color','darkred').text('Timeout (5s)');

	// Если мы залогинены, проверяем неполученные ачивки сразу и запускаем проверку каждые 5 минут
	if ($('.logged').length != 0) {
		achCheck();
		
		setInterval(function() {
			achCheck();
		}, 300000);
	}

	// Проверяем онлайн при загрузке страницы и раз в 5 минут
	onlineCheck('kernel');
	
	setInterval(function() {
		pingServer('kernel');
		onlineCheck('kernel');
	}, 300000);

	$('#header .row-2 ul li').each(function() {
		var base = $('#header').offset().left;
		var position = base - $(this).offset().left;
		$(this).css('background-position', position);
	});

	// Если у нас есть цветной ник, то выделяем цвет
	if ($('input[name="nameColor"]').length != 0) {
		var color = $('input[name="nameColor"]').val();
		$('.name.color[data-color="' + color + '"]').addClass('active');
	}

	// Парсим сразу префикс
	parsePrefix();

	//В ЛК и списке достижений нужно установить высоту стопок ачивок чтобы они не пересекались, не знаю, как сделать это средствами html
	// $('.achievementStack').each(function() {
	// 	var inner = $(this).children().eq(0);
	// 	var height = parseInt(inner.css('height').replace('px',''));
	// 	var margin = parseInt(inner.css('margin-top').replace('px',''));
	// 	$(this).css('height', height + margin + 4);
	// });

	// Хер его проссыт, зачем это, наследие
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

	// И это наследие
	$('.shadowed').each(function(){
		$(this).textDropShadow('shadow')
	});

	// При клике на кнопку банлиста, мы не по ссылке переходим, а отображаем банлист
	$('#banlist-button').click(function(e) {
		e.preventDefault();
		$.ajax({
			  type: 'GET'
			, url: '/ajax'
			// , async: false
			, data: {'mode': 'checkBL'}
			, dataType: 'json'
			, success: function(data) {
				$('#banlist table').html('<tr><th>Игрок</th><th>Причина</th><th>Забанивший</th><th width="125">Забанен от</th><th width="125">Забанен до</th></tr>');
				for (i in data) {
					var ban = data[i];
					$('#banlist table').append('<tr><td>' + ban.player + '</td><td>' + ban.reason + '</td><td>' + ban.admin + '</td><td>' + ban.ban + '</td><td>' + ban.unban + '</td></tr>');
				}
			}
		});
		$('#shadow').show();
		$('#banlist').show();
	});

	$('#refer').click(function() {
		$('#shadow').show();
		$('#referralForm').show();
	});

	$('.vkHead').click(function() {
		var mainClass = $('#vkSlider').attr('class');
		if (mainClass == 'closed') {
			$('#vkSlider').attr('class', 'opened').animate({left: '-=290px'}, 300);
			$('#vkArrow').css('transform', 'rotate(0deg)');
		} else if (mainClass == 'opened') {
			$('#vkSlider').attr('class', 'closed').animate({left: '+=290px'}, 300);
			$('#vkArrow').css('transform', 'rotate(180deg)');
		}
	});

	// При клике на тень, скрываем все всплывающие какашки
	$('#shadow').click(function() {
		$('#shadow').hide();
		$('.modal').hide();
	});

	// Смена пароля в ЛК
	$('#changePass').click(function() {
		var oldpw = $('input[name="oldpw"]').val();
		var newpw = $('input[name="newpw"]').val();
		var repw = $('input[name="repw"]').val();
		if (oldpw != '' && newpw != '' && newpw == repw)
			$('#changePw').submit();
		else alert("Неправильное подтверждение пароля!");
	});

	// При клике на поле с реферральной ссылкой, выделяем ее всю
	$('#refLink').click(function() {
		this.select();
	});

	// Проверяем входные данные при печати количества изюма
	$('#izumform input[name="want"]').keyup(function() {
		var clear = $(this).val().substr(0,7).replace(/\D+/g, '');
		$(this).val(clear);
	});
	$('#izumform input[name="want"]').change(function() {
		var clear = $(this).val().substr(0,7).replace(/\D+/g, '');
		if (parseInt(clear) < 100) clear = 100;
		$(this).val(clear);
		var rate = $('.rate').text();
		var discount = parseFloat($('input[name="izumDiscount"]:checked').attr('data-effect'));
		var cost = Math.ceil(clear / rate * (100 - discount)) / 100;
		$('.cost').text(cost);
		var bonus = 0;
		var percent = 0;
		if (parseInt(clear) >= 100000) {
			percent = Math.pow(2,(-500000 / clear)) * 3 / 10;
			bonus = Math.round(percent * clear);
			percent = Math.round(percent * 100);
		}
		if (bonus == 0) $('.bonus').hide();
		else {
			$('.bonus').show();
			$('.bonusIzum').text(bonus);
			$('.bonusPercent').text(percent);
		}
	});

	$('.moreAboutBonuses').click(function() {
		$('#shadow').show();
		$('#aboutBonuses').show();
	});

	$('#calculateIzum').click(function() {
		$('#izumform input[name="want"]').trigger('change');
	});

	$('.diamond').dblclick(function() {
		$.ajax({
			  type: 'GET'
			, url: '/ajax'
			, data: {'mode': 'stealDiamond'}
			, success: function(data) {
				alert(data);
				$('.diamond').remove();
				$('.herobrine').attr('src','images/zomb_nodiamond.png');
			}
		});
	});

	$('ul.tabs li').click(function() {
		$('ul.tabs li.active').removeClass('active');
		var showing = $(this).attr('class');
		$(this).toggleClass('active');
		$('.tabContent').hide();
		$('.' + showing + ':not(li)').show();
	});

	$('ul.tabs li:first').trigger('click');

	$('#changePrefix').click(function() {
		$('#prefixConstructor').toggle();
	});

	// При клике по цвету, добавляем его код в конец инпута префикса
	$('.prefix.color').click(function() {
		var prefix = $('input[name="prefix"]').val();
		var color = $(this).attr('data-color');
		if (prefix.length < 42) $('input[name="prefix"]').val(prefix + '&' + color).trigger('keyup').focus();
	});

	$('.name.color').click(function() {
		$('.name.color.active').removeClass('active');
		$(this).addClass('active');
		var color = $(this).attr('data-color');
		$('input[name="nameColor"]').val(color);
		parsePrefix();
		$('#savePrefix').show();
	});

	// При изменени префикса, парсим его
	$('input[name="prefix"]').keyup(function() {
		parsePrefix();
		$('#savePrefix').show();
	});


});

/**
* 
* Пингуем серваки
* 
**/
function pingServer(server) {
	$.ajax({
		  type: 'GET'
		, url: '/ajax'
		// , async: false
		, data: {'mode': 'pingServer', 'server': server}
		, dataType: 'json'
		, success: function(data) {
			if (data) $('.' + server + ' .status').css('color','darkgreen').text(data.players + '/' + data.limit);
			else $('.' + server + ' .status').css('color','darkred').text('Timeout (5s)');
		}
		, error: function() {
			$('.' + server + ' .status').css('color','darkred').text('Timeout (5s)');
		}
	});
}

/**
* 
* Список неполученных ачивок
* 
**/
function achCheck() {
	$.ajax({
		  type: 'GET'
		, url: '/ajax'
		, data: {'mode': 'achCheck'}
		, dataType: 'json'
		, success: function(data) {
				for (i in data) {
					showAchievement(data[i]);
				}
			}
	});
}

/**
* 
* Список онлайн игроков
* 
**/
function onlineCheck(server) {
	$.ajax({
		  type: 'GET'
		, url: '/ajax'
		, data: {'mode': 'onlineCheck'}
		, dataType: 'json'
		, success: function(data) {
				var hidden = 0;
				$('.' + server + ' .online').empty();
				for (time in data) {
					$('.' + server + ' .online').append('<div class="time">' + time + '</div>');
					for (group in data[time]) {
						var playerlist = '';
						for (i in data[time][group]) {
							var player = data[time][group][i];
							if (player.search(/\[СКРЫТ\]/) != -1) hidden++;
							else {
								playerlist += '<div class="player">' + player + '</div>';
							}
						}
						if (playerlist != '') {
							$('.' + server + ' .online').append('<div class="group">' + group + '</div>');
							$('.' + server + ' .online').append(playerlist);
						}
					}
				}
				if (hidden != 0) $('.' + server + ' .online').append('<div class="hidden">+ Скрытых: ' + hidden + '</div>');
				if ($('.online .player').length == 0) $('.online .group').hide();
			}
		, error: function() {
			$('.' + server + ' .online').html('Произошла какая-то ошибка при загрузке списка игроков, такое бывает.');
		}
	});
}

/**
* 
* Отображение всплывающей ачивки
* 
**/
function showAchievement(id) {
	$.ajax({
		  type: 'GET'
		, url: '/ajax'
		, data: {'mode': 'getAchHtml', 'id': id}
		, success: function(data) {
				$('#popupAch').append(data);
				$('#achList').prepend(data);
				$('#achList .achievement').eq(0).removeAttr('id');
				$('#ach-' + id).click(function() {$(this).remove()});
				setTimeout(function() {$('#ach-' + id).remove()}, 15000)
		}
	});
}

/**
* 
* Парсинг префикса
* 
**/
function parsePrefix(id) {
	var prefix = $('input[name="prefix"]').val();
	var nameColor = $('input[name="nameColor"]').length ? $('input[name="nameColor"]').val() : 'f';
	if (prefix) {
		var flags = prefix.match(/\&[0-9a-f]/g);
		if (flags != null) count = flags.length;
		else count = 0;
		count = count * 2 + 14;
		var parsing = prefix.substr(0,count);
		$('input[name="prefix"]').val(parsing);
		parsing = '<span style="color: white">[' + parsing + '</span><span style="color: white">]';
	} else {
		var parsing = $('.groupPrefix').text();
	}

	parsing += '&' + nameColor;

	var nick = $('.mcnick').text();
	parsing = parsing.replace(/\&0/g, '</span><span style="color: #000">');
	parsing = parsing.replace(/\&1/g, '</span><span style="color: #00a">');
	parsing = parsing.replace(/\&2/g, '</span><span style="color: #0a0">');
	parsing = parsing.replace(/\&3/g, '</span><span style="color: #0aa">');
	parsing = parsing.replace(/\&4/g, '</span><span style="color: #a00">');
	parsing = parsing.replace(/\&5/g, '</span><span style="color: #a0a">');
	parsing = parsing.replace(/\&6/g, '</span><span style="color: #fa0">');
	parsing = parsing.replace(/\&7/g, '</span><span style="color: #aaa">');
	parsing = parsing.replace(/\&8/g, '</span><span style="color: #555">');
	parsing = parsing.replace(/\&9/g, '</span><span style="color: #55f">');
	parsing = parsing.replace(/\&a/g, '</span><span style="color: #5f5">');
	parsing = parsing.replace(/\&b/g, '</span><span style="color: #5ff">');
	parsing = parsing.replace(/\&c/g, '</span><span style="color: #f55">');
	parsing = parsing.replace(/\&d/g, '</span><span style="color: #FE54FE">');
	parsing = parsing.replace(/\&e/g, '</span><span style="color: #ff5">');
	parsing = parsing.replace(/\&f/g, '</span><span style="color: #fff">');
	parsing = parsing.replace(/\&r/g, '</span><span style="color: #fff">');
	var parsed = parsing + ' ' + nick + '</span>&nbsp;&nbsp;&nbsp;';
	$('#parsedPrefix').children('div').html(parsed + parsed);
}