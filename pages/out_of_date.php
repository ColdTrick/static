<?php

admin_gatekeeper();

if (!static_out_of_date_enabled()) {
	forward(REFERER);
}

$days = (int) elgg_get_plugin_setting("out_of_date_days", "static");

$options = array(
	"type" => "object",
	"subtype" => "static",
	"limit" => false,
	"modified_time_upper" => time() - ($days * 24 * 60 * 60),
	"order_by" => "e.time_updated DESC"
);

$batch = new ElggBatch("elgg_get_entities", $options);
$rows = array();
foreach ($batch as $entity) {
	$rows[] = elgg_view_entity($entity, array("full_view" => false));
}

if (!empty($rows)) {
	$body = "<table class='elgg-table-alt' id='static-pages-list'>";
	$body .= "<thead><tr>";
	$body .= "<th>" . elgg_echo("title") . "</th>";
	$body .= "<th class='center'>" . elgg_echo("edit") . "</th>";
	$body .= "<th class='center'>" . elgg_echo("delete") . "</th>";
	$body .= "</tr></thead>";
	$body .= "<tr>";
	$body .= implode("</tr><tr>", $rows);
	$body .= "</tr>";
	$body .= "</table>";
} else {
	$body = elgg_view("output/longtext", array("value" => elgg_echo("static:out_of_date:none")));
}

$title_text = elgg_echo("static:out_of_date:title");
$filter = elgg_view("page/layouts/elements/filter");

$page_data = elgg_view_layout("one_column", array(
	"title" => $title_text,
	"content" => $filter . $body
));

echo elgg_view_page($title_text, $page_data);