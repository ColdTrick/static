<?php

$entity = elgg_extract('entity', $vars);
$full_view = elgg_extract('full_view', $vars);

if ($full_view) {
	
	$metadata = elgg_view_menu('entity', [
		'entity' => $entity,
		'handler' => 'static',
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz alliander-theme-menu-static',
	]);
	
	$params = [
		'entity' => $entity,
		'title' => false,
		'metadata' => $metadata,
		'tags' => false,
	];
	$summary = elgg_view('object/elements/summary', $params);
	
	$body = '';
	if ($entity->icontime) {
		$body .= elgg_view_entity_icon($entity, 'large', [
			'href' => false,
			'class' => 'float-alt',
		]);
	}
	$body .= elgg_view('output/longtext', ['value' => $entity->description]);
	
	echo elgg_view('object/elements/full', [
		'summary' => $summary,
		'body' => $body,
	]);

} elseif (elgg_in_context('search')) {
	// probably search

	$title = $entity->getVolatileData('search_matched_title') ?: $entity->title;
	$description = $entity->getVolatileData('search_matched_description');
	
	$title = elgg_view('output/url', [
		'text' => $title,
		'href' => $entity->getURL(),
		'is_trusted' => true,
	]);
	$body = $title . '<br />' . $description;

	echo elgg_view_image_block('', $body);
	
} elseif (elgg_in_context('widgets')) {
	$body = elgg_view('output/url', [
		'text' => $entity->title,
		'href' => $entity->getURL(),
	]);
	echo elgg_view_image_block('', $body);
	
} else {

	$show_edit = elgg_extract('show_edit', $vars, true);
	
	$row_data = elgg_format_element('td', [], elgg_view('output/url', [
		'text' => $entity->title,
		'href' => $entity->getURL(),
		'is_trusted' => true,
	]));
	if ($show_edit && $entity->canEdit()) {
		$edit_link = elgg_view('output/url', [
			'href' => 'static/edit/' . $entity->getGUID(),
			'text' => elgg_view_icon('settings-alt'),
		]);
		$delete_link = elgg_view('output/url', [
			'href' => 'action/static/delete?guid=' . $entity->getGUID(),
			'text' => elgg_view_icon('delete'),
			'confirm' => true,
		]);

		$row_data .= elgg_format_element('td', ['width' => '1%', 'class' => 'center'], $edit_link);
		$row_data .= elgg_format_element('td', ['width' => '1%', 'class' => 'center'], $delete_link);
		
	} else {
		// add blank cells if you can not edit
		$row_data .= elgg_format_element('td', ['width' => '1%'], '&nbsp;');
		$row_data .= elgg_format_element('td', ['width' => '1%'], '&nbsp;');
	}

	echo elgg_format_element('tr', ['data-guid' => $entity->getGUID()], $row_data);
}
