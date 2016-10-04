<?php
require_once __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/src/functions.php';

add_action('admin_enqueue_scripts', function () {
    wp_register_style('aphmau_admin', get_template_directory_uri() . '/web/stylesheets/admin.css', false, '1.0.0');
    wp_enqueue_style('aphmau_admin');
});

add_action('init', function () {
    // add video taxonomy
    register_taxonomy(
        'video_categories',
        'videos',
        array(
            'label' => __('Categories'),
            'hierarchical' => false,
        )
    );

    // remove dashboard access
    $role = get_role('subscriber');
    $role->remove_cap('read');

    // remove admin bar
	if (!current_user_can('administrator') && !is_admin()) {
	    show_admin_bar(false);
	}
});