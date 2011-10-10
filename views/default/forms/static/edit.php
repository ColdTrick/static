<?php

$entity = $vars['entity'];
if ($entity) {
    $guid      = $entity->getGUID();
    $title     = $entity->title;
    $desc      = $entity->description;
    $access_id = $entity->access_id;
} else {
    $guid = $title = $desc = $access_id = '';
}

$form_body .= elgg_view("input/hidden", array("name" => "guid", "value" => $guid));

$form_body .= "<div><label>" . elgg_echo("title") . "</label><br />";
$form_body .= elgg_view("input/text", array("name" => "title", "value" => $title)) . "</div>";

$form_body .= "<div><label>" . elgg_echo("description") . "</label><br />";
$form_body .= elgg_view("input/longtext", array("name" => "description", "value" => $desc)) . "</div>";

$form_body .= "<div><label>" . elgg_echo("access") . "</label><br />";
$form_body .= elgg_view("input/access", array("name" => "access_id", "value" => $access_id)) . "</div>";

$form_body .= "<p>" . elgg_view("input/submit", array("value" => elgg_echo("save")));
$form_body .= elgg_view('output/confirmlink', array('text' => elgg_echo("cancel"), 'href' => "admin/static/list")) . '</p>';

echo $form_body;