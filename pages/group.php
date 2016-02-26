<?php
/**
 * List the top statics of this group
 */

elgg_gatekeeper();
elgg_group_gatekeeper();

$group = elgg_get_page_owner_entity();
if (empty($group) || !elgg_instanceof($group, 'group')) {
	forward(REFERER);
}

if (!static_group_enabled($group)) {
	forward(REFERER);
}

$can_write = $group->canWriteToContainer(0, 'object', 'static');

$options = [
	'type' => 'object',
	'subtype' => 'static',
	'limit' => false,
	'container_guid' => $group->getGUID(),
	'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid'],
	'order_by' => 'oe.title asc',
];

if ($can_write) {
	$ia = elgg_set_ignore_access(true);
}

$entities = elgg_get_entities($options);

if ($can_write) {
	elgg_set_ignore_access($ia);
}

if ($entities) {
	
	elgg_require_js('static/list_reorder');
	
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = elgg_view_entity($entity, array('full_view' => false));
	}
	
	ksort($ordered_entities);
	
	$table_params = [
		'id' => 'static-pages-list',
		'class' => ['elgg-table-alt'],
		'data-container-guid' => $group->getGUID(),
	];
	
	$header_row = elgg_format_element('th', [], elgg_echo('title'));
	if ($can_write) {
		$header_row .= elgg_format_element('th', ['class' => 'center'], elgg_echo('edit'));
		$header_row .= elgg_format_element('th', ['class' => 'center'], elgg_echo('delete'));
	}
	
	$table_data = elgg_format_element('thead', [], elgg_format_element('tr', [], $header_row));
	$table_data .= implode($ordered_entities);
	
	$body = '';
	if ($group->canEdit()) {
		$body .= elgg_format_element('div', ['class' => 'mbm'], elgg_echo('static:list:info'));
		$table_params['class'][] = 'static-reorder';
	}
	$body .= elgg_format_element('table', $table_params, $table_data);
} else {
	$body = elgg_echo('static:admin:empty');
}

if ($can_write) {
	elgg_register_title_button();
}

$filter = '';
if (static_out_of_date_enabled()) {
	$filter = elgg_view('page/layouts/elements/filter');
}

$title_text = elgg_echo('static:groups:title');
$body = elgg_view_layout('content', [
	'content' => $body,
	'title' => $title_text,
	'filter' => $filter,
]);

echo elgg_view_page($title_text, $body);
