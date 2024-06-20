<?php

elgg_push_collection_breadcrumbs('object', \StaticPage::SUBTYPE);

echo elgg_view_page(elgg_echo('static:all'), [
	'content' => elgg_view('trash/listing/all', [
		'options' => [
			'type_subtype_pairs' => ['object' => [\StaticPage::SUBTYPE]],
		],
	]),
	'sidebar' => false,
	'filter_id' => 'static',
	'filter_value' => 'trashed',
]);
