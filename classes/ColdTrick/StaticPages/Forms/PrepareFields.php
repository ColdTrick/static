<?php

namespace ColdTrick\StaticPages\Forms;

/**
 * Prepare the form field values for static/edit
 */
class PrepareFields {
	
	/**
	 * Prepare the form field values
	 *
	 * @param \Elgg\Event $event 'form:prepare:fields', 'static/edit'
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event): array {
		$vars = $event->getValue();
		
		$values = [];
		
		$parent_guid = (int) get_input('parent_guid');
		$fields = elgg()->fields->get('object', \StaticPage::SUBTYPE);
		foreach ($fields as $field) {
			$default_value = null;
			$name = elgg_extract('name', $field);
			
			if ($name === 'parent_guid') {
				$default_value = $parent_guid;
			} elseif ($name === 'access_id' && !empty($parent_guid)) {
				$parent = get_entity($parent_guid);
				if ($parent instanceof \StaticPage) {
					$default_value = $parent->access_id;
				}
			}
			
			$values[$name] = $default_value;
		}
		
		$entity = elgg_extract('entity', $vars);
		if ($entity instanceof \StaticPage) {
			foreach (array_keys($values) as $field) {
				if ($field === 'friendly_title') {
					$values[$field] = $entity->getFriendlyTitle();
				} elseif (isset($entity->{$field})) {
					$values[$field] = $entity->{$field};
				}
			}
		}
		
		return array_merge($values, $vars);
	}
}
