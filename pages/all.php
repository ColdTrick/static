<?php

elgg_gatekeeper();

$options = array(
	"type" => "object",
	"subtype" => "static",
	"limit" => false,
	"container_guid" => elgg_get_site_entity()->getGUID()
);

$entities = elgg_get_entities($options);
if ($entities) {
	$body = "<table class='elgg-table-alt' id='static-pages-list'>";
	$body .= "<thead><tr>";
	$body .= "<th>" . elgg_echo("title") . "</th>";
	$body .= "<th class='center'>" . elgg_echo("edit") . "</th>";
	$body .= "<th class='center'>" . elgg_echo("delete") . "</th>";
	$body .= "</tr></thead>";

	foreach ($entities as $entity) {

		$body .= elgg_view_entity($entity, array("full_view" => false));

	}
	$body .= "</table>";
}

if (empty($body)) {
	$body = elgg_echo("static:admin:empty");
}

elgg_register_title_button();

$title_text = elgg_echo("static:all");
$body = elgg_view_layout('one_column', array('content' => $body, "title" => $title_text));

echo elgg_view_page($title_text, $body);
