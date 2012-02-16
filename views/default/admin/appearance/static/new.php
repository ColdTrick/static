<?php 

	global $CONFIG; 

	$guid = get_input("guid");
	$parent_guid = get_input("parent_guid");
	
	if($guid && ($entity = get_entity($guid))){
		
		if($entity->getSubtype() != "static"){
			forward();
		}
	}
	if($entity){
		$content_guid = $entity->getGUID();
		$content_title = $entity->title;
		$content_description = $entity->description;
		$content_access_id = $entity->access_id;
		$friendly_title = $entity->friendly_title;
	}

	$form_body .= elgg_view("input/hidden", array("name" => "guid", "value" => $content_guid));
	$form_body .= elgg_view("input/hidden", array("name" => "parent_guid", "value" => $parent_guid));
	$form_body .= "<label>" . elgg_echo("title") . "</label><br />";
	$form_body .= elgg_view("input/text", array("name" => "title", "value" => $content_title)) . "<br />";
	if(!empty($friendly_title)){
		$form_body .= "<label>" . elgg_echo("admin:appearance:static:new:permalink") . "</label><br />";
		$form_body .= elgg_view("input/text", array("name" => "friendly_title", "value" => $friendly_title)) . "<br />";
	}
	$form_body .= "<label>" . elgg_echo("description") . "</label><br />";
	$form_body .= elgg_view("input/longtext", array("name" => "description", "value" => $content_description)) . "<br />";
	$form_body .= "<label>" . elgg_echo("access") . "</label><br />";
	$form_body .= elgg_view("input/access", array("name" => "access_id", "value" => $content_access_id)) . "<br />";
	$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
	
	$form = elgg_view("input/form", array("body" => $form_body, "action" => $CONFIG->wwwroot . "action/static/edit"));
	
	$page = $form;
			
	echo $page;
