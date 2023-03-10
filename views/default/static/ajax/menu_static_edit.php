<?php
$root_entity = get_entity((int) get_input('guid'));

echo elgg_view_menu('static_edit', [
	'sort_by' => 'priority',
	'root_entity' => $root_entity,
]);
