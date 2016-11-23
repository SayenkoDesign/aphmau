<?php
require_once __DIR__.'/app/bootstrap.php';
$twig = $container->get("twig.environment");
$data = [];
ob_start();
woocommerce_page_title();
$data['title'] = ob_get_clean();
$data['products'] = [];

//do_action( 'woocommerce_before_main_content' );
//do_action( 'woocommerce_archive_description' );
if ( have_posts() ) {
	//do_action( 'woocommerce_before_shop_loop' );
	//woocommerce_product_loop_start();
	//woocommerce_product_subcategories();
	while ( have_posts() ){
		ob_start();
		the_post();
		wc_get_template_part( 'content', 'product' );
		$data['products'][]= ob_get_clean();
	}
	//woocommerce_product_loop_end();
	//do_action( 'woocommerce_after_shop_loop' );
}

//do_action( 'woocommerce_after_main_content' );
//do_action( 'woocommerce_sidebar' );

echo $twig->render('products.html.twig', $data);
