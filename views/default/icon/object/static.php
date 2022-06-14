<?php
/**
 * Static icon view.
 *
 * @uses $vars['entity']     The entity the icon represents - uses getIconURL() method
 * @uses $vars['size']       topbar, tiny, small, medium (default), large, master
 * @uses $vars['href']       Optional override for link
 * @uses $vars['img_class']  Optional CSS class added to img
 * @uses $vars['img_attr']   Optional attributes to set on the img
 * @uses $vars['link_class'] Optional CSS class for the link
 * @uses $vars['link_attr']  Optional attributes to set on the link
 * @uses $vars['class']      Optional classes to set on the wrapper
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \StaticPage) {
	return;
}

$icon_sizes = elgg_get_icon_sizes($entity->type, $entity->getSubtype());
// Get size
$size = elgg_extract('size', $vars, 'medium');
if (!array_key_exists($size, $icon_sizes)) {
	$size = 'medium';
}
$vars['size'] = $size;

$wrapper_params = [
	'class' => elgg_extract_class($vars, [
		'static-thumbnail',
		"static-thumbnail-{$size}",
	]),
];
unset($vars['class']);

// make image params
$img_params = elgg_extract('img_attr', $vars, []);
$img_params['class'] = elgg_extract_class($img_params, elgg_extract('img_class', $vars, []));

if (!isset($img_params['alt'])) {
	$img_params['alt'] = htmlspecialchars($entity->getDisplayName(), ENT_QUOTES, 'UTF-8', false);
}
if (!isset($img_params['src'])) {
	$img_params['src'] = $entity->getIconURL(['size' => $size]);
}

if (!isset($vars['width'])) {
	$vars['width'] = $size != 'master' ? $icon_sizes[$size]['w'] : null;
}
if (!isset($vars['height'])) {
	$vars['height'] = $size != 'master' ? $icon_sizes[$size]['h'] : null;
}

if (!isset($img_params['width']) && !empty($vars['width'])) {
	$img_params['width'] = elgg_extract('width', $vars);
}

if (!isset($img_params['height']) && !empty($vars['height'])) {
	$img_params['height'] = elgg_extract('height', $vars);
}

$img = elgg_view('output/img', $img_params);

// make content
$content = $img;

// check to showlink
$url = elgg_extract('href', $vars, $entity->getURL());
if ($url) {
	$link_params = elgg_extract('link_attr', $vars, []);
	$link_params['href'] = $url;
	$link_params['text'] = $img;
	$link_params['is_trusted'] = true;
	$link_params['class'] = elgg_extract_class($link_params, elgg_extract('link_class', $vars, []));
	
	$content = elgg_view('output/url', $link_params);
}

echo elgg_format_element('span', $wrapper_params, $content);
