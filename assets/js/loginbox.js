$(document).ready(function(){
	$('a.loginbtn').click(function(e){
		e.stopPropagation();
		$(document).find('div.loginfrm').slideToggle();
	});
});