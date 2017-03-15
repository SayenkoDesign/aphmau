<?php 

class WCST_AdminMenu
{
	var $aftership_url = "http://track.aftership.com/%s";
	var $trackingmore_url = "http://track.trackingmore.com/choose-en-%s.html";
	function __construct() {
	}

	public static function get_shipping_companies_list()
	{
		include WCST_PLUGIN_ABS_PATH.'included_companies/WCST_shipping_companies_list.php';
				
		ksort($shipping_companies);
		
		return $shipping_companies;
	}

	public function render_page() 
	{
		/* $tab_to_render = isset($_REQUEST['tab']) ? $_REQUEST['tab']:'general';
		
		if($tab_to_render == 'add-custom-companies')
			$this->render_add_custom_companies_tab();
		else if($tab_to_render == 'edit-messages')
			$this->render_edit_messages_tab();
		else if($tab_to_render == 'additional-checkout-fields')
			$this->render_delivery_date_time_tab();
		else if($tab_to_render == 'general-options')
			$this->render_general_options_tab();
		else
			$this->render_general_tab(); */
		
		$tab_to_render = isset($_REQUEST['page']) ? $_REQUEST['page']:'wcst-shipping-companies';
		
		if($tab_to_render == 'wcst-add-custom-shipping-company')
			$this->render_add_custom_companies_tab();
		else if($tab_to_render == 'wcst-edit-messages')
			$this->render_edit_messages_tab();
		else if($tab_to_render == 'wcst-delivery-extra-fields')
			$this->render_delivery_date_time_tab();
		else if($tab_to_render == 'wcst-general-options')
			$this->render_general_options_tab();
		else
			$this->render_general_tab();
	}
	public static function get_default_message()
	{
		$default_message =  '<h3>Your Order has been shipped via [shipping_company_name].</h3>';
		$default_message .=	'<strong>Tracking #</strong>[tracking_number]';
		$default_message .=	'<br/>';
		$default_message .=	'<a href="[url_track]" target="_blank" ><strong>CLICK HERE</strong></a> to track your shipment.<br/>';
		$default_message .=	'Dispatched on: [dispatch_date] <br/>';
		$default_message .=	'[custom_text] <br/>';
		return $default_message;
	}
	public static function get_default_message_additional_shippings()
	{
		$default_message =  '<br/>';
		$default_message .=  'Company name: [additional_shipping_company_name]<br/>';
		$default_message .=  '<strong>Tracking #</strong>[additional_shipping_tracking_number]';
		$default_message .=	'<br/>';
		$default_message .=	'[if_has_tracking_url]<a href="[additional_shipping_url_track]" target="_blank" ><strong>CLICK HERE</strong></a> to track your shipment.[if_has_tracking_url]<br/>';
		$default_message .=	'Dispatched on: [additional_dispatch_date] <br/>';
		$default_message .=	'[additional_custom_text]';
		return $default_message;
	}
	private function update_custom_companies()
	{
		$companies = array();
		if(isset($_REQUEST['wcst_custom_shipping_company']))
		{
			
			foreach($_REQUEST['wcst_custom_shipping_company'] as $company)
			{
				if(isset($company['name'])/*  && (isset($company['url']) || isset($company['enable_aftership']) || isset($company['enable_trackingmore'])) */ )
				{
					if(isset($company['disable_tracking_url']))
						array_push($companies, array("name"=> $company['name'], "url"=>"", "disable_tracking_url" => true,  "enable_aftership"=>false,"enable_trackingmore"=>false));
					else if(isset($company['enable_trackingmore']))
						array_push($companies, array("name"=> $company['name'], "url"=>$this->trackingmore_url, "disable_tracking_url" => false,"enable_aftership"=>false,"enable_trackingmore"=>true));
					else if(isset($company['enable_aftership']))
						array_push($companies, array("name"=> $company['name'], "url"=>$this->aftership_url, "disable_tracking_url" => false,"enable_aftership"=>true,"enable_trackingmore"=>false));
					else
						array_push($companies, array("name"=> $company['name'], "url"=> $company['url'], "disable_tracking_url" => false,  "enable_aftership"=>false,"enable_trackingmore"=>false));
				}
			}
			update_option( 'wcst_user_defined_companies', $companies );
		}
		else
		{
			delete_option( 'wcst_user_defined_companies');
		}
	}
	private function render_edit_messages_tab()
	{
		$options = new WCST_Option();
		$wpml = new WCST_Wpml();
		
		if(isset($_POST['wcst_template_messages']))
			$options->save_messages($_POST['wcst_template_messages']);
		//$messages = get_option( 'wcst_template_messages');
		$messages = $options->get_messages();
		
		$default_message = WCST_AdminMenu::get_default_message();
		$default_message_additional_shippings = WCST_AdminMenu::get_default_message_additional_shippings();
		
		
		$mail_message = (!isset($messages['wcst_mail_message']) || $messages['wcst_mail_message'] == "") ? $default_message:$messages['wcst_mail_message'];
		$mail_additional_snippet = (!isset($messages['wcst_mail_message_additional_shippings']) || $messages['wcst_mail_message_additional_shippings'] == "") ? $default_message_additional_shippings:$messages['wcst_mail_message_additional_shippings'];
	
	
		$order_details_page_message =  (!isset($messages['wcst_order_details_page_message']) || $messages['wcst_order_details_page_message'] == "" )? $default_message:$messages['wcst_order_details_page_message'];
		$order_additional_snippet = (!isset($messages['wcst_order_details_page_additional_shippings']) || $messages['wcst_order_details_page_additional_shippings'] == "") ? $default_message_additional_shippings:$messages['wcst_order_details_page_additional_shippings'];
	
		wp_enqueue_style( 'wcst-common', WCST_PLUGIN_PATH.'/css/wcst_common.css');
		wp_enqueue_style( 'wcst-admin', WCST_PLUGIN_PATH.'/css/wcst_options.css');
		ob_start();
		?>
		<!-- <div id="icon-themes" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper">
		<a class='nav-tab ' href='?page=woocommerce-shipping-tracking&tab=general'>Shipping Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=add-custom-companies'>Add Custom Companies</a>
		<a class='nav-tab nav-tab-active' href='?page=woocommerce-shipping-tracking&tab=edit-messages'>Edit Messages</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=additional-checkout-fields'>Delivery date e time fields</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=general-options'>General</a>
		</h2> -->
		<div class="wrap white-box">
		<?php if($wpml->is_wpml_active()):?>
			<small class="wcst_notice"><strong><?php _e('NOTE:', 'woocommerce-customers-manager');?></strong> <?php _e('WPML Detected! to translate following texts simply select the language you desire from the upper WPML language selector, edit texts and save!', 'woocommerce-customers-manager');?> </small>
		<?php endif; ?>
		<h2><?php _e('Edit email and "Order details" page messages', 'woocommerce-shipping-tracking');?></h2>
		<!--<form action="options.php" method="post" >-->
		<form action="" method="post" >
		<?php //settings_fields('wcst_template_messages_group'); ?> 
				<div class="input_fields_wrap">
				<p>
				<?php _e('For main shipping company use the following shortocodes <strong>[shipping_company_name]</strong>, <strong>[tracking_number]</strong>, <strong>[custom_text]</strong>, <strong>[dispatch_date]</strong> and <strong>[url_track]</strong> to display directly into the messages the Shipping company name, the tracking number and the tracking url.', 'woocommerce-shipping-tracking');?>
				<br/>
				<?php _e('For additional shippings, use following shorcodes <strong>[additional_shipping_company_name]</strong>, <strong>[additional_shipping_tracking_number]</strong>, <strong>[additional_custom_text]</strong>, <strong>[additional_dispatch_date]</strong> and <strong>[additional_shipping_url_track]</strong>', 'woocommerce-shipping-tracking');?>
				<br/><br/>
				<strong><?php _e('NOTE', 'woocommerce-shipping-tracking');?></strong> <br/>
				<?php _e("If you are using a <strong>Custom company</strong> for which you have <strong>Disabled the tracking url</strong>, use the conditional <strong>[if_has_tracking_url] [/if_has_tracking_url]</strong> shortcode to wrap the message part you won't to display in case there is no tracking ur. This is useful when generating auotomatic tracking links.", 'woocommerce-shipping-tracking');?>
				<br>
				<?php _e("Exmple:", 'woocommerce-shipping-tracking');?>
				<pre>
				Custom message
				[if_has_tracking_url]&lt;a href="[additional_shipping_url_track]" target="_blank"&gt;&lt;strong&gt;CLICK HERE&lt;/strong&gt;&lt;/a&gt; to track your shipment.[/if_has_tracking_url]
				</pre>
				<?php _e("In this way the tracking link will not be generated if the current company has not any tracking url associated.", 'woocommerce-shipping-tracking');?>
				</p>
				<h2 class="wcst_sub_titles" style="margin-bottom:15px;" ><?php _e('Email', 'woocommerce-shipping-tracking');?></h2>
					<label><?php _e('Email message', 'woocommerce-shipping-tracking');?></label>
					<!--<textarea rows="8" cols="100" name="wcst_template_messages[wcst_mail_message]"><?php echo $mail_message;?></textarea>-->
					<?php wp_editor($mail_message, "wcst_mail_message", array("textarea_name" => "wcst_template_messages[wcst_mail_message]", 'media_buttons' => false, 'teeny'=> true) ) ?>
					
					<label class="wcst_option_label"><?php _e('Additional email message snippet messagge (In case of one or more additional shippings. Will be rendered one per additional shippings).', 'woocommerce-shipping-tracking');?></label>
					<!-- <textarea rows="8" cols="100" name="wcst_template_messages[wcst_mail_message_additional_shippings]"><?php echo $mail_additional_snippet;?></textarea> -->
					<?php wp_editor($mail_additional_snippet, "wcst_mail_message_additional_shippings", array("textarea_name" => "wcst_template_messages[wcst_mail_message_additional_shippings]", 'media_buttons' => false) ) ?>
					
					<label class="wcst_option_label"><?php _e('Email message preview', 'woocommerce-shipping-tracking');?></label>
					<div class="preview_box"><?php echo nl2br($mail_message)."<br><br>".nl2br($mail_additional_snippet);?></div>
				
				<h2 class="wcst_sub_titles" style="margin-bottom:15px;"><?php _e('Order details page', 'woocommerce-shipping-tracking');?></h2>				
					<label><?php _e('Order detail page message', 'woocommerce-shipping-tracking');?></label>
					<!--<textarea rows="8" cols="100"  name="wcst_template_messages[wcst_order_details_page_message]"><?php echo $order_details_page_message;?></textarea> -->
					<?php wp_editor($order_details_page_message, "wcst_order_details_page_message", array("textarea_name" => "wcst_template_messages[wcst_order_details_page_message]", 'media_buttons' => false) ) ?>
					
					<label class="wcst_option_label"><?php _e('Additional order detail page snippet messagge (In case of one or more additional shippings. Will be rendered one per additional shippings)', 'woocommerce-shipping-tracking');?></label>
					<!-- <textarea rows="8" cols="100" name="wcst_template_messages[wcst_order_details_page_additional_shippings]"><?php echo $order_additional_snippet;?></textarea> -->
					<?php wp_editor($order_additional_snippet, "wcst_order_details_page_additional_shippings", array("textarea_name" => "wcst_template_messages[wcst_order_details_page_additional_shippings]", 'media_buttons' => false) ) ?>
					
					
					<label class="wcst_option_label"><?php _e('Order detail page message preview', 'woocommerce-shipping-tracking');?></label>
					<div class="preview_box"><?php echo nl2br($order_details_page_message)."<br><br>".nl2br($order_additional_snippet);?></div>
				</div>
				<p class="submit">
					<input type="submit" value="Save Changes" class="button-primary" name="Submit">
				</p>
		</form>
		</div>
		<?php
		echo ob_get_clean();
	}
	private function render_add_custom_companies_tab()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
			$this->update_custom_companies();
		
