<?php
require_once __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/src/functions.php';

add_action( 'admin_enqueue_scripts', function () {
    wp_register_style( 'aphmau_admin', get_template_directory_uri() . '/web/stylesheets/admin.css', false, '1.0.0' );
    wp_enqueue_style( 'aphmau_admin' );
} );