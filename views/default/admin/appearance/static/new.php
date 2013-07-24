<?php

	$guid = get_input("guid");
	$parent_guid = get_input("parent_guid");

	if($guid && ($entity = get_entity($guid))){

		if($entity->getSubtype() != "static"){
			forward();
		}
	}

	$content_guid = ELGG_ENTITIES_ANY_VALUE;
	$content_title = ELGG_ENTITIES_ANY_VALUE;
	$content_description = ELGG_ENTITIES_ANY_VALUE;
	$content_access_id = ELGG_ENTITIES_ANY_VALUE;
	$friendly_title = ELGG_ENTITIES_ANY_VALUE;

	if(isset($entity)){
		$content_guid = $entity->getGUID();
		$content_title = $entity->title;
		$content_description = $entity->description;
		$content_access_id = $entity->access_id;
		$friendly_title = $entity->friendly_title;
		$parent_guid = $entity->parent_guid;
	}

	$parent_options = array();

	$children = false;
	if ($entity) {
		$children_options = array(
				"type" => "object",
				"subtype" => "static",
				"metadata_name_value_pairs" => array("parent_guid" => $entity->getGUID()),
				"count" => true
		);
		$children = elgg_get_entities_from_metadata($children_options);
	}

	if (!$children) {
		// only allow parent change for pages without children
		$options = array(
				"type" => "object",
				"subtype" => "static",
				"limit" => false,
				"order_by" => "e.time_created asc"
		);

		$parent_guid_metadata_id = add_metastring('parent_guid');
		$zero_metadata_id = add_metastring(0);

		$options["wheres"] = array(
				"NOT EXISTS (
				SELECT 1 FROM " . elgg_get_config("dbprefix") . "metadata md
				WHERE md.entity_guid = e.guid
				AND md.name_id = " . $parent_guid_metadata_id . " AND NOT (md.value_id = " . $zero_metadata_id. "))"
		);
		if ($entity) {
			$options["wheres"][] = "e.guid <> " . $entity->guid;
		}

		if ($parent_entities = elgg_get_entities_from_metadata($options)) {
			$parent_options[0] = elgg_echo("admin:appearance:static:new:parent:top_level");

			foreach ($parent_entities as $parent) {
				$parent_options[$parent->guid] = $parent->title;
			}
		}

	}

	$form_body = elgg_view("input/hidden", array("name" => "guid", "value" => $content_guid));

	$form_body .= "<label>" . elgg_echo("title") . "</label><br />";
	$form_body .= elgg_view("input/text", array("name" => "title", "value" => $content_title)) . "<br />";
	if(!empty($friendly_title)){
		$form_body .= "<label>" . elgg_echo("admin:appearance:static:new:permalink") . "</label><br />";
		$form_body .= elgg_view("input/text", array("name" => "friendly_title", "value" => $friendly_title)) . "<br />";
	}
	$form_body .= "<label>" . elgg_echo("description") . "</label><br />";
	$form_body .= elgg_view("input/longtext", array("name" => "description", "value" => $content_description)) . "<br />";

	if (!empty($parent_options)) {
		$form_body .= "<label>" . elgg_echo("admin:appearance:static:new:parent") . "</label><br />";
		$form_body .= elgg_view("input/dropdown", array("name" => "parent_guid", "options_values" => $parent_options, "value" => $parent_guid)) . "<br />";
	} else {
		$form_body .= elgg_view("input/hidden", array("name" => "parent_guid", "value" => $parent_guid));

	}
	$form_body .= "<label>" . elgg_echo("access") . "</label><br />";
	$form_body .= elgg_view("input/access", array("name" => "access_id", "value" => $content_access_id)) . "<br />";
	$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));

	$form = elgg_view("input/form", array("body" => $form_body, "action" => "action/static/edit"));

	$page = $form;

	echo $page;
