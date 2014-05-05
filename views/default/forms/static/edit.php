<?php


$parent_guid = (int) get_input("parent_guid");
$entity = $vars["entity"];

$content_guid = ELGG_ENTITIES_ANY_VALUE;
$content_title = ELGG_ENTITIES_ANY_VALUE;
$content_description = ELGG_ENTITIES_ANY_VALUE;
$content_access_id = ACCESS_DEFAULT;
$friendly_title = ELGG_ENTITIES_ANY_VALUE;
$content_enable_comments = "no";
$content_moderators = ELGG_ENTITIES_NO_VALUE;

if ($entity) {
	$content_guid = $entity->getGUID();
	$content_title = $entity->title;
	$content_description = $entity->description;
	$content_access_id = $entity->access_id;
	$friendly_title = $entity->friendly_title;
	$parent_guid = $entity->getContainerGUID();
	$content_enable_comments = $entity->enable_comments;
	$content_moderators = $entity->moderators;
} else {
	if (!empty($parent_guid)) {
		$parent = get_entity($parent_guid);
		if ($parent) {
			$content_access_id = $parent->access_id;
		}
	}
}

$comment_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

$parent_options = array();

$options = array(
	"type" => "object",
	"subtype" => "static",
	"container_guid" => elgg_get_site_entity()->getGUID(),
	"limit" => false,
);

if ($parent_entities = elgg_get_entities_from_metadata($options)) {
	$parent_options[0] = elgg_echo("static:new:parent:top_level");

	foreach ($parent_entities as $parent) {
		$parent_options[$parent->getGUID()] = $parent->title;
	}
	
	if ($entity) {
		unset($parent_options[$entity->guid]);
	}
}

$form_body = elgg_view("input/hidden", array("name" => "guid", "value" => $content_guid));

$form_body .= "<div><label>" . elgg_echo("title") . "</label><br />";
$form_body .= elgg_view("input/text", array("name" => "title", "value" => $content_title)) . "</div>";

if (!empty($friendly_title)) {
	$form_body .= "<div><label>" . elgg_echo("static:new:permalink") . "</label><br />";
	$form_body .= elgg_view("input/text", array("name" => "friendly_title", "value" => $friendly_title)) . "</div>";
}

$form_body .= "<div><label>" . elgg_echo("description") . "</label><br />";
$form_body .= elgg_view("input/longtext", array("name" => "description", "value" => $content_description)) . "</div>";

if (!empty($parent_options)) {
	$form_body .= "<div><label>" . elgg_echo("static:new:parent") . "</label><br />";
	$form_body .= elgg_view("input/dropdown", array("name" => "parent_guid", "options_values" => $parent_options, "value" => $parent_guid)) . "</div>";
} else {
	$form_body .= elgg_view("input/hidden", array("name" => "parent_guid", "value" => $parent_guid));
}

$form_body .= "<div><label>" . elgg_echo("static:new:comment") . "</label><br />";
$form_body .= elgg_view("input/select", array("name" => "enable_comments", "value" => $content_enable_comments, "options_values" => $comment_options)) . "</div>";

$form_body .= "<div><label>" . elgg_echo("static:new:moderators") . "</label><br />";
$form_body .= elgg_view("input/userpicker", array("name" => "moderators", "values" => $content_moderators));

$form_body .= "<div><label>" . elgg_echo("access") . "</label><br />";
$form_body .= elgg_view("input/access", array("name" => "access_id", "value" => $content_access_id)) . "</div>";

$form_body .= "<div class='elgg-foot mtm'>";
if ($entity) {
	$form_body .= elgg_view("output/confirmlink", array("href" => "action/static/delete?guid=" . $entity->getGUID(), "text" => elgg_echo("delete"), "class" => "elgg-button elgg-button-delete float-alt"));
}
$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
$form_body .= "</div>";

echo $form_body;
