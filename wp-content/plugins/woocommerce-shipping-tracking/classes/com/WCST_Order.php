<?php 
class WCST_Order
{
	public function __construct()
	{
		add_action('wp_ajax_wcst_get_order_list', array(&$this, 'ajax_get_order_partial_list'));
		add_action('wp_ajax_wcst_upload_tracking_csv', array(&$this, 'process_csv_upload_ajax'));
	}
	function process_csv_upload_ajax()
	{
		
		$csv_array = explode("<#>", $_POST['csv']);
		$result = $this->process_csv_data_and_update_orders($csv_array);
	
		foreach((array)$result as $message)
				echo $message;
		wp_die();
	}
	public function load_csv_data_from_url_and_import($csv_url)
	{
		$data = file_get_contents($csv_url);
		if( $data == false)
		{
			$error_message = sprintf(__('Automatic bulk importer task failed because could not load the following file: %s', 'woocommerce-shipping-tracking'), $csv_url);
			$wcst_email_model->send_error_email_to_admin($error_message);
			return;
		}
		$rows = explode("\n",$data);
		$this->process_csv_data_and_update_orders($rows);
	}
	private function process_csv_data_and_update_orders($csv_array = null)
	{
		$customerAdded = 0;
		$messages = array(); 
		$order_statuses = wc_get_order_statuses();
		$allowed_email_notification_statuses = array("send_email_new_order,send_email_cancelled_order",
													 "send_email_customer_processing_order",
													 "send_email_customer_completed_order",
													 "send_email_customer_refunded_order",
													 "send_email_customer_invoice" );
		$columns_names = array("order_id",
								"order_status",
								"force_email_notification",
								"dispatch_date",
								"custom_text",
								"tracking_info");
		$colum_index_to_name = array();
		
		$row = 1;
		$updated = 0;
		if($csv_array != null)
		{
			//while (($data = fgetcsv($handle)) !== FALSE) 
			foreach($csv_array as $csv_row)
			{
				//wccm_var_dump($csv_row);
				if(empty($csv_row) || $csv_row == "")
					continue;
				$data = str_getcsv($csv_row);
				$num = count($data);
				$order = array();
				
				for ($c=0; $c < $num; $c++) 
				{						
					if($row == 1)
					{
						foreach( $columns_names as $title)
							if($title == $data[$c])
									$colum_index_to_name[$c] = $title;
					}
					else
					{
						if(isset($colum_index_to_name[$c]))
						{
							$order[$colum_index_to_name[$c]] = $data[$c];
						}
					}
					
				}
				if($order != null)
				{
					//Order id
					$is_order_id_valid =   !isset($order['order_id'] ) || $order['order_id'] == "" || empty($order['order_id'] ) || !is_numeric($order['order_id']) ? false : true; 
					if($is_order_id_valid)
					{
						$order_object = new WC_Order($order['order_id']);
						$is_order_id_valid =  !isset($order_object->post) ? false : $is_order_id_valid;
					}
					if(!$is_order_id_valid)
						array_push( $messages, '<span class="error_message">'.sprintf(__("Order %s: the id is not valid.", 'woocommerce-shipping-tracking'), $order[ 'order_id' ]).'</span><br/>' );
					
					//Order status
					$is_status_valid = !isset($order['order_status'] ) || $order['order_status'] == "" || empty($order['order_status'] ) ? true : false; 
					foreach($order_statuses as $code => $status_name)
						if($order['order_status'] == $code)
							$is_status_valid = true;
					if(!$is_status_valid)	
					{
						//array_push( $messages, new WP_Error('order', sprintf(__("Order %s selected status was not valid.", 'woocommerce-shipping-tracking'), $order[ 'order_id' ])));
						array_push( $messages, '<br/><span class="error_message">'.sprintf(__("Order %s: selected status was not valid. Its status has been left unchanged.", 'woocommerce-shipping-tracking'), $order[ 'order_id' ]).'</span><br/>' );
						//wcst_var_dump($messages);
					}
					
					//Forced email notification
					$is_notification_email_status_valid = !isset($order['force_email_notification'] ) || $order['force_email_notification'] == "" || empty($order['force_email_notification'] ) ? true : false; 
					$is_notification_email_status_valid = in_array( $order['force_email_notification'], $allowed_email_notification_statuses) ? true : $is_notification_email_status_valid;
					
					if(!$is_notification_email_status_valid)	
						array_push( $messages, '<span class="error_message">'.sprintf(__("Order %s: notification email status was not valid. No notification has been sent.", 'woocommerce-shipping-tracking'), $order[ 'order_id' ]).'</span><br/>' );
				
					//Track info
					$tracking_info_strings = explode("|", $order['tracking_info']);
					$company_id_and_tracking_code = array();
					foreach((array)$tracking_info_strings as $tracking_info_string)
					{
						$temp = explode(":", $tracking_info_string);
						array_push($company_id_and_tracking_code, array('company_id' => $temp[0], 'tracking_code' => $temp[1]));
					}
				
					//Custom text
					$custom_texts = explode("|",$order['custom_text']);
					//Dispatch date
					$dispatch_dates = explode("|",$order['dispatch_date']);
					
					if(empty($company_id_and_tracking_code))
						array_push( $messages, '<span class="error_message">'.sprintf(__("Order %s: tacking info is not valid.", 'woocommerce-shipping-tracking'), $order[ 'order_id' ]).'</span><br/>' );
					else
					{
						//Save info 
						global $wcst_email_model;
						$meta_data_array = array();
						$meta_data_array['_wcst_order_trackurl'] = $company_id_and_tracking_code[0]['company_id'];
						$meta_data_array['_wcst_order_trackno'] = $company_id_and_tracking_code[0]['tracking_code'];
						$meta_data_array['_wcst_order_dispatch_date'] = $dispatch_dates[0];
						$meta_data_array['_wcst_custom_text'] = $custom_texts[0];
						
						if(count($company_id_and_tracking_code) > 1 )
						{
							$additiona_company = array();
							$meta_data_array['_wcst_order_additional_shipping'] = array();
							for($i = 1; $i < count($company_id_and_tracking_code); $i++)
							{
								$additiona_company['trackurl'] = isset($company_id_and_tracking_code[$i]['company_id']) ? $company_id_and_tracking_code[$i]['company_id'] : "";
								$additiona_company['trackno'] = isset($company_id_and_tracking_code[$i]['tracking_code']) ? $company_id_and_tracking_code[$i]['tracking_code'] : "";
								$additiona_company['order_dispatch_date'] = isset($dispatch_dates[$i]) ? $dispatch_dates[$i] : "";
								$additiona_company['custom_text'] = isset($custom_texts[$i]) ? $custom_texts[$i] : "";
							}
							array_push($meta_data_array['_wcst_order_additional_shipping'], $additiona_company);
						}
						
						//wcst_var_dump($meta_data_array);
						if($is_order_id_valid)
						{
							$this->save_shippings_info_metas($order['order_id'], $meta_data_array);
							
			
							if($is_status_valid && $order['order_status'] != "" )
							{
								foreach($order_statuses as $code => $status_name)
									if($order['order_status'] == $code)
										$order_object->update_status($order['order_status']);
							}
							if($is_notification_email_status_valid && $order['force_email_notification'] != "")
							{
								//wcst_var_dump($order['force_email_notification']);
								$wcst_email_model->force_status_email_sending($order['force_email_notification'], $order_object);
							} 
						}
					}
				}
				$row++;
			}
			//array_push( $messages, sprintf(__('Updated %d orders!', 'woocommerce-shipping-tracking'),  $updated  ));
			
		}
		//wcst_var_dump($messages);
		return $messages;
	}
	public function get_delivery_and_times($order_id)
	{
		$result =  get_post_meta($order_id, '_wcst_order_delivery_datetimes' , true);
		return isset($result) && $result != "" ? $result : array();
	}
	public function save_delivery_date_and_time($order_id, $date_and_time)
	{
		return update_post_meta($order_id, '_wcst_order_delivery_datetimes',$date_and_time);
	}
	public function get_order_statuses($get_codes = false)
	{
		$result = array('version'=>0, 'statuses'=>array());
		if(function_exists( 'wc_get_order_statuses' ))
		{
			
			$result['version'] = 2.2;
			//[slug] => name
			if(!$get_codes)
				$result['statuses'] = wc_get_order_statuses();
			else foreach(wc_get_order_statuses() as $code => $name)
				$result['statuses'][] = $code;
		}
		else
		{
			$args = array(
				'hide_empty'   => false, 
				'fields'            => 'id=>name', 
			);
			$result['version'] = 2.1;
			//[id] => name
			$result['statuses'] =  get_terms('shop_order_status', $args);
		}
		return $result;
	}
	public function ajax_get_order_partial_list()
	{
		$resultCount = 50;
		$search_string = isset($_GET['search_string']) ? $_GET['search_string'] : null;
		$page = isset($_GET['page']) ? $_GET['page'] : null;
		$offset = isset($page) ? ($page - 1) * $resultCount : null;
		$orders = $this->get_order_list($search_string ,$offset, $resultCount);
		 echo json_encode( $orders);
		 wp_die();
	}
	public function get_orders_by_user_id($user_id)
	{
		$args =  array(
				'numberposts' => -1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user_id,
				//'post_type'   => 'shop_order',
				'post_type'   => wc_get_order_types(),
				//'post_status' => $this->get_order_statuses()['statuses']
				'post_status' => array_keys( wc_get_order_statuses() )
			 );
			 
		return get_posts($args);	
	}
	private function get_order_list($search_string = null, $offset = null, $resultCount  = null)
	{
		global $wpdb;
		$statuses = $this->get_order_statuses(true);
		$statuses_names = $this->get_order_statuses();
		$limit_query = isset($offset) && isset($resultCount) ? " LIMIT {$resultCount} OFFSET {$offset}": "";
		$additional_select = $additional_join = $additional_where = "";
		if($search_string)
		{
			$offset = null;
			$limit_query = "";
			$additional_join = " INNER JOIN {$wpdb->postmeta} AS billing_name_meta  ON billing_name_meta.post_id = orders.ID 
								 INNER JOIN {$wpdb->postmeta} AS billing_last_name_meta  ON billing_last_name_meta.post_id = orders.ID
								 INNER JOIN {$wpdb->postmeta} AS billing_email_meta  ON billing_email_meta.post_id = orders.ID
								 INNER JOIN {$wpdb->postmeta} AS shipping_name_meta  ON shipping_name_meta.post_id = orders.ID
								 INNER JOIN {$wpdb->postmeta} AS shipping_last_name_meta  ON shipping_last_name_meta.post_id = orders.ID
								 INNER JOIN {$wpdb->postmeta} AS customer_id_meta  ON customer_id_meta.post_id = orders.ID
								";
			$additional_where = " AND billing_name_meta.meta_key = '_billing_first_name' 
								  AND billing_last_name_meta.meta_key = '_billing_last_name' 
								  AND billing_email_meta.meta_key = '_billing_email' 
								  AND shipping_name_meta.meta_key = '_shipping_first_name' 
								  AND shipping_last_name_meta.meta_key = '_shipping_last_name' 
								  AND customer_id_meta.meta_key = '_customer_user' 
			";
		}
		 $query_string = "SELECT orders.ID as order_id, orders.post_date as order_date, orders.post_status as order_status
							 FROM {$wpdb->posts} AS orders {$additional_join}
							 WHERE orders.post_status IN ('".implode("','", $statuses['statuses'])."') 
							 AND orders.post_type = 'shop_order' {$additional_where} ";
		if($search_string)
				$query_string .=  " AND ( orders.ID LIKE '%{$search_string}%' OR  
										  orders.post_date LIKE '%{$search_string}%' OR 
										  orders.post_status LIKE '%{$search_string}%' OR
										  billing_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  billing_last_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  billing_email_meta.meta_value LIKE '%{$search_string}%' OR 
										  shipping_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  shipping_last_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  customer_id_meta.meta_value LIKE '%{$search_string}%' 
										  )";
		
