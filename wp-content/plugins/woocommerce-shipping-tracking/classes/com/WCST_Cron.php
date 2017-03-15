<?php 
class WCST_Cron
{
	public function __construct()
	{
		add_action( 'wp', array(&$this,'schedule_bulk_import_if_any') );
		add_action( 'wcst_bulk_import_csv_data', array(&$this, 'bulk_import_csv_data' ));
	}

	function schedule_bulk_import_if_any() 
	{
		$options = get_option('wcst_general_options');
		$enable_bulk_import = isset($options['enable_bulk_import']) ? $options['enable_bulk_import'] : "no";
		$enable_bulk_import_time_interval = isset($options['enable_bulk_import_time_interval']) ? $options['enable_bulk_import_time_interval'] : "daily";
		//$enable_bulk_import_csv_file_path = isset($options['enable_bulk_import_csv_file_path']) ? $options['enable_bulk_import_csv_file_path'] : "";
		
		if($enable_bulk_import == 'yes')
		{
			if ( !wp_next_scheduled( 'wcst_bulk_import_csv_data' ) ) 
			{
				wp_schedule_event( time(), $enable_bulk_import_time_interval, 'wcst_bulk_import_csv_data' ); 
			}
		}
		else 
			wp_clear_scheduled_hook( 'wcst_bulk_import_csv_data' );
		
	}
	function bulk_import_csv_data()
	{
		global $wcst_order_model;
		$options = get_option('wcst_general_options');
		$enable_bulk_import_csv_file_path = isset($options['enable_bulk_import_csv_file_path']) ? $options['enable_bulk_import_csv_file_path'] : "";
		if($enable_bulk_import_csv_file_path != "")
			$wcst_order_model->load_csv_data_from_url_and_import($enable_bulk_import_csv_file_path);
	}
}
?>