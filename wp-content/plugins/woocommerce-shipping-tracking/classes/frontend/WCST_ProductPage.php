<?php 
class WCST_ProductPage
{
	public function __construct()
	{
		 $options_controller = new WCST_Option();
		 $estimated_shipping_info_product_page_positioning = $options_controller->get_general_options('estimated_shipping_info_product_page_positioning', 'none');
		 if($estimated_shipping_info_product_page_positioning != "none")
			 add_action($estimated_shipping_info_product_page_positioning, array(&$this, 'render_estimated_shipping_date'));
			 
	}
	public function render_estimated_shipping_date()
	{
		global $post;
		$options_controller = new WCST_Option();
		$wpml_helper = new WCST_Wpml();
		$estimated_shipping_info_product_page_label = $options_controller->get_general_options('estimated_shipping_info_product_page_label');
		$estimated_shipping_info_product_page_label = isset($estimated_shipping_info_product_page_label[$wpml_helper->get_current_locale()]) ? $estimated_shipping_info_product_page_label[$wpml_helper->get_current_locale()] : __('Estimated shipping date:', 'woocommerce-shipping-tracking');
		echo $estimated_shipping_info_product_page_label." ".do_shortcode('[wcst_show_estimated_date product_id="'.$post->ID.'"]');	
	}
}
?>