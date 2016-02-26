<?php

$size = elgg_extract('size', $vars, 'medium');
$class = elgg_extract('class', $vars);

$params = [
	'class' => [
		'static-thumbnail',
		'static-thumbnail-' . $size,
	],
];
if (!empty($class)) {
	$params['class'][] = $class;
	unset($vars['class']);
}

echo elgg_format_element('span', $params, elgg_view('icon/default', $vars));
