<?php
/**
 * Show a trashed Static page
 *
 * @uses $vars['entity'] the static page
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \StaticPage) {
	return;
}

$params = [
	'byline_owner_entity' => $entity->getLastEditor(),
	'type' => false,
];

if (!$entity->canRestore()) {
	$parent = $entity->getParentPage();
	if ($parent instanceof \StaticPage) {
		while (!$parent->canRestore()) {
			$parent = $parent->getParentPage();
			if (!$parent instanceof \StaticPage) {
				break;
			}
		}
	}
	
	if (!$parent instanceof \StaticPage) {
		$params['content'] = elgg_view_message('warning', elgg_echo('static:trashed:no_parent'), ['title' => false]);
	} else {
		$parent_link = elgg_view('output/url', [
			'text' => elgg_echo('static:trashed:restore_parent:link'),
			'href' => elgg_generate_action_url('entity/restore', [
				'guid' => $parent->guid,
			]),
			'confirm' => elgg_echo('restoreconfirm'),
		]);
		$params['content'] = elgg_view_message('warning', elgg_echo('static:trashed:restore_parent', [elgg_format_element('strong', [], $parent->getDisplayName())]), ['title' => false, 'link' => $parent_link]);
	}
}

$params = $params + $vars;
echo elgg_view('trash/entity/default', $params);
