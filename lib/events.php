<?php
/**
 * All event handlers are bundled in this file
 */

/**
 * Make sure the last editor of a static page gets notified about a comment
 *
 * @param string     $event  'create'
 * @param string     $type   'object'
 * @param ElggObject $object the object that was just created
 *
 * @return void
 */
function static_create_comment_handler($event, $type, ElggObject $object) {

	// check of this is a comment
	if (empty($object) || !elgg_instanceof($object, "object", "comment")) {
		return;
	}

	// is it a comment on a static page
	$container = $object->getContainerEntity();
	if (empty($container) || !elgg_instanceof($container, "object", "static")) {
		return;
	}

	$comment_owner = $object->getOwnerEntity();

	// get last revisor
	$ia = elgg_set_ignore_access(true);
	$revisions = $container->getAnnotations(array(
		"annotation_name" => "static_revision",
		"limit" => 1,
		"reverse_order_by" => true
	));

	$static_owner = $revisions[0]->getOwnerEntity();

	elgg_set_ignore_access($ia);

	// @see actions/comment/save
	$subject = elgg_echo("generic_comment:email:subject");
	$message = elgg_echo("generic_comment:email:body", array(
		$container->title,
		$comment_owner->name,
		$object->description,
		$container->getURL(),
		$comment_owner->name,
		$comment_owner->getURL()
	));

	$params = array(
		"object" => $object,
		"action" => "create",
	);

	// don't notify yourself
	if ($static_owner->getGUID() != $comment_owner->getGUID()) {
		notify_user($static_owner->getGUID(), $comment_owner->getGUID(), $subject, $message, $params);
	}
}

/**
 * Listen to the delete event of an ElggObject to remove a static thumbnail when needed
 *
 * @param string     $event  'delete'
 * @param string     $type   'object'
 * @param ElggObject $entity the entity about to be removed
 *
 * @return void
 */
function static_delete_object_handler($event, $type, ElggObject $entity) {
	
	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return;
	}
	
	if ($entity->icontime) {
		static_remove_thumbnail($entity->getGUID());
	}
}

/**
 * Migrate old tree structure to new structure
 *
 * @param string $event  the name of the event
 * @param string $type   the type of the event
 * @param mixed  $entity supplied entity
 *
 * @return void
 */
function static_upgrade_system_handler($event, $type, $entity) {

	// this process could take a while
	set_time_limit(0);

	// set entity options
	$options = array(
		"type" => "object",
		"subtype" => "static",
		"metadata_name" => "parent_guid",
		"site_guids" => false,
		"limit" => false
	);

	// set default metadata options
	$metadata_options = array(
		"metadata_name" => "parent_guid",
		"site_guids" => false,
		"limit" => false
	);

	// make sure we can get all entities
	$ia = elgg_set_ignore_access(true);

	// create a batch for processing
	$batch = new ElggBatch("elgg_get_entities_from_metadata", $options);
	$batch->setIncrementOffset(false);
	foreach ($batch as $entity) {

		$metadata_options["guid"] = $entity->getGUID();

		$parent_guid = (int) $entity->parent_guid;
		if (empty($parent_guid)) {
			// workaround for multi-site
			elgg_delete_metadata($metadata_options);
				
			continue;
		}

		$parent = get_entity($parent_guid);
		if (empty($parent)) {
			// workaround for multi-site
			elgg_delete_metadata($metadata_options);
				
			continue;
		}

		// set correct container
		$entity->container_guid = $parent->getGUID();
		$entity->save();

		// find the root page for the tree
		$root = static_find_old_root_page($entity);
		if (empty($root)) {
			// workaround for multi-site
			elgg_delete_metadata($metadata_options);

			continue;
		}

		// add relationship to the correct tree
		remove_entity_relationships($entity->getGUID(), "subpage_of");

		$entity->addRelationship($root->getGUID(), "subpage_of");

		// workaround for multi-site
		elgg_delete_metadata($metadata_options);
	}

	// restore access
	elgg_set_ignore_access($ia);
}
