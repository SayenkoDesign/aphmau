<?php
require_once __DIR__.'/app/bootstrap.php';
$twig = $container->get("twig.environment");

$data = [];
$panels = [];
while(have_rows('content_builder')) {
    the_row();
    $panels[] = $twig->render('panels/'.get_row_layout().'.html.twig', $data);
}
$data['panels'] = $panels;

$data['teasers'] = [];
while(have_posts()) {
    the_post();
    $data['teasers'][] = $twig->render('teasers/video.html.twig', $data);
}

if(is_search()) {
    $data['title'] = 'Search Results';
}
echo $twig->render('videos.html.twig', $data);
