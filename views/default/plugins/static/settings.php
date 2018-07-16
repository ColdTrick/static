<?php

/* @var $plugin ElggPlugin */
$plugin = elgg_extract('entity', $vars);

// general settings
$general = elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('static:settings:enable_groups'),
	'name' => 'params[enable_groups]',
	'checked' => $plugin->enable_groups === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

echo elgg_view_module('info', elgg_echo('static:settings:general:title'), $general);

// out of date
$out_of_date = elgg_view('output/longtext', [
	'value' => elgg_echo('static:settings:out_of_date:description'),
]);

$out_of_date .= elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('static:settings:out_of_date_days'),
	'#help' => elgg_echo('static:settings:out_of_date_days:help'),
	'name' => 'params[out_of_date_days]',
	'value' => $plugin->out_of_date_days,
	'min' => 0,
	'max' => 9999,
]);

$out_of_date .= elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('static:settings:out_of_date:reminder_interval'),
	'#help' => elgg_echo('static:settings:out_of_date:reminder_interval:help'),
	'name' => 'params[out_of_date_reminder_interval]',
	'value' => $plugin->out_of_date_reminder_interval,
	'min' => 0,
	'max' => 999,
]);

$out_of_date .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('static:settings:out_of_date:reminder_repeat'),
	'#help' => elgg_echo('static:settings:out_of_date:reminder_repeat:help'),
	'name' => 'params[out_of_date_reminder_repeat]',
	'value' => (int) $plugin->out_of_date_reminder_repeat,
	'options' => range(0, 9),
]);

echo elgg_view_module('info', elgg_echo('static:settings:out_of_date:title'), $out_of_date);
