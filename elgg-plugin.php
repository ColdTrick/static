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
		'view:object:static' => [
			'path' => '/static/view/{guid}',
			'resource' => 'static/view',
		],
		'add:object:static' => [
			'path' => '/static/add/{container_guid?}',
			'resource' => 'static/edit',
		],
		'edit:object:static' => [
			'path' => '/static/edit/{guid}',
			'resource' => 'static/edit',
		],
		'collection:object:static:group' => [
			'path' => '/static/group/{guid}',
			'resource' => 'static/group',
		],
		'collection:object:static:group:out_of_date' => [
			'path' => '/static/group/{guid}/out_of_date',
			'resource' => 'static/out_of_date',
		],
		'collection:object:static:out_of_date' => [
			'path' => '/static/out_of_date',
			'resource' => 'static/out_of_date',
		],
		'collection:object:static:all' => [
			'path' => '/static/all',
			'resource' => 'static/all',
		],
		'default:object:static' => [
			'path' => '/static',
			'resource' => 'static/all',
		],
		/**
		 * @todo case 'out_of_date':
				
			$user = false;
			$username = elgg_extract(1, $page);
			if (!empty($username)) {
				$user = get_user_by_username($username);
			}
			
			if ($user instanceof \ElggUser) {
				elgg_set_page_owner_guid($user->getGUID());
				
				echo elgg_view_resource('static/out_of_date_owner', ['user' => $user]);
			} else {
				echo elgg_view_resource('static/out_of_date');
			}
		 */
	],
];
