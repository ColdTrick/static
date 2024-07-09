<?php

namespace ColdTrick\StaticPages;

/**
 * Register fields for static pages
 */
class FieldsHandler {
	
	/**
	 * Register the fields for static pages
	 *
	 * @param \Elgg\Event $event 'fields', 'object:static'
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event): array {
		$result = (array) $event->getValue();
		
		$result[] = [
			'#type' => 'text',
			'#label' => elgg_echo('title'),
			'name' => 'title',
			'required' => true,
		];
		
		$result[] = [
			'#type' => 'text',
			'#label' => elgg_echo('static:new:permalink'),
			'name' => 'friendly_title',
			'required' => true,
		];
		
		$result[] = [
			'#type' => 'longtext',
			'#label' => elgg_echo('description'),
			'name' => 'description',
			'required' => true,
		];
		
		$result[] = [
			'#type' => 'tags',
			'#label' => elgg_echo('tags'),
			'name' => 'tags',
		];
		
		$result[] = [
			'#type' => 'static/parent',
			'#label' => elgg_echo('static:new:parent'),
			'name' => 'parent_guid',
		];
		
		$result[] = [
			'#type' => 'checkbox',
			'#label' => elgg_echo('static:new:comment'),
			'name' => 'enable_comments',
			'switch' => true,
			'default' => 'no',
			'value' => 'yes',
		];
		
		$result[] = [
			'#type' => 'userpicker',
			'#label' => elgg_echo('static:new:moderators'),
			'name' => 'moderators',
		];
		
		$result[] = [
			'#type' => 'access',
			'#label' => elgg_echo('access'),
			'name' => 'access_id',
			'entity_type' => 'object',
			'entity_subtype' => \StaticPage::SUBTYPE,
		];
		return $result;
	}
}
