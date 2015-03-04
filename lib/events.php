<?php
/**
 * All event handlers are bundled in this file
 */

/**
 * Make sure the last editor of a static page gets notified about a comment
 *
 * @param string         $event  'create'
 * @param string         $type   'object'
 * @param ElggAnnotation $comment the object that was just created
 *
 * @return void
 */
function static_create_comment_handler($event, $type, ElggAnnotation $comment) {
	
	// check of this is a comment
	if (empty($comment) || !($comment instanceof ElggAnnotation)) {
		return;
	}

	// is it a comment on a static page
	$entity = $comment->getEntity();
	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return;
	}

	$comment_owner = $comment->getOwnerEntity();

	// get last revisor
	$ia = elgg_set_ignore_access(true);
	$revisions = $entity->getAnnotations("static_revision", 1, 0, "desc");

	$static_owner = $revisions[0]->getOwnerEntity();

	elgg_set_ignore_access($ia);

	// @see actions/comment/save
	$subject = elgg_echo("generic_comment:email:subject");
	$message = elgg_echo("generic_comment:email:body", array(
		$entity->title,
		$comment_owner->name,
		$comment->value,
		$entity->getURL(),
		$comment_owner->name,
		$comment_owner->getURL()
	));

	// don't notify yourself
	if ($static_owner->getGUID() != $comment_owner->getGUID()) {
		notify_user($static_owner->getGUID(), $comment_owner->getGUID(), $subject, $message);
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
	
	$options = array(
		"type" => "object",
		"subtype" => "static",
		"metadata_name" => "parent_guid",
		"site_guids" => false,
		"limit" => false
	);
	
	$metadata_options = array(
		"metadata_name" => "parent_guid",
		"site_guids" => false,
		"limit" => false
	);
	
	$ia = elgg_set_ignore_access(true);
	
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
	
	elgg_set_ignore_access($ia);
	
}
