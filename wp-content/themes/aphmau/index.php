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

echo $twig->render('basic.html.twig', $data);
