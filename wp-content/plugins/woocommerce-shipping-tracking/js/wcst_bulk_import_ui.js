 function wcst_importing_data_transition_in()
 {
	 jQuery('#progress-bar-container').fadeIn();
	 jQuery('#instruction-box, #star-import-button').fadeOut();
 }
 function wcst_importing_data_transition_out()
 {
	 jQuery('#notice-box').append("<p>100% done</p>");
	 jQuery('#import-again-button').fadeIn();
 }
 function wcst_appent_status_text(text)
 {
	 jQuery('#notice-box').append("<p>"+text+"</p>");
 }
 function wcst_set_progress_bar_level(perc)
 {
	 jQuery( "#progress-bar" ).animate({'width':perc+"%"});
 }
 