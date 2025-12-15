<?php
/**
 * Configure group specific static settings
 */

$group = elgg_extract('entity', $vars);

$checked = false;
$help = null;
if ($group instanceof \ElggGroup) {
	$checked = (bool) $group->getPluginSetting('static', 'enable_manual_sorting', false);
	$help = elgg_view_url(elgg_generate_url('collection:object:static:group', ['guid' => $group->guid]), elgg_echo('static:groups:title'));
}

$content = elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('static:groups:edit:enable_manual_sorting'),
	'#help' => $help,
	'name' => 'settings[static][enable_manual_sorting]',
	'value' => $checked,
]);

echo elgg_view_module('info', elgg_echo('collection:object:static'), $content);
