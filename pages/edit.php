<?php

elgg_gatekeeper();

$guid = get_input("guid");
$body_vars = array();
$sidebar = "";

elgg_push_breadcrumb(elgg_echo("static:all"), "static/all");

if ($guid) {
	$entity = get_entity($guid);
	if (!elgg_instanceof($entity, "object", "static")) {
		forward(REFERER);
	}
	
	$body_vars["entity"] = $entity;

	elgg_push_breadcrumb($entity->title, $entity->getURL());

	$sidebar = elgg_view("static/sidebar/revisions", array("entity" => $entity));
}

$body = elgg_view_form("static/edit", array("class" => "elgg-form-alt"), $body_vars);

$title_text = elgg_echo("static:edit");

$body = elgg_view_layout('one_sidebar', array('content' => $body, "title" => $title_text, "sidebar" => $sidebar));

echo elgg_view_page($title_text, $body);
