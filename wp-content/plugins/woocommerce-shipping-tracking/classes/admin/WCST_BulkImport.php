<?php 
class WCST_BulkImport
{
	public function __construct()
	{
	}
	public function render_page()
	{
		wp_enqueue_style( 'wcst-common', WCST_PLUGIN_PATH.'/css/wcst_common.css');
		wp_enqueue_style( 'wcst-admin', WCST_PLUGIN_PATH.'/css/wcst_options.css');
		wp_enqueue_style( 'wcst-cst-bulk-import', WCST_PLUGIN_PATH.'/css/wcst_bulk_import.css');

		wp_enqueue_script('wcst-csv', WCST_PLUGIN_PATH.'/js/csv/jquery.csv-0.71.min.js', array('jquery'));	 
		wp_enqueue_script('wcst-bulk-import', WCST_PLUGIN_PATH.'/js/wcst_bulk_import.js', array('jquery'));	 
		wp_enqueue_script('wcst-bulk-import-ui', WCST_PLUGIN_PATH.'/js/wcst_bulk_import_ui.js', array('jquery'));	 
		?>
		<div class="wrap white-box">
		
			<h1 ><?php _e('Import tracking info', 'woocommerce-shipping-tracking');?></h1>
			
			<div id="instruction-box">
				<p><?php _e('Select a .csv file that has the following columns:','woocommerce-shipping-tracking');?></p>
				<ul id="field_list">
						<li>order_id</li>
						<li>order_status <span class="normal">(<?php _e('Leave empty to leave order status unchanghed, otherwise use the follwing codes to set order status: ', 'woocommerce-shipping-tracking');  
											$counter = 0;
											foreach(wc_get_order_statuses() as $code => $status):
												if($counter > 0)
													echo ", ";
												echo "<strong>".$code."</strong>";
												$counter++;
											endforeach; ?>)</li>
						<li>force_email_notification <span class="normal">(<?php _e('Leave empty for no notification, otherwise choose one of the following values to resend a notification email: <strong>send_email_new_order</strong>, <strong>send_email_cancelled_order</strong>, <strong>send_email_customer_processing_order</strong>, <strong>send_email_customer_completed_order</strong>, <strong>send_email_customer_refunded_order</strong>, <strong>send_email_customer_invoice</strong>', 'woocommerce-shipping-tracking'); ?>)</li>
						<li>dispatch_date <span class="normal">(<?php _e('In case of multiple tracking code, can be spacified multiple dispatch dates using the <strong>|</strong> character to separate dates: <strong>dispatch_date1|dispatch_date2</strong>', 'woocommerce-shipping-tracking'); ?>)</span></li>
						<li>custom_text <span class="normal">(<?php _e('<strong>REMOVE ALL , CHARACTERS</strong> eventually present in this field otherwise import <strong>WILL FAIL</strong>. In case of multiple tracking code, can be spacified multiple text using the <strong>|</strong> character to separate texts: <strong>text1|text2</strong>', 'woocommerce-shipping-tracking'); ?>)</span></li>
						<li>tracking_info <span class="normal">(<?php _e('this column must have the following format: <strong>company_id:tracking_code</strong>. In case of multiple codes use the <strong>|</strong> to separate the info: <strong>company_id:tracking_code|company_id2:tracking_code2</strong>. All compani ids are reported in the <strong>Shipping companies</strong> section', 'woocommerce-shipping-tracking'); ?>)</span></li>
				</ul>
				<input type="file" name="csv_file" id="csv_file" accept=".csv"></input>
			</div>
			<div id="progress-bar-container">
				<div id="progress-bar-background"><div id="progress-bar"></div></div>
				<div id="notice-box"></div>				
			</div>						
			<div id="button-container">
				<input type="submit" value="<?php _e('Start import', 'woocommerce-shipping-tracking');?>" class="button-primary" id="star-import-button" name="Submit">
		
				<input type="submit" value="<?php _e('Import another csv', 'woocommerce-shipping-tracking');?>" class="button-primary" id="import-again-button" name="Submit">
			</div>
		
		<?php
	}
}
?>