		$query_string .=  " GROUP BY orders.ID ORDER BY orders.post_date DESC ".$limit_query ;
		$results = $wpdb->get_results($query_string );
		//wcst_var_dump($query_string);
		
		$bad_char = array('"', "'");
		foreach((array)$results as $key => $result)
		{
			$order = new WC_Order($result->order_id);
			$user = $order->customer_user > 0 ? get_userdata($order->customer_user) : null;
			$results[$key]->billing_name_and_last_name = str_replace($bad_char, "", $order->billing_first_name." ".$this->billing_last_name);
			$results[$key]->shipping_name_and_last_name = str_replace($bad_char, "",$order->shipping_first_name." ".$this->shipping_last_name);
			//$results[$key]->shipping_address = method_exists($order, 'get_formatted_shipping_address') ? str_replace($bad_char, "",$order->get_formatted_shipping_address()) : "";
			$results[$key]->user_login = isset($user) ? $user->user_login: "Guest";
			$results[$key]->user_id = $order->customer_user;
			$results[$key]->user_email = isset($user->billing_email) ? $user->billing_email : "N/A" /* isset($user) ? $user->user_email: " N/A" */;
			$results[$key]->order_status = $statuses_names['statuses'][$result->order_status];
		}
		//wcst_var_dump($results);
		
