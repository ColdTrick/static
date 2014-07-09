<?php

$entity = $vars["entity"];

if ($vars["full_view"]) {
	$body = elgg_view("output/longtext", array("value" => $entity->description));

	echo "<div class='elgg-content'>" . $body . "</div>";

} elseif (elgg_in_context("search")) {
	// probably search

	$title = $entity->getVolatileData("search_matched_title");
	$description = $entity->getVolatileData("search_matched_description");
	
	$title = elgg_view("output/url", array("text" => $title, "href" => $entity->getURL(), "is_trusted" => true));
	$body = $title . "<br />" . $description;

	echo elgg_view_image_block("", $body);
} else {

	$show_edit = elgg_extract("show_edit", $vars, true);
	
	$body = "<tr>";
	$body .= "<td>" . elgg_view("output/url", array("text" => $entity->title, "href" => $entity->getURL(), "is_trusted" => true)) . "</td>";
	if ($show_edit && $entity->canEdit()) {
		$edit_link = elgg_view("output/url", array("href" => "static/edit/" . $entity->getGUID(), "text" => elgg_view_icon("settings-alt")));
		$delete_link = elgg_view("output/confirmlink", array("href" => "action/static/delete?guid=" . $entity->getGUID(), "text" => elgg_view_icon("delete")));
	
		$body .= "<td width='1%' class='center'>" . $edit_link . "</td>";
		$body .= "<td width='1%' class='center'>" . $delete_link . "</td>";
	}
	$body .= "</tr>";

	echo $body;
}
