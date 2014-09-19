var servers = new Array('kernel', 'backtrack', 'gentoo');

$(document).ready(function(){

	$("#back-top").hide(); // Скрываем банлист

	// Пингуем серваки раз в пять минут и один раз при загрузке страницы
	// for (i in servers) {
	// 	server = servers[i];
	// 	pingServer(server);
	// }
	pingServer('kernel');
	$('.backtrack-status').css('color','darkred').text('Timeout (5s)');
	$('.gentoo-status').css('color','darkred').text('Timeout (5s)');

	// Если мы залогинены, проверяем неполученные ачивки сразу и запускаем проверку каждые 5 минут
	if ($('.logged').length != 0) {
		achCheck();
		
		setInterval(function() {
			achCheck();
		}, 300000);
	}

	//В ЛК и списке достижений нужно установить высоту стопок ачивок чтобы они не пересекались, не знаю, как сделать это средствами html
	$('.achievementStack').each(function() {
		var inner = $(this).children().eq(0);
		var height = parseInt(inner.css('height').replace('px',''));
		var margin = parseInt(inner.css('margin-top').replace('px',''));
		$(this).css('height', height + margin + 4);
	});

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
		var clear = $(this).val().substr(0,5).replace(/\D+/g, '');
		$(this).val(clear);
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
			if (data) $('.' + server + '-status').css('color','darkgreen').text(data.players + '/' + data.limit);
			else $('.' + server + '-status').css('color','darkred').text('Timeout (5s)');
		}
		, error: function() {
			$('.' + server + '-status').css('color','darkred').text('Timeout (5s)');
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