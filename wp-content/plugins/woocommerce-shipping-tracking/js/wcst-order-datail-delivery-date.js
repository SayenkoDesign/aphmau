jQuery(document).ready(function()
{
	jQuery( ".wcst_input_date" ).pickadate({formatSubmit: wcst_date_format, format: wcst_date_format, selectYears:true, selectMonths:true,
														// Strings and translations
														monthsFull: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
														monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
														weekdaysFull: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
														weekdaysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
														

														// Buttons
														today: 'Today',
														clear: 'Clear',
														close: 'Close',

														// Accessibility labels
														labelMonthNext: 'Next month',
														labelMonthPrev: 'Previous month',
														labelMonthSelect: 'Select a month',
														labelYearSelect: 'Select a year'});
	jQuery( ".wcst_input_time" ).pickatime({formatSubmit: 'HH:i', format: 'HH:i'});
	
});

