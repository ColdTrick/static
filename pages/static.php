<?php

$guid = (int) get_input("guid");
$parent_guid = (int) get_input("parent_guid");
$page_title = get_input("page_title");
$edit = get_input("edit", false);
$new = get_input("new", false);

$content = false;

if($guid){
	$content = get_entity($guid);
} elseif(!empty($page_title)){
	if(is_numeric($page_title)){
		// support old links
		if($content = get_entity($page_title)){
			if($content->getSubtype() != "static"){
				unset($content);
			}
		}
	}

	if(!$content){
		$options = array(
			"type" => "object",
			"subtype" => "static",
			"metadata_name_value_pairs" => array("friendly_title" => $page_title),
			"limit" => 1
		);
		if($entities = elgg_get_entities_from_metadata($options)){
			$content = $entities[0];
		}
	}
}

if($content && ($content->getSubtype() == "static") && !$edit){

	// show content
	$title = $content->title;

	$body = elgg_view_entity($content, array('full_view' => true));

	$parent_guid = (int) $content->parent_guid;
	if(empty($parent_guid)){
		$parent_guid = (int) $content->getGUID();
	}

	$options = array(
		"type" => "object",
		"subtype" => "static",
		"metadata_name_value_pairs" => array("parent_guid" => $parent_guid),
		"limit" => false,
		"order_by" => "e.time_created asc"
	);

	if($menu_entities = elgg_get_entities_from_metadata($options)){
		$menu_name = "static";
		if($parent_guid != $content->parent_guid){
			elgg_register_menu_item('page', array(
				'name' => $menu_name,
				'href' => $content->getURL(),
				'text' => $content->title,
				'context' => "static"
			));
		} elseif($parent = get_entity($parent_guid)) {
			elgg_register_menu_item('page', array(
				'name' => $menu_name,
				'href' => $parent->getURL(),
				'text' => $parent->title,
				'context' => "static"
			));
		}

		$padding = strlen(count($menu_entities));
		foreach($menu_entities as $menu_item){
			$order = $menu_item->order;
			if (!$order) {
				$order = "9999" . $menu_item->time_created;
			} else {
				$order = str_pad($order, $padding, "0", STR_PAD_LEFT);
			}

			$sub_menu_name = $menu_name . "-" . $order;
			elgg_register_menu_item('page', array(
				'name' => $sub_menu_name,
				'href' => $menu_item->getURL(),
				'text' => $menu_item->title,
				'context' => "static"
			));
		}
		$page = elgg_view_layout('content', array(
				'filter' => '',
				'content' => $body,
				'title' => $title
			));
	} else {
		$page = elgg_view_layout('content', array(
				'filter' => '',
				'content' => $body,
				'title' => $title
			));
	}

	echo elgg_view_page($title, $page);
} else {
	forward();
}
