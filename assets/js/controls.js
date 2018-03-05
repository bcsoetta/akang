

var datepickerOptions = {
		showOn: 'both',
		dateFormat: 'dd/mm/yy',
		buttonText: ' ',
		buttonImage: 'http://192.168.146.248/akang/assets/img/calendar.gif',
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		firstDay: 1,
		dayNamesMin: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
		beforeShow: function(e) {
			if ($(e).attr('readonly')) {
				return false;
			}
		}
	};

$(document).ready(function(){
	
	$('.datepicker').datepicker(datepickerOptions);
	$('.ui-datepicker-trigger').removeAttr('alt');
	$('#preview').click(function(){
		$(this).fadeToggle('slow');
	});	
	
});