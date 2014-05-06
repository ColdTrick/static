<?php

$guid = (int) get_input("guid");
$parent_guid = (int) get_input("parent_guid");

$entity = false;

if ($guid) {
	$entity = get_entity($guid);
}

if (elgg_instanceof($entity, "object", "static")) {

	if ($entity->canEdit()) {
		elgg_register_menu_item("title", array(
			"name" => "edit",
			"href" => "static/edit/" . $entity->getGUID(),
			"text" => elgg_echo("edit"),
			"link_class" => "elgg-button elgg-button-action",
		));
			
		elgg_register_menu_item("title", array(
			"name" => "create_subpage",
			"href" => "static/add/" . elgg_get_logged_in_user_guid() . "?parent_guid=" . $entity->getGUID(),
			"text" => elgg_echo("static:add:subpage"),
			"link_class" => "elgg-button elgg-button-action",
		));
	}
	
	// page owner (for groups)
	$owner = $entity->getOwnerEntity();
	if (elgg_instanceof($owner, "group")) {
		elgg_set_page_owner_guid($owner->getGUID());
	}
	
	// show content
	$container_entity = $entity->getContainerEntity();
	if (elgg_instanceof($container_entity, "object", "static")) {
		while(elgg_instanceof($container_entity, "object", "static")) {
			elgg_push_breadcrumb($container_entity->title, $container_entity->getURL());
			$container_entity = $container_entity->getContainerEntity();
		}
		
		elgg_set_config("breadcrumbs", array_reverse(elgg_get_config("breadcrumbs")));
		
		elgg_push_breadcrumb($entity->title);
	}
	$title = $entity->title;

	$body = elgg_view_entity($entity, array('full_view' => true));
	
	if ($entity->enable_comments == "yes") {
		$body .= elgg_view_comments($entity);
	}

	static_setup_page_menu($entity);
	
	$page = elgg_view_layout('content', array(
		'filter' => '',
		'content' => $body,
		'title' => $title
	));

	echo elgg_view_page($title, $page);
} else {
	forward();
}
