jQuery(document).ready(function()
{
	jQuery(document).on('change',wcst_enable_bulk_import,wcst_check_if_to_show_advanced_bulk_import_options_box);
	wcst_check_if_to_show_advanced_bulk_import_options_box(null);
});
function wcst_check_if_to_show_advanced_bulk_import_options_box(event)
{
	if(jQuery('#wcst_enable_bulk_import').val() == 'yes')
		jQuery('#wcst_advanced_bulk_import_options').show();
	else
		jQuery('#wcst_advanced_bulk_import_options').hide();
}