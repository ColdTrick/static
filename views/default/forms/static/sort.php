<?php

use Elgg\Exceptions\Http\EntityNotFoundException;
use Elgg\Exceptions\Http\EntityPermissionsException;

elgg_ajax_gatekeeper();

$group = get_entity((int) elgg_extract('container_guid', $vars));
if (!$group instanceof \ElggGroup) {
	throw new EntityNotFoundException();
}

if (!$group->canEdit() || !$group->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE)) {
	throw new EntityPermissionsException();
}

$entities = elgg_get_entities([
	'type' => 'object',
	'subtype' => \StaticPage::SUBTYPE,
	'metadata_name_value_pairs' => [
		'parent_guid' => 0,
	],
	'container_guid' => $group->guid,
	'limit' => false,
	'sort_by' => [
		'property' => 'order',
		'direction' => 'asc',
		'join_type' => 'left',
		'signed' => true,
	],
	'batch' => true,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'container_guid',
	'value' => $group->guid,
]);

$lis = '';
foreach ($entities as $entity) {
	$input = elgg_view_field([
		'#type' => 'hidden',
		'name' => 'guids[]',
		'value' => $entity->guid,
	]);
	
	$lis .= elgg_format_element('li', [], elgg_view_image_block(elgg_view_icon('arrows'), $input . $entity->getDisplayName()));
}

if (empty($lis)) {
	echo elgg_echo('static:admin:empty');
	return;
}

echo elgg_format_element('ul', ['class' => ['static-order-group-list', 'mbm']], $lis);

?>
<script type='module'>
	import 'jquery';
	import 'jquery-ui';
	
	$('.static-order-group-list').sortable({
		items: 'li',
		containment: 'parent',
		tolerance: 'pointer'
	});
</script>
<?php

echo elgg_view_field(['#type' => 'submit', 'text' => elgg_echo('save')]);
