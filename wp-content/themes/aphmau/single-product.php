<?php
require_once __DIR__.'/app/bootstrap.php';
$twig = $container->get("twig.environment");
$data = [];


//do_action( 'woocommerce_before_main_content' );
while ( have_posts() ) {
    ob_start();
    the_post();
    wc_get_template_part('content', 'single-product');
    $data['product'] = ob_get_clean();
}
//do_action( 'woocommerce_after_main_content' );
//do_action( 'woocommerce_sidebar' );

echo $twig->render('product.html.twig', $data);