<?php
class WCST_WooCommerceAddon
{
	
	var $track;
	function __construct() 
	{
		$theme_version = wcst_get_file_version( get_template_directory() . '/woocommerce/myaccount/my-account.php' );
		try{
			$wc_version = wcst_get_woo_version_number();
		}catch(Exception $e){}
		
		add_action( 'add_meta_boxes', array( &$this, 'woocommerce_metaboxes' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( &$this, 'woocommerce_process_shop_ordermeta' ), 5, 2 );
		add_action( 'admin_menu', array( &$this, 'ship_select_menu')); 
		
		
		if(!isset($wc_version) || version_compare($wc_version , 2.6, '<') || version_compare($theme_version , 2.6, '<') )
			add_action( 'woocommerce_after_my_account', array( &$this, 'add_shipping_tracking_buttons'));
		if(isset($wc_version) && version_compare($wc_version , 2.6, '>=') )
			add_action( 'woocommerce_account_content', array( &$this,'add_shipping_tracking_buttons'),99 );
		
		//Order table
		add_filter( 'woocommerce_shop_order_search_fields', array( &$this, 'woocommerce_shop_order_search_tracking_number') );
		add_action( 'manage_edit-shop_order_columns', array( &$this, 'add_tracking_column'), 20, 1 );
		add_action( 'manage_shop_order_posts_custom_column', array( &$this, 'add_tacking_info_to_column'));
		add_filter( 'manage_edit-product_columns', array(&$this, 'add_estimated_column'),15 );
		add_action( 'manage_product_posts_custom_column', array(&$this, 'add_estimation_info_to_column'), 10, 2 );
		add_action('restrict_manage_posts', array( &$this,'add_uploads_select_box_filter'));
		add_filter('parse_query',array( &$this,'filter_query_by_shipping_company')); 
	
	}
	public function add_uploads_select_box_filter()
	{
		global $typenow, $wp_query, $wcst_html_helper; 
		if ($typenow=='shop_order') 
		{
			$selected = isset($_GET['wcst_filter_by_shipping_company']) && $_GET['wcst_filter_by_shipping_company'] ? $_GET['wcst_filter_by_shipping_company']:"none";
			//onchange="this.form.submit()" ?>
			<select name="wcst_filter_by_shipping_company" >
				<option value="none" <?php if($selected == "none") echo 'selected="selected"';?>><?php _e('Select a shipping company', 'woocommerce-shipping-tracking') ?></option>
				<?php $wcst_html_helper->generic_shipping_comanies_dropdown_options($selected); ?>
			</select>
			<?php
		}
	}
	function filter_query_by_shipping_company($query) 
	{
		global $pagenow;
		$qv = &$query->query_vars;
		if ($pagenow=='edit.php' && 
		    isset($qv['post_type']) && $qv['post_type']=='shop_order' && isset($_GET['wcst_filter_by_shipping_company']) && $_GET['wcst_filter_by_shipping_company'] != 'none') 
		{
			 $qv['meta_query'][] = 
				array(
				 'relation' => 'OR',
				  array(
					'key' => '_wcst_order_trackurl',
					'compare' => '=',
					/*'compare' => 'NOT NULL',
					 'type' => 'CHAR' , */
					 'value' => $_GET['wcst_filter_by_shipping_company']
				  ),
				   array(
					'key' => '_wcst_additional_companies',
					'compare' => 'LIKE',
					 'value' => serialize('_wcst_order_trackurl').serialize($_GET['wcst_filter_by_shipping_company'])
				  )
			  );
			 // wcst_var_dump( $qv['meta_query']);
		}
	}
	function woocommerce_shop_order_search_tracking_number( $search_fields ) {

		$search_fields[] = '_wcst_order_trackno';
		$search_fields[] = '_wcst_additional_companies';
		$search_fields[] = '_wcst_order_trackname';
		return $search_fields;
	}
	//Order list columns
	function add_tracking_column($columns){ 
	
		wp_enqueue_style('wcst-info-tracking-box',  WCST_PLUGIN_PATH.'/css/wcst_orders_list.css');
		$columns["wcst_tracking_number"] = __('Tracking Number', 'woocommerce-shipping-tracking');
		
		return $columns;
		
	}
	//Order list columns
	function add_tacking_info_to_column($column)
	{ 
		global $post, $woocommerce, $the_order, $wcst_product_model;
			
			if ( empty( $the_order ) || $the_order->id != $post->ID )
				$the_order = new WC_Order( $post->ID );
				
			switch ( $column ) 
			{
				case "wcst_tracking_number" :
					
					$order_meta = get_post_custom( $the_order->id );
								
					//if(isset($order_meta['_wcst_order_trackno']) && isset($order_meta['_wcst_order_trackurl']))
					if(isset($order_meta['_wcst_order_trackno']) && isset($order_meta['_wcst_order_trackurl']) && isset($order_meta['_wcst_order_trackname']))
					{
					 	$this->admin_shipping_details_to_column($order_meta, $the_order);
					}
					if(isset($order_meta['_wcst_additional_companies']))
					{
						$additiona_companies = unserialize($order_meta['_wcst_additional_companies'][0]);
						$this->admin_additional_shipping_details_to_column($additiona_companies, $the_order);
					}
				break; 
				
				
			}
			
		}
	//Product list columns
	function add_estimated_column($columns){ 
	
		$columns["wcst_estimated_rule_name"] =__('Estimated shipping rule', 'woocommerce-shipping-tracking');
		
		return $columns;
		
	}
	//Product list columns
	function add_estimation_info_to_column( $column, $post_id ) 
	{ 
		global $post, $woocommerce, $the_order, $wcst_product_model;
	
			switch ( $column ) 
			{
					
				case "wcst_estimated_rule_name":
					$rule = $wcst_product_model->get_estimation_shippment_rule($post_id);
					if(isset($rule))
						echo '<a class="" target="_blank" href="'.admin_url().'admin.php?page=acf-options-estimated-shipping-configurator">'.
								$rule['name_id'].
								'</a></br>';
					break;
				
			}
			
		}
		
	function admin_shipping_details_to_column($order_meta , $order)
	{
		
		$urltrack =isset($order_meta['_wcst_order_track_http_url'][0]) ? $order_meta['_wcst_order_track_http_url'][0] : "#";
	
		if ($order_meta['_wcst_order_trackno'][0] != null && $order_meta['_wcst_order_trackurl'][0] != null && $order_meta['_wcst_order_trackurl'][0] != 'NOTRACK' ) 
		{ ?>
			<div class="wcst_tracking_info_box"><STRONG><?php 
				echo $order_meta['_wcst_order_trackname'][0];
			?></STRONG><br/>
			<a target="_blank" href="<?php echo $urltrack;?>">#<?php echo $order_meta['_wcst_order_trackno'][0]; ?></a>
			</div>
		<?php } 
		
	}
	function admin_additional_shipping_details_to_column($additiona_companies , $order)
	{
		foreach($additiona_companies as $order_meta)
		{
			$urltrack = isset($order_meta['_wcst_order_track_http_url']) ? $order_meta['_wcst_order_track_http_url'] : "#";
		
			if ($order_meta['_wcst_order_trackno'] != null && $order_meta['_wcst_order_trackurl'] != null && $order_meta['_wcst_order_trackurl'] != 'NOTRACK' ) 
			{ ?>
				<div class="wcst_tracking_info_box"><STRONG><?php 
					echo $order_meta['_wcst_order_trackname'];
				?></STRONG><br/>
				<a target="_blank" href="<?php echo $urltrack;?>">#<?php echo $order_meta['_wcst_order_trackno']; ?></a>
				</div>
			<?php } 
		}
		
	}
	function woocommerce_process_shop_ordermeta( $post_id, $post ) 
	{
		$wcst_order_model = new WCST_Order();
		$wcst_order_model->save_shippings_info_metas( $post_id, $_POST);
	}

	function woocommerce_metaboxes() 
	{
		global $wcst_html_helper;
		add_meta_box( 'woocommerce-order-ship', __('Tracking Code', 'woocommerce-shipping-tracking'), array( &$wcst_html_helper, 'render_shipping_companies_tracking_info_configurator_widget' ), 'shop_order', 'side', 'high');

	}
		
	function ship_select_menu(){
		
		if (!function_exists('current_user_can') || !current_user_can('manage_options') )
		return;
			
	}
	
	function add_shipping_tracking_buttons()
	{
		global $wp, $wcst_order_model;
		$can_render = false;
		if ( did_action( 'woocommerce_account_content' ) ) 
		{
				foreach ( $wp->query_vars as $key => $value ) 
				{
					if($key == 'orders')
						$can_render = true;
				}
		}
		else
			$can_render = true;
		
		if(!$can_render)
			return false;
		
			if(!get_current_user_id())
				return;
			
			$orders = $wcst_order_model->get_orders_by_user_id(get_current_user_id());
			
			$track_urls = array();
			foreach($orders as $order)
			{
				$order_meta = get_post_custom( $order->ID );
				$order_meta_additional_shippings = isset($order_meta['_wcst_additional_companies']) ? unserialize(array_shift($order_meta['_wcst_additional_companies'])): array();
				$track_urls[$order->ID] = array();
				if(isset($order_meta['_wcst_order_trackno']) && isset($order_meta['_wcst_order_trackurl']) && isset($order_meta['_wcst_order_trackname']) && strlen ($order_meta['_wcst_order_trackno'][0]) > 0)
				{
					 array_push($track_urls[$order->ID], $order_meta['_wcst_order_track_http_url'][0]);
				}
				foreach($order_meta_additional_shippings as $additional)
				{
					array_push($track_urls[$order->ID], $additional['_wcst_order_track_http_url']);
				}
				if(empty($track_urls[$order->ID]))
					$track_urls[$order->ID] = "false";
			}
			wp_enqueue_style('wcst-order-table', WCST_PLUGIN_PATH.'/css/wcst_order_table.css');
			include WCST_PLUGIN_ABS_PATH.'template/my_account_orders_table.php';
		}
} 
?>