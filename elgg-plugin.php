<?php

use ColdTrick\StaticPages\Bootstrap;

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'enable_groups' => 'no',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'static',
			'class' => StaticPage::class,
			'searchable' => true,
		],
	],
	'actions' => [
		'static/edit' => [],
		'static/delete' => [],
		'static/reorder' => [],
		'static/reorder_root_pages' => [],
		'static/mark_not_out_of_date' => [],
	],
	'routes' => [
		
	],
	'widgets' => [
		
	],
];
	