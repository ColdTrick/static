<?php

$parent_guid = (int) get_input("parent_guid");
$entity = elgg_extract("entity", $vars);
$owner = elgg_extract("owner", $vars);

$content_guid = ELGG_ENTITIES_ANY_VALUE;
$content_title = ELGG_ENTITIES_ANY_VALUE;
$content_description = ELGG_ENTITIES_ANY_VALUE;
$content_access_id = ACCESS_DEFAULT;
$friendly_title = ELGG_ENTITIES_ANY_VALUE;
$content_enable_comments = "no";
$content_moderators = ELGG_ENTITIES_NO_VALUE;
$content_owner_guid = $owner->getGUID();

$comment_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

$parent_options = static_get_parent_options($owner->getGUID());

if ($entity) {
	$content_guid = $entity->getGUID();
	$content_title = $entity->title;
	$content_description = $entity->description;
	$content_access_id = $entity->access_id;
	$friendly_title = $entity->friendly_title;
	if (empty($friendly_title)) {
		// this sometimes happens, prefill with new friendly title
		$friendly_title = static_make_friendly_title($content_title);
	}
	$parent_guid = $entity->getContainerGUID();
	$content_enable_comments = $entity->enable_comments;
	$content_moderators = $entity->moderators;
	$content_owner_guid = $entity->getOwnerGUID();
	
	unset($parent_options[$entity->getGUID()]);
} else {
	if (!empty($parent_guid)) {
		$parent = get_entity($parent_guid);
		if ($parent) {
			$content_access_id = $parent->access_id;
		}
	}
}

// build the form
$form_body = "<div><label>" . elgg_echo("title") . "</label><br />";
$form_body .= elgg_view("input/text", array(
	"name" => "title",
	"value" => elgg_get_sticky_value("static", "title", $content_title),
	"required" => true
)) . "</div>";

if (!empty($entity)) {
	$form_body .= "<div><label>" . elgg_echo("static:new:permalink") . "</label><br />";
	$form_body .= elgg_view("input/text", array(
		"name" => "friendly_title",
		"value" => elgg_get_sticky_value("static", "friendly_title", $friendly_title),
		"required" => true
	)) . "</div>";
}

$form_body .= "<div class='mbm'><label>" . elgg_echo("static:new:thumbnail") . "</label><br />";
$form_body .= elgg_view("input/file", array("name" => "thumbnail"));
if ($entity && $entity->icontime) {
	$form_body .= elgg_view("input/checkbox", array(
		"name" => "remove_thumbnail",
		"value" => "1",
		"label" => elgg_echo("static:new:remove_thumbnail")
	));
}
$form_body .= "</div>";

$form_body .= "<div><label>" . elgg_echo("description") . "</label><br />";
$form_body .= elgg_view("input/longtext", array(
	"name" => "description",
	"value" => elgg_get_sticky_value("static", "description", $content_description),
	"required" => true
)) . "</div>";

if (!empty($parent_options)) {
	$form_body .= "<div><label>" . elgg_echo("static:new:parent") . "</label><br />";
	$form_body .= elgg_view("input/dropdown", array(
		"name" => "parent_guid",
		"options_values" => $parent_options,
		"value" => elgg_get_sticky_value("static", "parent_guid", $parent_guid)
	)) . "</div>";
} else {
	$form_body .= elgg_view("input/hidden", array(
		"name" => "parent_guid",
		"value" => $parent_guid
	));
}

$form_body .= "<div><label>" . elgg_echo("static:new:comment") . "</label><br />";
$form_body .= elgg_view("input/select", array(
	"name" => "enable_comments",
	"value" => elgg_get_sticky_value("static", "enable_comments", $content_enable_comments),
	"options_values" => $comment_options
)) . "</div>";

$form_body .= "<div><label>" . elgg_echo("static:new:moderators") . "</label><br />";
$form_body .= elgg_view("input/userpicker", array(
	"name" => "moderators",
	"values" => elgg_get_sticky_value("static", "moderators", $content_moderators)
));

$form_body .= "<div><label>" . elgg_echo("access") . "</label><br />";
$form_body .= elgg_view("input/access", array(
	"name" => "access_id",
	"value" => elgg_get_sticky_value("static", "access_id", $content_access_id)
)) . "</div>";

$form_body .= "<div class='elgg-foot mtm'>";
$form_body .= elgg_view("input/hidden", array("name" => "guid", "value" => $content_guid));
$form_body .= elgg_view("input/hidden", array("name" => "owner_guid", "value" => $content_owner_guid));
if ($entity) {
	$form_body .= elgg_view("output/url", array(
		"href" => "action/static/delete?guid=" . $entity->getGUID(),
		"text" => elgg_echo("delete"),
		"class" => "elgg-button elgg-button-delete float-alt",
		"confirm" => true
	));
}
$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
$form_body .= "</div>";

echo $form_body;

// clear sticky form
elgg_clear_sticky_form("static");
