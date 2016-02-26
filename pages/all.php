<?php

elgg_gatekeeper();

$site = elgg_get_site_entity();

$options = [
	'type' => 'object',
	'subtype' => 'static',
	'limit' => false,
	'container_guid' => $site->getGUID(),
	'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid'],
	'order_by' => 'oe.title asc',
];

$ia = elgg_set_ignore_access(true);
$entities = elgg_get_entities($options);
elgg_set_ignore_access($ia);

if ($entities) {
	
	elgg_require_js('static/list_reorder');
	
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
	
		if (!has_access_to_entity($entity) && !$entity->canEdit()) {
			continue;
		}
	
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = elgg_view_entity($entity, ['full_view' => false]);
	}
	ksort($ordered_entities);
	
	$table_params = [
		'id' => 'static-pages-list',
		'class' => ['elgg-table-alt'],
		'data-container-guid' => $site->getGUID()
	];
	
	$header_row = elgg_format_element('th', [], elgg_echo('title'));
	$header_row .= elgg_format_element('th', ['class' => 'center'], elgg_echo('edit'));
	$header_row .= elgg_format_element('th', ['class' => 'center'], elgg_echo('delete'));
	
	$table_data = elgg_format_element('thead', [], elgg_format_element('tr', [], $header_row));
	$table_data .= implode($ordered_entities);

	$body = '';
	if ($site->canEdit()) {
		$body .= elgg_format_element('div', ['class' => 'mbm'], elgg_echo('static:list:info'));
		$table_params['class'][] = 'static-reorder';
	}
	$body .= elgg_format_element('table', $table_params, $table_data);
} else {
	$body = elgg_echo('static:admin:empty');
}

$filter = '';
if (static_out_of_date_enabled()) {
	$filter = elgg_view('page/layouts/elements/filter');
}

if (can_write_to_container(elgg_get_logged_in_user_guid(), $site->getGUID(), 'object', 'static')) {
	elgg_register_title_button();
}

$title_text = elgg_echo('static:all');
$body = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $filter . $body,
]);

echo elgg_view_page($title_text, $body);
