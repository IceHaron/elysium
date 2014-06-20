$(document).ready(function(){

	$("#back-top").hide(); // Скрываем банлист

	// Если мы залогинены, проверяем неполученные ачивки сразу и запускаем проверку каждые 5 минут
	if ($('.logged').length != 0) {
		achCheck();
		
		setInterval(function() {
			achCheck();
		}, 300000);
	}

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
	$('#banlist').click(function(e) {
		e.preventDefault();
		$('#shadow').show();
		$('#basic-modal-content').show();
	});

	// При клике на тень, скрываем все всплывающие какашки
	$('#shadow').click(function() {
		$('#shadow').hide();
		$('#basic-modal-content').hide();
	});

	// Смена пароля в ЛК
	$('#changePass').click(function() {
		var oldpw = $('input[name="oldpw"]').val();
		var newpw = $('input[name="newpw"]').val();
		var repw = $('input[name="repw"]').val();
		if (oldpw != '' && newpw != '' && newpw == repw)
			$('#changePw').submit();
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

});

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