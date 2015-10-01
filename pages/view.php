<?php

$guid = (int) get_input("guid");

$ia = elgg_set_ignore_access(true);
$entity = get_entity($guid);
elgg_set_ignore_access($ia);

if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
	forward(REFERER);
}

if (!has_access_to_entity($entity) && !$entity->canEdit()) {
	register_error(elgg_echo("noaccess"));
	forward(REFERER);
}

if ($entity->canEdit()) {
	elgg_register_menu_item("title", array(
		"name" => "edit",
		"href" => "static/edit/" . $entity->getGUID(),
		"text" => elgg_echo("edit"),
		"link_class" => "elgg-button elgg-button-action",
	));
		
	elgg_register_menu_item("title", array(
		"name" => "create_subpage",
		"href" => "static/add/" . $entity->getOwnerGUID() . "?parent_guid=" . $entity->getGUID(),
		"text" => elgg_echo("static:add:subpage"),
		"link_class" => "elgg-button elgg-button-action",
	));
}

// page owner (for groups)
$owner = $entity->getOwnerEntity();
if (elgg_instanceof($owner, "group")) {
	elgg_set_page_owner_guid($owner->getGUID());
}

// show breadcrumb
$visited = array();
$append_breadcrumb = function($entity) use (&$visited, &$append_breadcrumb) {
	if (!elgg_instanceof($entity, "object", "static")) {
		return true;
	}

	// skip already visited containers to prevent loops with containers referring to each-other
	if (in_array($entity->guid, $visited)) {
		return true;
	}
	$visited[] = $entity->guid;

	elgg_push_breadcrumb($entity->title, $entity->getURL());
	$append_breadcrumb($entity->getContainerEntity());
};

$ia = elgg_set_ignore_access(true);
$append_breadcrumb($entity->getContainerEntity());
elgg_set_ignore_access($ia);

elgg_set_config("breadcrumbs", array_reverse(elgg_get_config("breadcrumbs")));
elgg_push_breadcrumb($entity->title);

// build content
$title = $entity->title;

$body = elgg_view_entity($entity, array("full_view" => true));

if ($entity->canComment()) {
	$body .= elgg_view_comments($entity, true, array("id" => "static-comments-" . $entity->getGUID()));
}

static_setup_page_menu($entity);

$page = elgg_view_layout("content", array(
	"filter" => "",
	"content" => $body,
	"title" => $title
));

echo elgg_view_page($title, $page);
