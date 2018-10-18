<?php

$guid = (int) get_input('guid');
if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$entity = elgg_call(ELGG_IGNORE_ACCESS, function() use ($guid) {
	return get_entity($guid);
});

if (!$entity instanceof StaticPage || !$entity->canDelete()) {
	return elgg_error_response(elgg_echo('entity:delete:permission_denied'));
}

$display_name = $entity->getDisplayName();
$container = $entity->getContainerEntity();

if (!$entity->delete()) {
	return elgg_error_response(elgg_echo('entity:delete:fail', [$display_name]));
}

$forward_url = elgg_generate_url('collection:object:static:all');
if ($container instanceof ElggGroup) {
	$forward_url = elgg_generate_url('collection:object:static:group', [
		'guid' => $container->guid,
	]);
}

return elgg_ok_response('', elgg_echo('entity:delete:success', [$display_name]), $forward_url);
