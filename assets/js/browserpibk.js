$(document).ready(function(){
    $('.datepicker').datepicker({
        showOn: 'both',
        dateFormat: 'dd/mm/yy',
        buttonImage: './calendar.gif',
        buttonImageOnly: true,
        changeMonth: true,
        firstDay: 1,
        dayNamesMin: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
        buttonText: ''
    });
});