		if(isset($offset) && isset($resultCount))
		{
			$query_string = "SELECT COUNT(*) as tot
							 FROM {$wpdb->posts} AS orders
							 WHERE orders.post_type = 'shop_order' ";
			$num_order = $wpdb->get_col($query_string);
			$num_order = isset($num_order[0]) ? $num_order[0] : 0;
			$endCount = $offset + $resultCount;
			$morePages = $num_order > $endCount;
			$results = array(
				  "results" => $results,
				  "pagination" => array(
					  "more" => $morePages
				  )
			  );
		}
		else
			$results = array(
				  "results" => $results,
				  "pagination" => array(
					  "more" => $false
				  )
			  );
		
		return $results;
	}
	public function is_email_tracking_info_embedding_disabled($order_id)
	{
		$result = get_post_meta( $order_id, '_wcst_order_disable_email', true);
		//wcst_var_dump($result);
		return isset($result) && $result == 'disable_email_embedding' ? true : false;
	}
	public function save_shippings_info_metas($post_id, $data_to_save)
	{
		global $wcst_shipping_company_model;
		$order = new WC_Order($post_id);
		/* $post_code = stripslashes( $data_to_save['_billing_postcode']);
		if(isset( $data_to_save['_shipping_postcode']) && !empty( $data_to_save['_shipping_postcode']))
			$post_code = stripslashes( $data_to_save['_shipping_postcode']); */
		$post_code = isset($order->shipping_postcode) && $order->shipping_postcode != "" ? $order->shipping_postcode : $order->billing_postcode;
		
		$info = WCST_shipping_companies_url::get_company_url(stripslashes( $data_to_save['_wcst_order_trackurl'] ), stripslashes( $data_to_save['_wcst_order_trackno'] ), $post_code );
		add_post_meta( $post_id, '_order_key', uniqid('order_') );
		update_post_meta( $post_id, '_wcst_order_trackno', stripslashes( $data_to_save['_wcst_order_trackno'] ));
		update_post_meta( $post_id, '_wcst_order_dispatch_date', stripslashes( $data_to_save['_wcst_order_dispatch_date'] ));
		update_post_meta( $post_id, '_wcst_custom_text', stripslashes( $data_to_save['_wcst_custom_text'] ));
		update_post_meta( $post_id, '_wcst_order_trackname', stripslashes( $wcst_shipping_company_model->get_company_name_by_id($data_to_save['_wcst_order_trackurl']) ));
		update_post_meta( $post_id, '_wcst_order_trackurl', stripslashes( $data_to_save['_wcst_order_trackurl'] ));
		update_post_meta( $post_id, '_wcst_order_track_http_url', stripslashes( $info['urltrack'] ));
		update_post_meta( $post_id, '_wcst_order_disable_email', $data_to_save['_wcst_order_disable_email']);
		
		//additional
		if(!isset($data_to_save['_wcst_order_additional_shipping']))
		{
			delete_post_meta( $post_id, '_wcst_additional_companies' );
			return;
		}
		
		$addtional_companies_counter = 0;
		$additiona_companies = array();
		foreach($data_to_save['_wcst_order_additional_shipping'] as $additional_company)
		{
			$temp = array();
			$info = WCST_shipping_companies_url::get_company_url(stripslashes( $additional_company['trackurl'] ), stripslashes( $additional_company['trackno'] ), $post_code );
			$temp['_wcst_order_trackno'] = $additional_company['trackno'] ;
			$temp['_wcst_custom_text'] = $additional_company['custom_text'] ;
			$temp['_wcst_order_dispatch_date'] = $additional_company['order_dispatch_date'] ;
			$temp['_wcst_order_trackname'] = stripslashes( $wcst_shipping_company_model->get_company_name_by_id($additional_company['trackurl']) );
			$temp['_wcst_order_trackurl'] = stripslashes( $additional_company['trackurl']);
			$temp['_wcst_order_track_http_url'] = stripslashes( $info['urltrack']);
			array_push($additiona_companies, $temp);
		}
		update_post_meta( $post_id, '_wcst_additional_companies', $additiona_companies );
	}
}
?>