		$custom_companies = get_option( 'wcst_user_defined_companies');
		$counter  = 0;
		
		wp_enqueue_script('wcst-custom-companies', WCST_PLUGIN_PATH.'/js/wcst-admin-custom-companies.js' ,array('jquery'));
		
		wp_enqueue_style( 'wcst-common', WCST_PLUGIN_PATH.'/css/wcst_common.css');
		wp_enqueue_style( 'wcst-admin', WCST_PLUGIN_PATH.'/css/wcst_options.css');
		
		ob_start();
		?>
		<!--<div id="icon-themes" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper">
		<a class='nav-tab ' href='?page=woocommerce-shipping-tracking&tab=general'>Shipping Companies</a>
		<a class='nav-tab nav-tab-active' href='?page=woocommerce-shipping-tracking&tab=add-custom-companies'>Add Custom Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=edit-messages'>Edit Messages</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=additional-checkout-fields'>Delivery date e time fields</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=general-options'>General</a>
		</h2> -->
		<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') echo '<div id="message" class="updated"><p>' . __('Saved successfully.', 'woocommerce-shipping-tracking') . '</p></div>'; ?>
		<div class="wrap white-box">
		
			<h2><?php _e('Custom defined shipping companies', 'woocommerce-shipping-tracking');?></h2>
			<h3><b><?php _e('Add or remove custom shipping companies:', 'woocommerce-shipping-tracking');?></b></h3>
			<p><b><?php _e('NOTE:', 'woocommerce-shipping-tracking');?></b>
			<?php _e('You can create a special URL using the <b>%s</b> string in the address. For example: <i>"http://www.shipping-company.com/?tacking-code:%s"</i><br/>In this way the WCST plugin will include the tracking code directly in the url.', 'woocommerce-shipping-tracking');?>
			<p>
			<br>
			<form method="post" >
				<div class="input_fields_wrap">
				<button class="add_field_button button-primary"><?php _e('Add one more Company', 'woocommerce-shipping-tracking');?></button>
				<?php if($custom_companies && is_array($custom_companies)):
						foreach($custom_companies as $company): 
						$company['enable_aftership'] = isset($company['enable_aftership']) ? $company['enable_aftership'] : false;
						$company['enable_trackingmore'] = isset($company['enable_trackingmore']) ? $company['enable_trackingmore'] : false;
						?>
						<div class="input_box" >
							<label><?php _e('Shipping Company Name:', 'woocommerce-shipping-tracking'); ?> </label>
							<input type="text" value="<?php echo $company['name']; ?>" name="wcst_custom_shipping_company[<?php echo $counter ?>][name]" placeholder="ex. DHL, UPS, ..." required></input>
							<br/>
							<label class="wcst_label"><?php _e('Set a shipping Company Tracking URL...:', 'woocommerce-shipping-tracking'); ?> </label>
							<input  class="wcst_tracking_url_input" id="wcst_tracking_url_input_<?php echo $counter ?>" value="<?php echo $company['url']; ?>"  type="text" size="80" name="wcst_custom_shipping_company[<?php echo $counter ?>][url]" placeholder="http://www.ups.com?tracking=%s" required></input>
							<button class="remove_field button-secondary"><?php _e('Remove company', 'woocommerce-shipping-tracking');?></button>
							
