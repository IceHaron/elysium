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

	$('.shadowed').each(function(){
		$(this).textDropShadow('shadow')
	});

	$('#banlist').click(function(e) {
		e.preventDefault();
		$('#shadow').show();
		$('#basic-modal-content').show();
	});

	$('#shadow').click(function() {
		$('#shadow').hide();
		$('#basic-modal-content').hide();
	});

	$('#changePass').click(function() {
		var oldpw = $('input[name="oldpw"]').val();
		var newpw = $('input[name="newpw"]').val();
		var repw = $('input[name="repw"]').val();
		if (oldpw != '' && newpw != '' && newpw == repw)
			$('#changePw').submit();
	});

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


});

setInterval(function() {
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
}, 300000);

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