<?php

$widget = elgg_extract('entity', $vars);

$page_selector = static_get_widget_selector($widget->getOwnerEntity());
$page_selector = array_reverse($page_selector, true);
$page_selector[''] = elgg_echo('static:widgets:static_groups:edit:main_page:select');
$page_selector = array_reverse($page_selector, true);

echo '<div>';
echo elgg_echo('static:widgets:static_groups:edit:main_page');
echo elgg_view('input/dropdown', array(
	'name' => 'params[main_page]',
	'options_values' => $page_selector,
	'value' => $widget->main_page,
	'class' => 'mls'
));
echo '</div>';