							<h3><label class="wcst_label"><?php _e('Extra options', 'woocommerce-shipping-tracking'); ?> </label></h3>
							<label class="wcst_label"><?php _e('Disable the tracking url', 'woocommerce-shipping-tracking'); ?> </label>
							<p><?php _e('The plugin will not generate any tracking url for this company.', 'woocommerce-shipping-tracking'); ?></p>
							<input class="wcst_disable_tracking_url" id="wcst_disable_tracking_url_checkbox_<?php echo $counter ?>" type="checkbox" value="true" data-id="<?php echo $counter ?>" name="wcst_custom_shipping_company[<?php echo $counter ?>][disable_tracking_url]" <?php if($company['disable_tracking_url']) echo 'checked="checked"';?>><?php _e('Disable tracking url', 'woocommerce-shipping-tracking'); ?></input><br/>
							
							<div  id="wcst_extra_tracking_services_box_<?php echo $counter ?>">
								<label class="wcst_label"><?php _e('Use a 3rd party tracking service to track the shipping', 'woocommerce-shipping-tracking'); ?> </label>
								<input class="wcst_aftership_checkbox" id="wcst_aftership_checkbox_<?php echo $counter ?>" type="checkbox" value="true" data-id="<?php echo $counter ?>" name="wcst_custom_shipping_company[<?php echo $counter ?>][enable_aftership]" <?php if($company['enable_aftership']) echo 'checked="checked"';?>><?php _e('Use Aftership service to track order', 'woocommerce-shipping-tracking'); ?></input><br/>
								<input class="wcst_trackingmore_checkbox" id="wcst_trackingmore_checkbox_<?php echo $counter ?>" type="checkbox" value="true" data-id="<?php echo $counter ?>" name="wcst_custom_shipping_company[<?php echo $counter ?>][enable_trackingmore]" <?php if($company['enable_trackingmore']) echo 'checked="checked"';?>><?php _e('Use TrackingMore service to track order', 'woocommerce-shipping-tracking'); ?></input>
							</div>
						</div>
				<?php $counter++; endforeach; else: ?>
					<div class="input_box">
						<label><?php _e('Shipping Company Name:', 'woocommerce-shipping-tracking'); ?> </label>
						<input type="text" name="wcst_custom_shipping_company[0][name]" placeholder="ex. DHL, UPS, ..." required></input>
						<br/>
						<label class="wcst_label"><?php _e('Shipping Company Tracking URL:', 'woocommerce-shipping-tracking'); ?> </label>
						<input class="wcst_tracking_url_input" id="wcst_tracking_url_input_0" type="text" size="80" name="wcst_custom_shipping_company[0][url]" placeholder="http://www.ups.com?tracking=%s" required></input>
						<button class="remove_field button-secondary"><?php _e('Remove field', 'woocommerce-shipping-tracking');?></button>
						
						<h3><label class="wcst_label"><?php _e('Extra options', 'woocommerce-shipping-tracking'); ?> </label></h3>
							<label class="wcst_label"><?php _e('Disable the tracking url', 'woocommerce-shipping-tracking'); ?> </label>
						<span class="wcst_disable_tracking_url_info"><?php _e('The plugin will not generate any tracking url for this company.', 'woocommerce-shipping-tracking'); ?></span>
						<input class="wcst_disable_tracking_url" id="wcst_disable_tracking_url_checkbox_0" type="checkbox" value="true" data-id="0" name="wcst_custom_shipping_company[<?php echo $counter ?>][disable_tracking_url]" <?php if($company['disable_tracking_url']) echo 'checked="checked"';?>><?php _e('Disable tracking url', 'woocommerce-shipping-tracking'); ?></input><br/>
						
