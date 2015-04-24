<?php

$entity = elgg_extract('entity', $vars);

if (elgg_extract('full_view', $vars)) {
	
	$metadata = elgg_view_menu('entity', array(
		'entity' => $vars['entity'],
		'handler' => 'static',
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
	));
	
	$params = array(
		'entity' => $entity,
		'title' => false,
		'metadata' => $metadata,
		'tags' => false
	);
	$summary = elgg_view('object/elements/summary', $params);
	
	$body = "";
	if ($entity->icontime) {
		$body .= elgg_view_entity_icon($entity, "large", array(
			"href" => false,
			"class" => "float-alt"
		));
	}
	$body .= elgg_view("output/longtext", array("value" => $entity->description));
	
	echo elgg_view('object/elements/full', array(
		'summary' => $summary,
		'body' => $body,
	));

} elseif (elgg_in_context("search")) {
	// probably search

	$title = $entity->getVolatileData("search_matched_title");
	$description = $entity->getVolatileData("search_matched_description");
	
	$title = elgg_view("output/url", array(
		"text" => $title,
		"href" => $entity->getURL(),
		"is_trusted" => true
	));
	$body = $title . "<br />" . $description;

	echo elgg_view_image_block("", $body);
	
} elseif (elgg_in_context("widgets")) {
	echo elgg_view("output/url", array(
		"text" => $entity->title,
		"href" => $entity->getURL(),
		"is_trusted" => true
	));
	
	$show_children = (bool) elgg_extract('show_children', $vars, false);
	if ($show_children) {
		$children = static_get_ordered_children($entity);
		
		if (!empty($children)) {
			$params = $vars;
			// unset some stuff to preven deadloops
			unset($params['entity']);
			unset($params['items']);
			
			echo elgg_view_entity_list($children, $params);
		}
	}
} else {

	$show_edit = elgg_extract("show_edit", $vars, true);
	
	$body = "<tr>";
	$body .= "<td>" . elgg_view("output/url", array(
		"text" => $entity->title,
		"href" => $entity->getURL(),
		"is_trusted" => true
	)) . "</td>";
	if ($show_edit && $entity->canEdit()) {
		$edit_link = elgg_view("output/url", array(
			"href" => "static/edit/" . $entity->getGUID(),
			"text" => elgg_view_icon("settings-alt")
		));
		$delete_link = elgg_view("output/confirmlink", array(
			"href" => "action/static/delete?guid=" . $entity->getGUID(),
			"text" => elgg_view_icon("delete")
		));
	
		$body .= "<td width='1%' class='center'>" . $edit_link . "</td>";
		$body .= "<td width='1%' class='center'>" . $delete_link . "</td>";
	}
	$body .= "</tr>";

	echo $body;
}
