<?php

	$entity = $vars["entity"];

	if ($vars["full_view"]) {
		$body = elgg_view("output/longtext", array("value" => $entity->description));

		if($entity->canEdit()){
			$edit_link = elgg_view("output/url", array("href" => "admin/appearance/static/new?guid=" . $entity->getGUID(), "text" => elgg_echo("edit")));
			$delete_link = elgg_view("output/confirmlink", array("href" => "action/static/delete?guid=" . $entity->getGUID(), "text" => elgg_echo("delete")));

			$actions = $edit_link . " | " . $delete_link;
			if(empty($entity->parent_guid)){
				$actions .= " | " . elgg_view("output/url", array("href" => "admin/appearance/static/new?parent_guid=" . $entity->getGUID(), "text" => elgg_echo("static:admin:create:subpage")));
			}

			$actions .= " | " . elgg_view("output/url", array("href" => "admin/appearance/static", "text" => elgg_echo("admin:appearance:static")));
			$body .= $actions;
		}

		echo "<div class='elgg-content'>" . $body . "</div>";
	} elseif (elgg_in_context("admin")) {

		$edit_link = elgg_view("output/url", array("href" => "admin/appearance/static/new?guid=" . $entity->getGUID(), "text" => elgg_view_icon("settings-alt")));
		$delete_link = elgg_view("output/confirmlink", array("href" => "action/static/delete?guid=" . $entity->getGUID(), "text" => elgg_view_icon("delete")));

		$body = "<tr rel='" . $entity->getGUID() . "'>";
		$body .= "<td>";
		if ($vars["is_child"]) {
			$body .= elgg_view_icon("drag-arrow", "mrm");
		}
		$body .= "<a href='" . $entity->getURL() . "'>" . $entity->title . "</a></td>";
		$body .= "<td class='center'>" . $edit_link . "</td>";
		$body .= "<td class='center'>" . $delete_link . "</td>";
		$body .= "</tr>";

		echo $body;
	} elseif (elgg_in_context("search")) {
		// probably search

		$title = $entity->getVolatileData('search_matched_title');
		$description = $entity->getVolatileData('search_matched_description');
		$url = $entity->getURL();

		$title = "<a href=\"$url\">$title</a>";
		$body = "<p class=\"mbn\">$title</p>$description";

		echo elgg_view_image_block("", $body);
	}
