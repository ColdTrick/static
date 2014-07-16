<?php

elgg_gatekeeper();

$guid = (int) get_input("guid");
$body_vars = array();
$sidebar = "";
$page_owner = elgg_get_page_owner_entity();
$site = elgg_get_site_entity();

if (!elgg_instanceof($page_owner, "group")) {
	elgg_set_page_owner_guid($site->getGUID());
	$page_owner = $site;
}
$body_vars["owner"] = $page_owner;

elgg_push_breadcrumb(elgg_echo("static:all"), "static/all");

if ($guid) {
	elgg_entity_gatekeeper($guid, "object", "static");
	
	$entity = get_entity($guid);
	if (!$entity->canEdit()) {
		forward(REFERER);
	}
	
	$body_vars["entity"] = $entity;
	
	elgg_set_page_owner_guid($entity->getOwnerGUID());
	$page_owner = elgg_get_page_owner_entity();
	$body_vars["owner"] = $page_owner;
	
	$sidebar = elgg_view("static/sidebar/revisions", array("entity" => $entity));
}

if (elgg_instanceof($page_owner, "group")) {
	elgg_push_breadcrumb(elgg_echo("static:groups:title"), "static/group/" . $page_owner->getGUID());
}

if (!empty($entity)) {
	elgg_push_breadcrumb($entity->title, $entity->getURL());
}


$body = elgg_view_form("static/edit", array("class" => "elgg-form-alt", "enctype" => "multipart/form-data"), $body_vars);

$title_text = elgg_echo("static:edit");

$body = elgg_view_layout('one_sidebar', array('content' => $body, "title" => $title_text, "sidebar" => $sidebar));

echo elgg_view_page($title_text, $body);
