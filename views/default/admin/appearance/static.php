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
		
	$entities = elgg_get_entities($options);
	if($entities){
		$body = "<table class='elgg-table'>";
		$body .= "<tr>";
		$body .= "<th>" . elgg_echo("title") . "</th>";
		$body .= "<th class='center'>" . elgg_echo("edit") . "</th>";
		$body .= "<th class='center'>" . elgg_echo("delete") . "</th>";
		$body .= "</tr>";
		
		foreach($entities as $entity){
			
			$edit_link = elgg_view("output/url", array("href" => $vars["url"] . "admin/appearance/static/new?guid=" . $entity->getGUID(), "text" => elgg_view_icon("settings-alt")));
			$delete_link = elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/static/delete?guid=" . $entity->getGUID(), "text" => elgg_view_icon("delete")));
			
			$body .= "<tr>";
			$body .= "<td><a href='" . $entity->getURL() . "'>" . $entity->title . "</a></td>";
			$body .= "<td class='center'>" . $edit_link . "</td>";
			$body .= "<td class='center'>" . $delete_link . "</td>";
			$body .= "</tr>";
			
			$options = array(
						"type" => "object",
						"subtype" => "static",
						"metadata_name_value_pairs" => array("parent_guid" => $entity->getGUID()),
						"limit" => false,
						"order_by" => "e.time_created asc"
			);
			
			if($children = elgg_get_entities_from_metadata($options)){
				foreach($children as $child_entity){
					$edit_link = elgg_view("output/url", array("href" => $vars["url"] . "admin/appearance/static/new?guid=" . $child_entity->getGUID(), "text" => elgg_view_icon("settings-alt")));
					$delete_link = elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/static/delete?guid=" . $child_entity->getGUID(), "text" => elgg_view_icon("delete")));
						
					$body .= "<tr>";
					$body .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $child_entity->getURL() . "'>" . $child_entity->title . "</a></td>";
					$body .= "<td class='center'>" . $edit_link . "</td>";
					$body .= "<td class='center'>" . $delete_link . "</td>";
					$body .= "</tr>";
				}
			}
			
			$create_sub_link = elgg_view("output/url", array("href" => $vars["url"] . "admin/appearance/static/new?parent_guid=" . $entity->getGUID(), "text" => elgg_echo("static:admin:create:subpage")));
				
			
			$body .= "<tr>";
			$body .= "<td colspan='3'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $create_sub_link . "</td>";
			$body .= "</tr>";
			
						
		}
		$body .= "</table>";
	}
	
	
	
	if(empty($body)){
		$body = elgg_echo("static:admin:empty");
	}	
	
	$create_button = elgg_view("output/url", array("href" => $CONFIG->wwwroot . "admin/appearance/static/new", "text" => elgg_echo("static:admin:create"), "class" => "elgg-button elgg-button-action"));
	
	$page_data = $body . "<br /><br />" . $create_button;

	echo $page_data;