$(document).ready(function() {
	if ($('#newsAddForm, #newsEditForm').length > 0) {
		makeWhizzyWig("intro");
		makeWhizzyWig("text");
	}

	$('#newsCreate').click(function(e) {
		e.preventDefault();
		syncTextarea();
		$('#newsAddForm').submit();
	});

	$('#newsEdit').click(function(e) {
		e.preventDefault();
		syncTextarea();
		$('#newsEditForm').submit();
	});

	$('#usersEdit').click(function(e) {
		e.preventDefault();
		syncTextarea();
		$('#usersEditForm').submit();
	});

});
