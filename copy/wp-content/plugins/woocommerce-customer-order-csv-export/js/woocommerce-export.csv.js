jQuery(document).ready(function($) {

	$('div.export-target input.input-radio').live('click', function(){
		$('div.info').hide();
		if ($(this).is(':checked')) {
			$('div.info.' + $(this).attr('ID')).slideDown();
		}
	});
	
	$('div.export-style-orders input.input-radio').live('click', function(){
	  $('#export-submit').removeAttr('disabled');
	  if ($(this).is(':checked')) {
	    $('div.info.' + $(this).attr('ID')).slideDown();
	    if ($(this).val()=='csv-import-format-orders') {
  	    $('div.csv-import-format-orders').hide();
	    }
	    else {
  	    $('div.csv-import-format-orders').show();
	    }
	  }
	});
	
	$('div.export-style-clients input.input-radio').live('click', function(){
	  $('#export-submit').removeAttr('disabled');
	  if ($(this).is(':checked')) {
	    $('div.info.' + $(this).attr('ID')).slideDown();
	  }
	});
	
	$('input:checkbox').click(function() {
    var buttonsChecked = $('input:checkbox:checked');
    if (buttonsChecked.length) {
      $('#export-submit').removeAttr('disabled');
    }
    else {
      $('#export-submit').attr('disabled', 'disabled');
    }
  });
	
});

jQuery(document).ready(function($) {

	var dateNow = new Date();
  var currentMonth = dateNow.getMonth();
  var currentDate = dateNow.getDate();
  var currentYear = dateNow.getFullYear();


	var dates = $( "#datepicker-field-end, #datepicker-field-start" ).datepicker({
		defaultDate: "+1w",
		numberOfMonths: 3,
		dateFormat: "yy-mm-dd",
		maxDate: new Date(currentYear, currentMonth, currentDate),
		onSelect: function( selectedDate ) {
			var option = this.id == "datepicker-field-start" ? "minDate" : "maxDate",
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
			instance.settings.dateFormat ||
			$.datepicker._defaults.dateFormat,
			selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
});