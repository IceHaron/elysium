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

});