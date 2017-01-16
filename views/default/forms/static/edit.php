<?php

$parent_guid = (int) get_input('parent_guid');
$entity = elgg_extract('entity', $vars);
$owner = elgg_extract('owner', $vars);

$content_guid = ELGG_ENTITIES_ANY_VALUE;
$content_title = ELGG_ENTITIES_ANY_VALUE;
$content_description = ELGG_ENTITIES_ANY_VALUE;
$content_access_id = ACCESS_DEFAULT;
$friendly_title = ELGG_ENTITIES_ANY_VALUE;
$content_enable_comments = 'no';
$content_moderators = ELGG_ENTITIES_NO_VALUE;
$content_owner_guid = $owner->getGUID();

$comment_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes'),
];

if ($entity) {
	$content_guid = $entity->getGUID();
	$content_title = $entity->title;
	$content_description = $entity->description;
	$content_access_id = $entity->access_id;
	$friendly_title = $entity->getFriendlyTitle();
	$parent_guid = $entity->parent_guid;
	
	$content_enable_comments = $entity->enable_comments;
	$content_moderators = $entity->moderators;
	$content_owner_guid = $entity->getOwnerGUID();
	
} else {
	if (!empty($parent_guid)) {
		$parent = get_entity($parent_guid);
		if ($parent) {
			$content_access_id = $parent->access_id;
		}
	}
}

// build the form
$form_body = elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('title'),
	'name' => 'title',
	'value' => elgg_get_sticky_value('static', 'title', $content_title),
	'required' => true,
]);

if (!empty($entity)) {
	$form_body .= elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('static:new:permalink'),
		'name' => 'friendly_title',
		'value' => elgg_get_sticky_value('static', 'friendly_title', $friendly_title),
		'required' => true,
	]);
}

$form_body .= '<div class="mbm"><label>' . elgg_echo('static:new:thumbnail') . '</label><br />';
$form_body .= elgg_view('input/file', ['name' => 'thumbnail']);
if ($entity && $entity->icontime) {
	$form_body .= elgg_view('input/checkbox', [
		'name' => 'remove_thumbnail',
		'value' => '1',
		'label' => elgg_echo('static:new:remove_thumbnail'),
	]);
}
$form_body .= '</div>';

$form_body .= elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('description'),
	'name' => 'description',
	'value' => elgg_get_sticky_value('static', 'description', $content_description),
	'required' => true,
]);

$form_body .= elgg_view_field([
	'#type' => 'static/parent',
	'#label' => elgg_echo('static:new:parent'),
	'name' => 'parent_guid',
	'value' => elgg_get_sticky_value('static', 'parent_guid', $parent_guid),
	'owner' => $owner,
	'entity' => $entity,
]);

$form_body .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('static:new:comment'),
	'name' => 'enable_comments',
	'value' => elgg_get_sticky_value('static', 'enable_comments', $content_enable_comments),
	'options_values' => $comment_options,
]);

$form_body .= elgg_view_field([
	'#type' => 'userpicker',
	'#label' => elgg_echo('static:new:moderators'),
	'name' => 'moderators',
	'values' => elgg_get_sticky_value('static', 'moderators', $content_moderators),
]);

$form_body .= elgg_view_field([
	'#type' => 'access',
	'#label' => elgg_echo('access'),
	'name' => 'access_id',
	'value' => elgg_get_sticky_value('static', 'access_id', $content_access_id),
]);

$form_body .= elgg_view('input/hidden', [
	'name' => 'guid',
	'value' => $content_guid,
]);
$form_body .= elgg_view('input/hidden', [
	'name' => 'owner_guid',
	'value' => $content_owner_guid,
]);

echo $form_body;

$footer = '';
if ($entity) {
	$footer .= elgg_view('output/url', [
		'href' => 'action/static/delete?guid=' . $entity->getGUID(),
		'text' => elgg_echo('delete'),
		'class' => 'elgg-button elgg-button-delete float-alt',
		'confirm' => true,
	]);
}
$footer .= elgg_view('input/submit', ['value' => elgg_echo('save')]);

elgg_set_form_footer($footer);

// clear sticky form
elgg_clear_sticky_form('static');
