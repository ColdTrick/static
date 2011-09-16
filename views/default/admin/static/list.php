<?php

$options = array(
    "type" => "object",
    "subtype" => "static",
    "limit" => false,
    "full_view" => false
);

$body = elgg_list_entities($options);
if(empty($body)){
    $body = elgg_echo("static:admin:empty");
}

// button to create
$params = array(
    'text' => elgg_echo("static:admin:create"),
    'href' => $CONFIG->wwwroot . "admin/static/new",
    'class' => 'elgg-button elgg-button-submit',
);
$body .= '<p>' . elgg_view('output/url', $params) . '</p>';

echo $body;
