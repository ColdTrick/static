<?php

	global $CONFIG;

	$options = array(
			"type" => "object",
			"subtype" => "static",
			"limit" => false,
			"order_by" => "e.time_created asc");

	$parent_guid_metadata_id = get_metastring_id('parent_guid');
	$zero_metadata_id = get_metastring_id(0);
	if($parent_guid_metadata_id){
		$options["wheres"] = array(
			"NOT EXISTS (
			SELECT 1 FROM " . $CONFIG->dbprefix . "metadata md
			WHERE md.entity_guid = e.guid
				AND md.name_id = " . $parent_guid_metadata_id . " AND NOT (md.value_id = " . $zero_metadata_id. "))"
		);
	}

	if ($entities = elgg_get_entities($options)) {
		$body = "<table class='elgg-table-alt' id='static-pages-list'>";
		$body .= "<thead><tr>";
		$body .= "<th>" . elgg_echo("title") . "</th>";
		$body .= "<th class='center'>" . elgg_echo("edit") . "</th>";
		$body .= "<th class='center'>" . elgg_echo("delete") . "</th>";
		$body .= "</tr></thead>";

		foreach ($entities as $entity) {

			$body .= elgg_view_entity($entity);

			$options = array(
				"type" => "object",
				"subtype" => "static",
				"metadata_name_value_pairs" => array("parent_guid" => $entity->getGUID()),
				"limit" => false,
				"order_by" => "e.time_created asc"
			);

			if($children = elgg_get_entities_from_metadata($options)){
				$ordered_children = array();
				$body .= "<tbody class='static-children-sortable' rel='" . $entity->getGUID() . "'>";

				foreach($children as $child_entity){
					$order = $child_entity->order;
					if (!$order) {
						$order = $child_entity->time_created;
					}

					while (array_key_exists($order, $ordered_children)) {
						$order++;
					}

					$ordered_children[$order] = $child_entity;
				}
				ksort($ordered_children);

				foreach($ordered_children as $child_entity){
					$body .= elgg_view_entity($child_entity, array("is_child" => true));
				}

				$body .= "</tbody>";
			}

		}
		$body .= "</table>";
	}



	if(empty($body)){
		$body = elgg_echo("static:admin:empty");
	}

	$create_button = elgg_view("output/url", array("href" => $CONFIG->wwwroot . "admin/appearance/static/new", "text" => elgg_echo("static:admin:create"), "class" => "elgg-button elgg-button-action"));

	$page_data = $body . "<br /><br />" . $create_button;

	echo $page_data;