<?php

/* @var $entity StaticPage */
$entity = elgg_extract('entity', $vars);
if (!($entity instanceof StaticPage)) {
	return;
}

if (!$entity->isOutOfDate()) {
	return;
}

$message = elgg_echo('static:out_of_date:message');
if ($entity->canEdit()) {
	$message .= elgg_view('output/url', [
		'text' => elgg_view_icon('hand-pointer-o') . elgg_echo('static:out_of_date:message:mark'),
		'href' => "action/static/mark_not_out_of_date?guid={$entity->getGUID()}",
		'confirm' => true,
		'class' => 'mls',
		'id' => 'static-out-of-date-touch-link',
	]);
	
	elgg_require_js('static/out_of_date');
}

// message is first shown to editors only
if (!$entity->canEdit()) {
	$reminder_days = (int) elgg_get_plugin_setting('out_of_date_reminder_interval', 'static');
	$reminder_repeat = (int) elgg_get_plugin_setting('out_of_date_reminder_repeat', 'static');
	if (!empty($reminder_days) && !empty($reminder_repeat)) {
		// reminders have been set
		$days = elgg_get_plugin_setting('out_of_date_days', 'static');
		
		$compare_ts = time() - ($days * 24 * 60 * 60) - ($reminder_repeat * $reminder_days * 24 * 60 * 60);
		if ($entity->time_updated > $compare_ts) {
			// don't show to current user
			return;
		}
	}
}

echo elgg_format_element('div', [
	'id' => 'static-out-of-date-message',
	'class' => [
		'elgg-message',
		'elgg-state-error',
		'mbm',
	],
], $message);