						<div  id="wcst_extra_tracking_services_box_0">
							<label class="wcst_label"><?php _e('Use a 3rd party tracking service to track the shipping', 'woocommerce-shipping-tracking'); ?> </label>
							<input class="wcst_aftership_checkbox" id="wcst_aftership_checkbox_0" type="checkbox" value="true" data-id="0" name="wcst_custom_shipping_company[0][enable_aftership]"><?php _e('Use Aftership service to track order', 'woocommerce-shipping-tracking'); ?></input><br/>
							<input class="wcst_trackingmore_checkbox" id="wcst_trackingmore_checkbox_0" type="checkbox" value="true" data-id="0" name="wcst_custom_shipping_company[0][enable_trackingmore]"><?php _e('Use TrackingMore service to track order', 'woocommerce-shipping-tracking'); ?></input>
						</div>
					</div>
				<?php endif ?>
				</div>
				<script>
				jQuery(document).ready(function() 
				{
						wcst_init(<?php echo $counter; ?>);
				});
				
				function getHtmlTemplate(index)
				{
					var template = '<div class="input_box" >';
									template += '<label><?php _e('Shipping Company Name:', 'woocommerce-shipping-tracking');?> </label>';
									template += '<input type="text" name="wcst_custom_shipping_company['+index+'][name]" placeholder="ex. DHL, UPS, ..." required></input>';
									template += '<br/>';
									template += '<label class="wcst_label"><?php _e('Shipping Company URL:', 'woocommerce-shipping-tracking');?> </label>';
									template += '<input class="wcst_tracking_url_input" id="wcst_tracking_url_input_'+index+'" type="text" size="80" name="wcst_custom_shipping_company['+index+'][url]" placeholder="http://www.ups.com?tracking=%s" required></input>';
									template += '<button class="remove_field button-secondary"><?php _e('Remove field', 'woocommerce-shipping-tracking');?></button>';
									
									template += '<h3><label class="wcst_label"><?php _e('Extra options', 'woocommerce-shipping-tracking'); ?> </label></h3>';
									
									template += '<label class="wcst_label"><?php _e('Disable the tracking url', 'woocommerce-shipping-tracking'); ?> </label>';
									template += '<p><?php _e('The plugin will not generate any tracking url for this company.', 'woocommerce-shipping-tracking'); ?></p>';
									template += '<input class="wcst_disable_tracking_url" id="wcst_disable_tracking_url_checkbox_'+index+'" type="checkbox" value="true" data-id="'+index+'" name="wcst_custom_shipping_company['+index+'][disable_tracking_url]" ><?php _e('Disable tracking url', 'woocommerce-shipping-tracking'); ?></input><br/>';
									
									template += '<div id="wcst_extra_tracking_services_box_'+index+'">';
										template += '<label class="wcst_label"><?php _e('Use a 3rd party tracking service to track the shipping', 'woocommerce-shipping-tracking'); ?> </label>';
										template += '<input class="wcst_aftership_checkbox" id="wcst_aftership_checkbox_'+index+'" type="checkbox" value="true" data-id="'+index+'" name="wcst_custom_shipping_company['+index+'][enable_aftership]"><?php _e('Use Aftership service to track order', 'woocommerce-shipping-tracking'); ?></input><br/>';
										template += '<input class="wcst_trackingmore_checkbox" id="wcst_trackingmore_checkbox_'+index+'" type="checkbox" value="true" data-id="'+index+'" name="wcst_custom_shipping_company['+index+'][enable_trackingmore]"><?php _e('Use TrackingMore service to track order', 'woocommerce-shipping-tracking'); ?></input>';
									template += '</div>';
								template += '</div>';
								
					return template;
				}
				</script>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-shipping-tracking'); ?>" />
				</p>
			</form>
		</div>
		<?php
		echo ob_get_clean();
	}
	private function render_general_tab()
	{
		$options = get_option( 'wcst_options' );
		$custom_companies = get_option( 'wcst_user_defined_companies');
		$shipping_companies = WCST_AdminMenu::get_shipping_companies_list();
		$favorite = isset($options['favorite']) ? $options['favorite'] : -1;
		//wcst_var_dump($favorite);
		
		wp_enqueue_style( 'wcst-common', WCST_PLUGIN_PATH.'/css/wcst_common.css');
		wp_enqueue_style( 'wcst-admin', WCST_PLUGIN_PATH.'/css/wcst_options.css');
		ob_start();
		?>
		<!--<div id="icon-themes" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper">
		<a class='nav-tab nav-tab-active' href='?page=woocommerce-shipping-tracking&tab=general'>Shipping Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=add-custom-companies'>Add Custom Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=edit-messages'>Edit Messages</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=additional-checkout-fields'>Delivery date e time fields</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=general-options'>General</a>
		</h2>-->
		<div class="wrap white-box">
			<?php screen_icon("options-general"); ?>
			<h2><?php _e('Select Shipping Companies used to ship products', 'woocommerce-shipping-tracking');?></h2>
			<form action="options.php" method="post"  style="padding-left:20px">
			<?php settings_fields('wcst_shipping_companies_group'); ?> 
			<table cellpadding="10px">
			<?php if( ($favorite > -1 && count($options) > 1) || ( $favorite < 0 && !empty($options) && count($options) > 0)): ?>
				<h3><?php _e('Select default company', 'woocommerce-shipping-tracking');?></h3>
				<select name="wcst_options[favorite]">
				<option value="NOTRACK" <?php if($favorite === 'NOTRACK') echo 'selected="selected"'; ?>> <?php _e('No Tracking', 'woocommerce-shipping-tracking'); ?></option>
				<?php 
				if(isset( $shipping_companies) && is_array( $shipping_companies))
					foreach( $shipping_companies as $k => $v )
					{
						
						if (isset($options[$k]) == '1') 
						{
							echo '<option value="'.$k.'" ';
							if ($favorite === $k) {
								echo 'selected="selected"';
							}
							echo '>'.$v.'</option>';  
						}
						
					}
					//Custom companies
					if(isset( $custom_companies) && is_array( $custom_companies))
					foreach( $custom_companies as $index => $custom_company )
					{
						if (isset($options[$index]) == '1') 
						{
							echo '<option value="'.$index.'" ';
							if ($favorite === (string)$index) 
							{
								echo ' selected="selected"';
							}
							echo '>'.$custom_company['name'].'</option>';  
						}
					}
				?>
				</select>
			<?php endif; ?>
			
			
			<h3><b><?php _e('User defined Companies list:', 'woocommerce-shipping-tracking');?></b></h3>
			<p><?php _e('You can add new ones clicking on the "Add custom company" menu link.', 'woocommerce-shipping-tracking');?></p>
			<?php
					//Custom companies
					$i = 0;
					if(isset( $custom_companies) && is_array( $custom_companies))
						foreach( $custom_companies as $index => $custom_company )
						{
							if($i%5==0){
								echo '<tr>';
							}
								
							$checked = '';
								
							if(1 == isset($options[$index])){
								$checked = "checked='checked'";
							}
										
							echo "<td><td class='forminp'>
									<input type='checkbox' name='wcst_options[$index]' id='$index' value='1' $checked />
								</td>
								<td scope='row'><label for='$index' >".$custom_company['name']."<br/>(ID: ".$index.")</label></td>
								</td>";
							$i++;
							if($i%5==0){
								echo '</tr>';
							}
						}
					if($i%5!=0){
						echo '</tr>';
					}						
			?>
			</table>
			<h3><b><?php _e('Already defined Companies list:', 'woocommerce-shipping-tracking');?></b></h3>
			
			
				<table cellpadding="10px">
				<?php
										
					$i = 0;
					if(isset( $shipping_companies) && is_array( $shipping_companies))
					foreach( $shipping_companies as $k => $v )
					{
						
						if($i%5==0){
							echo '<tr>';
						}
							
						$checked = '';
							
						if(1 == isset($options[$k])){
							$checked = "checked='checked'";
						}
									
						echo "<td><td class='forminp'>
								<input type='checkbox' name='wcst_options[$k]' id='$k' value='1' $checked />
							</td>
							<td scope='row'><label for='$k' >$v<br/>(ID: $k)</label></td>
							</td>";
								
						$i++;
						if($i%5==0){
							echo '</tr>';
						}
					}
					if($i%5!=0){
						echo '</tr>';
					}
						
				?>
				</table>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-shipping-tracking'); ?>" />
				</p>
			</form>
		</div>
		<?php
		echo ob_get_clean();
	}	
	public function render_delivery_date_time_tab()
	{
		$options = new WCST_Option();
		$wpml = new WCST_Wpml();
		if(isset($_POST['wcst_checkout_options']))
			$options->save_checkout_options($_POST['wcst_checkout_options']);
		
		$messages_and_options = $options->get_checkout_options();
		//wccm_var_dump($_POST['wcst_checkout_options']);
		//wccm_var_dump($messages_and_options);
		wp_enqueue_style( 'wcst-common', WCST_PLUGIN_PATH.'/css/wcst_common.css');
		wp_enqueue_style( 'wcst-admin', WCST_PLUGIN_PATH.'/css/wcst_options.css');	
		?>
		<!--<div id="icon-themes" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper">
		<a class='nav-tab ' href='?page=woocommerce-shipping-tracking&tab=general'>Shipping Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=add-custom-companies'>Add Custom Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=edit-messages'>Edit Messages</a>
		<a class='nav-tab nav-tab-active' href='?page=woocommerce-shipping-tracking&tab=additional-checkout-fields'>Delivery date e time fields</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=general-options'>General</a>
		</h2>-->
		<div class="wrap white-box">
		<?php if($wpml->is_wpml_active()):?>
			<small class="wcst_notice"><strong><?php _e('NOTE:', 'woocommerce-customers-manager');?></strong> <?php _e('WPML Detected! to translate following texts simply select the language you desire from the upper WPML language selector, edit texts and save!', 'woocommerce-customers-manager');?> </small>
		<?php endif; ?>
				
			<?php screen_icon("options-general"); ?>
			<form method="post" >
				<h2><?php _e('Visibility', 'woocommerce-shipping-tracking');?></h2>
				<input type="checkbox" name="wcst_checkout_options[options][show_on_checkout_page]" <?php if(isset($messages_and_options['options']['show_on_checkout_page'])) echo 'checked="checked"'; ?>><?php _e('Show on Checkout page', 'woocommerce-shipping-tracking'); ?></input><br/>
				<input type="checkbox" name="wcst_checkout_options[options][show_on_order_details_page]" <?php if(isset($messages_and_options['options']['show_on_order_details_page'])) echo 'checked="checked"'; ?>><?php _e('Show on Order details page', 'woocommerce-shipping-tracking'); ?></input><br/><br/>
					
			
				<h2><?php _e('Delivery date and time', 'woocommerce-shipping-tracking');?></h2>
				<p><?php _e('If enabled, be displayed a date and time selector to let the user to decide an optional date and/or time when receive items', 'woocommerce-shipping-tracking');?></p>
			
				<input type="checkbox" name="wcst_checkout_options[options][date_range]" <?php if(isset($messages_and_options['options']['date_range'])) echo 'checked="checked"'; ?>><?php _e('Display date range', 'woocommerce-shipping-tracking'); ?></input><br/>
				
				<h3><?php _e('Time ranges', 'woocommerce-shipping-tracking');?></h3>
				<input type="checkbox" name="wcst_checkout_options[options][time_range]" <?php if(isset($messages_and_options['options']['time_range'])) echo 'checked="checked"'; ?>><?php _e('Display time range (you can restrict selection using the following starting and ending hours and minutes boxes)', 'woocommerce-shipping-tracking'); ?></input><br/><br/>
				<label><?php _e('Start hour & minute', 'woocommerce-shipping-tracking');?></label>
				<input type="number" name="wcst_checkout_options[options][time_range_start_hour]" min="0" step="1" max="24" value="<?php if(isset($messages_and_options['options']['time_range_start_hour'])) echo $messages_and_options['options']['time_range_start_hour']; else echo 0;?>"></input>
				<input type="number" name="wcst_checkout_options[options][time_range_start_minute]" min="0" step="1" max="59" value="<?php if(isset($messages_and_options['options']['time_range_start_minute'])) echo $messages_and_options['options']['time_range_start_minute'];  else echo 0;?>"></input><br/>
				<label><?php _e('End hour & minute', 'woocommerce-shipping-tracking');?></label>
				<input type="number" name="wcst_checkout_options[options][time_range_end_hour]" min="0" step="1" max="24" value="<?php if(isset($messages_and_options['options']['time_range_end_hour'])) echo $messages_and_options['options']['time_range_end_hour']; else echo 23;?>"></input>
				<input type="number" name="wcst_checkout_options[options][time_range_end_minute]" min="0" step="1" max="59" value="<?php if(isset($messages_and_options['options']['time_range_end_minute'])) echo $messages_and_options['options']['time_range_end_minute']; else echo 59;?>" ></input><br/><br/><br/>
				
				
				<input type="checkbox" name="wcst_checkout_options[options][time_secondary_range]" <?php if(isset($messages_and_options['options']['time_secondary_range'])) echo 'checked="checked"'; ?>><?php _e('Display secondary time range (will be displayed only in previous option has been checked. You can restrict selection using the following starting and ending hours and minutes boxes)', 'woocommerce-shipping-tracking'); ?></input><br/><br/>
				<label><?php _e('Start hour & minute', 'woocommerce-shipping-tracking');?></label>
				<input type="number" name="wcst_checkout_options[options][time_secondary_range_start_hour]" min="0" step="1" max="24" value="<?php if(isset($messages_and_options['options']['time_secondary_range_start_hour'])) echo $messages_and_options['options']['time_secondary_range_start_hour']; else echo 0;?>"></input>
				<input type="number" name="wcst_checkout_options[options][time_secondary_range_start_minute]" min="0" step="1" max="59" value="<?php if(isset($messages_and_options['options']['time_secondary_range_start_minute'])) echo $messages_and_options['options']['time_secondary_range_start_minute']; else echo 0;?>"></input><br/>
				<label><?php _e('End hour & minute', 'woocommerce-shipping-tracking');?></label>
				<input type="number" name="wcst_checkout_options[options][time_secondary_range_end_hour]" min="0" step="1" max="24" value="<?php if(isset($messages_and_options['options']['time_secondary_range_end_hour'])) echo $messages_and_options['options']['time_secondary_range_end_hour']; else echo 23;?>"></input>
				<input type="number" name="wcst_checkout_options[options][time_secondary_range_end_minute]" min="0" step="1" max="59" value="<?php if(isset($messages_and_options['options']['time_secondary_range_end_minute'])) echo $messages_and_options['options']['time_secondary_range_end_minute']; else echo 59;?>"></input><br/><br/>
				
				
				<h2><?php _e('Title, labels and description', 'woocommerce-shipping-tracking');?></h2>
				<p>
					<label><?php _e('Title', 'woocommerce-shipping-tracking'); ?></label>
					<input class="wcst_checkout_tab_input" type="text" name="wcst_checkout_options[messages][title]" value="<?php if(isset($messages_and_options['messages']['title'])) echo $messages_and_options['messages']['title']; ?>" placeholder="<?php _e('Ex.: Additional shipping delivery info', 'woocommerce-shipping-tracking'); ?>"></input>
				<p/>
				
				<p><?php _e('Will be visible only if one of the previews option has been selected.', 'woocommerce-shipping-tracking');?></p>
				<p>
					<label><?php _e('Date range label', 'woocommerce-shipping-tracking'); ?></label>
					<input class="wcst_checkout_tab_input" type="text" name="wcst_checkout_options[messages][date_range]" value="<?php if(isset($messages_and_options['messages']['date_range'])) echo $messages_and_options['messages']['date_range']; ?>" placeholder="<?php _e('Ex.: Select a start and end period.', 'woocommerce-shipping-tracking'); ?>"></input>
				</p>
				<p>
					<label><?php _e('Time range label', 'woocommerce-shipping-tracking'); ?></label>
					<input class="wcst_checkout_tab_input" type="text" name="wcst_checkout_options[messages][time_range]" value="<?php if(isset($messages_and_options['messages']['time_range'])) echo $messages_and_options['messages']['time_range']; ?>" placeholder="<?php _e('Ex.: Select a start and end time period.', 'woocommerce-shipping-tracking'); ?>"></input>
				</p>
				<p>
					<label><?php _e('Seconday time range', 'woocommerce-shipping-tracking'); ?></label>
					<input class="wcst_checkout_tab_input" type="text" name="wcst_checkout_options[messages][time_secondary_range]" value="<?php if(isset($messages_and_options['messages']['time_secondary_range'])) echo $messages_and_options['messages']['time_secondary_range']; ?>" placeholder="<?php _e('Ex.: Select a secondary start and end time period.', 'woocommerce-shipping-tracking'); ?>"></input>
				</p>
				<p>
					<label><?php _e('Description', 'woocommerce-shipping-tracking'); ?></label>
					<textarea class="wcst_checkout_tab_textarea" name="wcst_checkout_options[messages][note]" placeholder="<?php _e('Ex.: Type a preferead Date and time to receive you items, we will do the best to respect your needings.', 'woocommerce-shipping-tracking'); ?>"><?php if(!empty($messages_and_options['messages']['note'])) echo $messages_and_options['messages']['note']; ?></textarea>
				</p>
				
				<p class="submit">
					<input  name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-shipping-tracking'); ?>" />
				</p>
			</fom>
		</div>
		<?php
	}
	public function render_general_options_tab()
	{
		 $options_controller = new WCST_Option();
		$wpml_helper = new WCST_Wpml();
		if(isset($_POST['wcst_general_options']))
			$options_controller->save_general_options($_POST['wcst_general_options']); //update_option('wcst_general_options', $_POST['wcst_general_options']);
		
		$options = get_option('wcst_general_options');
		$date_format = isset($options['date_format']) ? $options['date_format'] : "dd/mm/yyyy";
		$order_details_page_positioning = isset($options['order_details_page_positioning']) ? $options['order_details_page_positioning'] : "woocommerce_order_details_after_order_table";
		$estimated_shipping_info_product_page_positioning = isset($options['estimated_shipping_info_product_page_positioning']) ? $options['estimated_shipping_info_product_page_positioning'] : "none";
		$redirect_method = isset($options['tracking_form_redirect_method']) ? $options['tracking_form_redirect_method'] : "same_page";
		$enable_bulk_import = isset($options['enable_bulk_import']) ? $options['enable_bulk_import'] : "no";
		$enable_bulk_import_time_interval = isset($options['enable_bulk_import_time_interval']) ? $options['enable_bulk_import_time_interval'] : "daily";
		$enable_bulk_import_csv_file_path = isset($options['enable_bulk_import_csv_file_path']) ? $options['enable_bulk_import_csv_file_path'] : "";
		$estimated_shipping_info_product_page_label = isset($options['estimated_shipping_info_product_page_label']) && isset($options['estimated_shipping_info_product_page_label'][$wpml_helper->get_current_locale()]) ? $options['estimated_shipping_info_product_page_label'][$wpml_helper->get_current_locale()] : __('Estimated shipping date:', 'woocommerce-shipping-tracking');;
		
		wp_enqueue_style( 'wcst-common', WCST_PLUGIN_PATH.'/css/wcst_common.css');
		wp_enqueue_style( 'wcst-admin', WCST_PLUGIN_PATH.'/css/wcst_options.css');		
		
		wp_enqueue_script('wcst-admin-options-page', WCST_PLUGIN_PATH.'/js/wcst-admin-options-page.js', array('jquery'));
		
		$order_statuses = wc_get_order_statuses();
		?>
		<!--<div id="icon-themes" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper">
		<a class='nav-tab ' href='?page=woocommerce-shipping-tracking&tab=general'>Shipping Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=add-custom-companies'>Add Custom Companies</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=edit-messages'>Edit Messages</a>
		<a class='nav-tab' href='?page=woocommerce-shipping-tracking&tab=additional-checkout-fields'>Delivery date e time fields</a>
		<a class='nav-tab nav-tab-active' href='?page=woocommerce-shipping-tracking&tab=general-options'>General</a>
		</h2>-->
		<div class="wrap white-box">
				
			<?php screen_icon("options-general"); ?>
			<h2><?php _e('General options', 'woocommerce-shipping-tracking');?></h2>
			<form action="" method="post" > <!--options.php -->
			<?php settings_fields('wcst_general_options_group'); ?> 
				
				<h3><?php _e('Email options', 'woocommerce-shipping-tracking');?></h3>
				<p>
					<!-- <label><?php _e('Include tracking info only if an order is marked as completed?', 'woocommerce-shipping-tracking'); ?></label>
					<input class="" type="checkbox" name="wcst_general_options[email_options][include_only_if_order_completed]" value="true" <?php  if(isset($options['email_options']['include_only_if_order_completed'])) echo 'checked="checked"';?> ></input>-->
					<label><?php _e('By default tracking info are displayed in every woocommerce outgoing email only if the Order status is completed. Select which in which order status(es) would you like to include the info:', 'woocommerce-shipping-tracking'); ?></label>
					<?php foreach($order_statuses as $order_status => $order_status_name):
						$order_status = str_replace("wc-", "", $order_status); ?>
						<input class="" type="checkbox" name="wcst_general_options[email_options][show_tracking_info_by_order_statuses][<?php echo $order_status; ?>]" value="true" <?php  if($options_controller->get_email_show_tracking_info_by_order_status($order_status)) echo 'checked="checked"';?> ><?php echo $order_status_name; ?></input>
					<?php endforeach; ?>
					
				<p/>
				<h4 class="wcst_option_sub_title"><?php _e('Custom statuses', 'woocommerce-shipping-tracking');?></h4>
				<label><?php _e('In case you are using custom statuses email, please enter the status codes comma separated for which you want to embed shipping tracking info:', 'woocommerce-shipping-tracking');?></label>
				<input type="text" class="wcst_text_input" name="wcst_general_options[email_options][show_tracking_info_by_order_statuses][custom_statuses]" placeholder="wc-shipping,wc-waiting,wc-delivered" value="<?php if(isset($options['email_options']['show_tracking_info_by_order_statuses']['custom_statuses'])) echo $options['email_options']['show_tracking_info_by_order_statuses']['custom_statuses']; ?>"></input>
				
				<h3><?php _e('Date format', 'woocommerce-shipping-tracking');?></h3>
				<label><?php _e('Select the date format used for dispatch and delivery dates', 'woocommerce-shipping-tracking'); ?></label>
				<select name="wcst_general_options[date_format]">
					<option value="dd/mm/yyyy" <?php if($date_format == "dd/mm/yyyy") echo 'selected="selected"';?>><?php _e('dd/mm/yyyy', 'woocommerce-shipping-tracking');?></option>
					<option value="mm/dd/yyyy" <?php if($date_format == "mm/dd/yyyy") echo 'selected="selected"';?>><?php _e('mm/dd/yyyy', 'woocommerce-shipping-tracking');?></option>
					<option value="dd.mm.yyyy" <?php if($date_format == "dd.mm.yyyy") echo 'selected="selected"';?>><?php _e('dd.mm.yyyy', 'woocommerce-shipping-tracking');?></option>
					<option value="mm.dd.yyyy" <?php if($date_format == "mm.dd.yyyy") echo 'selected="selected"';?>><?php _e('mm.dd.yyyy', 'woocommerce-shipping-tracking');?></option>
					<option value="dd-mm-yyyy" <?php if($date_format == "dd-mm-yyyy") echo 'selected="selected"';?>><?php _e('dd-mm-yyyy', 'woocommerce-shipping-tracking');?></option>
					<option value="mm-dd-yyyy" <?php if($date_format == "mm-dd-yyyy") echo 'selected="selected"';?>><?php _e('mm-dd-yyyy', 'woocommerce-shipping-tracking');?></option>
					<option value="mmmm dd, yyyy" <?php if($date_format == "mmmm dd, yyyy") echo 'selected="selected"';?>><?php _e('F j, Y', 'woocommerce-shipping-tracking');?></option>
				</select>
				
				<h3><?php _e('Tracking form redirection', 'woocommerce-shipping-tracking');?></h3>
				<label><?php _e('Select redirection method. Using the "Open new tab" method could cause the browse to detect the new tab as a popup.', 'woocommerce-shipping-tracking'); ?></label>
				<select name="wcst_general_options[tracking_form_redirect_method]">
					<option value="same_page" <?php if($redirect_method == "same_page") echo 'selected="selected"';?>><?php _e('Same page', 'woocommerce-shipping-tracking');?></option>
					<option value="new_tab" <?php if($redirect_method == "new_tab") echo 'selected="selected"';?>><?php _e('Open a new tab', 'woocommerce-shipping-tracking');?></option>
				</select>
				
				<h3><?php _e('Tacking info positioning', 'woocommerce-shipping-tracking');?></h3>
				<label><?php _e('Order details page: select where to display tracking info', 'woocommerce-shipping-tracking'); ?></label>
				<select name="wcst_general_options[order_details_page_positioning]">
					<option value="woocommerce_order_details_after_order_table" <?php if($order_details_page_positioning == "woocommerce_order_details_after_order_table") echo 'selected="selected"';?>><?php _e('After order table', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_view_order" <?php if($order_details_page_positioning == "woocommerce_view_order") echo 'selected="selected"';?>><?php _e('Before order table', 'woocommerce-shipping-tracking');?></option>
				</select>
				
				<h3><?php _e('Product Page - automatic estimated shipping info positioning', 'woocommerce-shipping-tracking');?></h3>
				<label><?php _e('By default estimated shipping info on product page is displayed using the [wcst_show_estimated_date] shortcode. However using the following option you can display that info automatically at specific positions:', 'woocommerce-shipping-tracking'); ?></label>
				<select name="wcst_general_options[estimated_shipping_info_product_page_positioning]">
					<option value="none" <?php if($estimated_shipping_info_product_page_positioning == "none") echo 'selected="selected"';?>><?php _e('Do not automatically show', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_before_add_to_cart_button" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_before_add_to_cart_button") echo 'selected="selected"';?>><?php _e('After variable options dropdown(s) and before add to cart button', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_before_add_to_cart_form" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_before_add_to_cart_form") echo 'selected="selected"';?>><?php _e('Before both variable options dropdown(s)	and add to cart button', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_after_add_to_cart_button" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_after_add_to_cart_button") echo 'selected="selected"';?>><?php _e('After add to cart button', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_product_thumbnails" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_product_thumbnails") echo 'selected="selected"';?>><?php _e('After product images', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_before_single_product_summary" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_before_single_product_summary") echo 'selected="selected"';?>><?php _e('Before product Images', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_single_product_summary" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_single_product_summary") echo 'selected="selected"';?>><?php _e('Before short description', 'woocommerce-shipping-tracking');?></option>
					<option value="woocommerce_after_single_product_summary" <?php if($estimated_shipping_info_product_page_positioning == "woocommerce_after_single_product_summary") echo 'selected="selected"';?>><?php _e('After product description', 'woocommerce-shipping-tracking');?></option>
				</select>
				
				<label class="wcst_option_label" ><?php _e('Label to display before the date:', 'woocommerce-shipping-tracking'); ?></label>
				<?php if ($wpml_helper->is_wpml_active()): ?>
				<p>
				<?php _e('<strong>WPML NOTE:</strong> to localize this label, simply switch language using WPML language selector and then save the options.', 'woocommerce-shipping-tracking'); ?>
				</p>
				<?php endif; ?>
				<input class="wcst_option_text_field" type="text" name="wcst_general_options[estimated_shipping_info_product_page_label][<?php echo $wpml_helper->get_current_locale();?>]" placeholder="<?php _e('Estimated shipping date:', 'woocommerce-shipping-tracking'); ?>" value="<?php echo $estimated_shipping_info_product_page_label;?>">
				
				<h3><?php _e('Automatic bulk import', 'woocommerce-shipping-tracking');?></h3>
				<p><?php _e('<strong>NOTE:</strong> Scheduling task is performed using the WordPress function <i>wp_schedule_event()</i>. It will trigger  the scheduled import task at the specified interval <strong>ONLY</strong> if someone visits your WordPress site. More info at <a href="https://codex.wordpress.org/Function_Reference/wp_schedule_event" target="_blank">wp_schedule_event reference page</a>.', 'woocommerce-shipping-tracking'); ?></p>
				<label><?php _e('Enable automatic bulk import?', 'woocommerce-shipping-tracking'); ?></label>
				<select name="wcst_general_options[enable_bulk_import]" id="wcst_enable_bulk_import">
					<option value="no" <?php if($enable_bulk_import == "no") echo 'selected="selected"';?>><?php _e('No', 'woocommerce-shipping-tracking');?></option>
					<option value="yes" <?php if($enable_bulk_import == "yes") echo 'selected="selected"';?>><?php _e('Yes', 'woocommerce-shipping-tracking');?></option>
				</select>
				
				<div id="wcst_advanced_bulk_import_options">
					<label  class="wcst_label" ><?php _e('Set a valid csv url path', 'woocommerce-shipping-tracking'); ?></label>
					<p><?php _e('It can be a DropBox public url, http url, etc...', 'woocommerce-shipping-tracking'); ?></p>
					<input type="url" class="wcst_csv_file_input_text" placeholder="http://www.yoursite.com/wp-content/csv/file.csv" name="wcst_general_options[enable_bulk_import_csv_file_path]" value="<?php echo $enable_bulk_import_csv_file_path; ?>"></input>
					
					<label  class="wcst_label" ><?php _e('How often has the automatic import to be performed?', 'woocommerce-shipping-tracking'); ?></label>
					<select name="wcst_general_options[enable_bulk_import_time_interval]">
						<option value="hourly" <?php if($enable_bulk_import_time_interval == "hourly") echo 'selected="selected"';?>><?php _e('Hourly', 'woocommerce-shipping-tracking');?></option>
						<option value="twicedaily" <?php if($enable_bulk_import_time_interval == "twicedaily") echo 'selected="selected"';?>><?php _e('Twice a day', 'woocommerce-shipping-tracking');?></option>
						<option value="daily" <?php if($enable_bulk_import_time_interval == "daily") echo 'selected="selected"';?>><?php _e('Daily', 'woocommerce-shipping-tracking');?></option>
					</select>
				</div>
				
				<p class="submit">
					<input  name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-shipping-tracking'); ?>" />
				</p>
			</fom>
		</div>
		<?php
	}
}
?>