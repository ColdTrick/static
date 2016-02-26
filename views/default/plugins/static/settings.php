<?php

$plugin = elgg_extract('entity', $vars);

$noyes_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes'),
];

$setting = elgg_echo('static:settings:enable_out_of_date');
$setting .= elgg_view('input/select', [
	'name' => 'params[enable_out_of_date]',
	'options_values' => $noyes_options,
	'value' => $plugin->enable_out_of_date,
	'class' => 'mls',
]);
echo elgg_format_element('div', [], $setting);

$setting = elgg_echo('static:settings:out_of_date_days');
$setting .= elgg_view('input/text', [
	'name' => 'params[out_of_date_days]',
	'value' => (int) $plugin->out_of_date_days,
	'size' => '4',
	'class' => 'mls',
	'style' => 'width:inherit;',
]);
$setting .= elgg_echo('static:settings:out_of_date_days:days');
echo elgg_format_element('div', [], $setting);

$setting = elgg_echo('static:settings:enable_groups');
$setting .= elgg_view('input/select', [
	'name' => 'params[enable_groups]',
	'options_values' => $noyes_options,
	'value' => $plugin->enable_groups,
	'class' => 'mls',
]);
echo elgg_format_element('div', [], $setting);
