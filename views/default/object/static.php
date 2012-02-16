<?php 
	
	$entity = $vars["entity"];

	$options = array(
		"type" => "object",
		"subtype" => "static",
		"metadata_name_value_pairs" => array("parent_guid" => $entity->getGUID()),
		"limit" => false,
		"order_by" => "e.time_created asc"
		);
	
	if($children = elgg_list_entities_from_metadata($options)){
		$children = "<div class='static_list_children'>" . $children . "</div>";
	}
	
	$edit_link = elgg_view("output/url", array("href" => $vars["url"] . "admin/appearance/static/new?guid=" . $entity->getGUID(), "text" => elgg_echo("edit")));
	$delete_link = elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/static/delete?guid=" . $entity->getGUID(), "text" => elgg_echo("delete")));
	
	if(empty($entity->parent_guid)){
		$create_sub_link = " | " . elgg_view("output/url", array("href" => $vars["url"] . "admin/appearance/static/new?parent_guid=" . $entity->getGUID(), "text" => elgg_echo("static:admin:create:subpage")));
	}
	
	$body = "<div>";
	$body .= "<div class='static_list_entity'><a href='" . $entity->getURL() . "'>" . $entity->title . "</a><span class='static_object_actions'> [ " . $edit_link . $create_sub_link . " | " . $delete_link . " ]</span></div>";
	$body .= $children;
	$body .= "</div>";
	echo $body;
