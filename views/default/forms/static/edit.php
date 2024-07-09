<?php

$entity = elgg_extract('entity', $vars);
$owner = elgg_extract('owner', $vars);

echo elgg_view('entity/edit/header', [
	'entity_type' => 'object',
	'entity_subtype' => \StaticPage::SUBTYPE,
	'entity' => $entity,
]);

// build the form
$fields = elgg()->fields->get('object', \StaticPage::SUBTYPE);
foreach ($fields as $field) {
	$name = elgg_extract('name', $field);
	
	switch ($name) {
		case 'friendly_title':
			if (!$entity instanceof \StaticPage) {
				// not during creation
				continue(2);
			}
			break;
		case 'parent_guid':
			$field['owner'] = $owner;
			$field['entity'] = $entity;
			break;
		case 'access_id':
			$field['entity'] = $entity;
			break;
	}
	
	if (elgg_extract('#type', $field) === 'checkbox') {
		$value = elgg_extract('value', $field);
		$field['checked'] = isset($value) ? $value === elgg_extract($name, $vars) : null;
	} else {
		$field['value'] = elgg_extract($name, $vars);
	}
	
	echo elgg_view_field($field);
}

if ($entity instanceof \StaticPage) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => $entity->guid,
	]);
}

echo elgg_view_field([
	'#type' => 'container_guid',
	'name' => 'owner_guid',
	'entity_type' => 'object',
	'entity_subtype' => \StaticPage::SUBTYPE,
	'entity' => $entity,
	'value' => $owner->guid,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
