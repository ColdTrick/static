<?php

gatekeeper();

$site = elgg_get_site_entity();

$options = array(
	"type" => "object",
	"subtype" => "static",
	"limit" => false,
	"container_guid" => $site->getGUID(),
	"joins" => array("JOIN " . elgg_get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
	"order_by" => "oe.title asc"
);

$ia = elgg_set_ignore_access(true);
$entities = elgg_get_entities($options);
elgg_set_ignore_access($ia);

if ($entities) {
	$body = "<table class='elgg-table-alt' id='static-pages-list'>";
	$body .= "<thead><tr>";
	$body .= "<th>" . elgg_echo("title") . "</th>";
	$body .= "<th class='center'>" . elgg_echo("edit") . "</th>";
	$body .= "<th class='center'>" . elgg_echo("delete") . "</th>";
	$body .= "</tr></thead>";

	foreach ($entities as $entity) {
		
		if (!has_access_to_entity($entity) && !$entity->canEdit()) {
			continue;
		}
		
		$body .= elgg_view_entity($entity, array("full_view" => false));
		
	}
	$body .= "</table>";
}

if (empty($body)) {
	$body = elgg_echo("static:admin:empty");
}

$filter = "";
if (static_out_of_date_enabled()) {
	$filter = elgg_view("page/layouts/elements/filter");
}

if (can_write_to_container(elgg_get_logged_in_user_guid(), $site->getGUID(), "object", "static")) {
	elgg_register_title_button();
}

$title_text = elgg_echo("static:all");
$body = elgg_view_layout("one_column", array(
	"title" => $title_text,
	"content" => $filter . $body
));

echo elgg_view_page($title_text, $body);
