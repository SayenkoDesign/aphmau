jQuery(document).ready(function()
{
	
});
function wcst_init(counter) //called from php file
{
	var max_fields      = 50; //maximum input boxes allowed
	var wrapper         = jQuery(".input_fields_wrap"); //Fields wrapper
	var add_button      = jQuery(".add_field_button"); //Add button ID
	var x = counter ; //initlal text box count
	
	//init
	wcst_setup_click_managment();
	wcst_manage_tracking_input_and_extra_tracking_options(null);
	
	jQuery(add_button).click(function(e)
	{ //on add input button click
		e.preventDefault();
		if(x < max_fields){ //max input box allowed
			x++; //text box increment
			
			jQuery(wrapper).append(getHtmlTemplate(x)); //add input box
			//wcst_setup_click_managment();
		}
	});
   
	jQuery(wrapper).on("click",".remove_field", function(e){ //user click on remove text
		e.preventDefault(); jQuery(this).parent('div').remove(); x--;
	});
}
function wcst_setup_click_managment()
{
	jQuery(document).on('click','.wcst_aftership_checkbox',wcst_manage_tracking_input_and_extra_tracking_options);						
	jQuery(document).on('click','.wcst_trackingmore_checkbox', wcst_manage_tracking_input_and_extra_tracking_options);						
	jQuery(document).on('click','.wcst_aftership_checkbox, .wcst_trackingmore_checkbox', wcst_tracking_services_mutually_exclusive_checkbox_managment);
	jQuery(document).on('click', '.wcst_disable_tracking_url', wcst_manage_tracking_input_and_extra_tracking_options);
}
function wcst_tracking_services_mutually_exclusive_checkbox_managment(event)
{
	if(jQuery(event.currentTarget).attr("class") == 'wcst_aftership_checkbox' && jQuery(event.currentTarget).prop('checked'))
		jQuery(event.currentTarget).parent().find('.wcst_trackingmore_checkbox').removeAttr('checked');
	else if(jQuery(event.currentTarget).attr("class") == 'wcst_trackingmore_checkbox' && jQuery(event.currentTarget).prop('checked'))
		jQuery(event.currentTarget).parent().find('.wcst_aftership_checkbox').removeAttr('checked');
		
}

function wcst_manage_tracking_input_and_extra_tracking_options(event)
{
	//Trackng input enable/disable option managment
	jQuery('.wcst_aftership_checkbox').each(function(index, elem)
	{
		var id = jQuery(elem).data('id');
		if(jQuery("#wcst_disable_tracking_url_checkbox_"+id).prop('checked') || jQuery("#wcst_aftership_checkbox_"+id).prop('checked') || jQuery("#wcst_trackingmore_checkbox_"+id).prop('checked'))
			jQuery('#wcst_tracking_url_input_'+id).prop('disabled', true);
		else 
			jQuery('#wcst_tracking_url_input_'+id).removeAttr('disabled');
			
	});
	
	//Hide/Show 3rd party tracking options
	jQuery('.wcst_disable_tracking_url').each(function(index, elem)
	{
		var id = jQuery(elem).data('id');
		if(jQuery(elem).prop('checked') )
		{
			jQuery("#wcst_extra_tracking_services_box_"+id).hide();
			//jQuery('#wcst_tracking_url_input_'+id).prop('disabled', true);
			jQuery('#wcst_aftership_checkbox_'+id+', #wcst_trackingmore_checkbox_'+id).removeAttr('checked');
		}
		else
		{
			jQuery("#wcst_extra_tracking_services_box_"+id).show();
			//jQuery('#wcst_tracking_url_input_'+id).removeAttr('disabled');
		}
	});
}