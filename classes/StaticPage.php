<?php

use Elgg\Database\Clauses\OrderByClause;
use Imagine\Filter\Basic\Save;

/**
 * StaticPage entity
 */
class StaticPage extends \ElggObject {
	
	const SUBTYPE = 'static';
	
	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getURL(): string {
		
		// custom url (eg. /my-static-page) or basic url
		$url = $this->friendly_title ?: elgg_generate_entity_url($this, 'view');

		// allow other to change the url
		$url = elgg_trigger_event_results('entity:url', $this->getType(), ['entity' => $this], elgg_normalize_url($url));
		
		return elgg_normalize_url($url);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canComment(int $user_guid = 0): bool {
		
		if ($this->enable_comments !== 'yes') {
			return false;
		}
		
		return parent::canComment($user_guid);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function delete(bool $recursive = true): bool {
		
		// do this here so we can ignore access later
		if (!$this->canDelete()) {
			return false;
		}
		
		// ignore access, so moderators cleanup everything correctly
		return elgg_call(ELGG_IGNORE_ACCESS, function() use ($recursive) {
			
			$result = parent::delete($recursive);
			if ($result === false || $recursive !== true) {
				return $result;
			}
		
			// remove sub pages
			/* @var $batch \ElggBatch */
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => self::SUBTYPE,
				'metadata_name_value_pairs' => [
					'name' => 'parent_guid',
					'value' => $this->guid,
				],
				'limit' => false,
				'batch' => true,
				'batch_inc_offset' => false,
			]);
			
			/* @var $entity \StaticPage */
			foreach ($batch as $entity) {
				$entity->delete($recursive);
			}
			
			return $result;
		});
	}

	/**
	 * Clears the menu cache for this entity
	 *
	 * @return void
	 */
	public function clearMenuCache(): void {
		elgg_delete_system_cache("static_menu_item_cache_{$this->guid}");
	}

	/**
	 * Save the menu cache for this entity
	 *
	 * @param mixed $contents contents to save
	 *
	 * @return void
	 */
	public function saveMenuCache($contents): void {
		elgg_save_system_cache("static_menu_item_cache_{$this->guid}", $contents);
	}

	/**
	 * Returns the menu cache for this entity
	 *
	 * @return mixed
	 */
	public function getMenuCache() {
		return elgg_load_system_cache("static_menu_item_cache_{$this->guid}");
	}
	
	/**
	 * Returns a friendly title
	 *
	 * @return string
	 */
	public function getFriendlyTitle(): string {
		return $this->friendly_title ?: static_make_friendly_title($this->title);
	}
	
	/**
	 * Returns the root page of an entity
	 *
	 * @return \StaticPage
	 */
	public function getRootPage(): \StaticPage {
		
		// first created relationship is the root entity
		$relations = elgg_call(ELGG_IGNORE_ACCESS, function () {
			return $this->getEntitiesFromRelationship([
				'type' => 'object',
				'subtype' => StaticPage::SUBTYPE,
				'relationship' => 'subpage_of',
				'limit' => 1,
			]);
		});
		
		return $relations ? $relations[0] : $this;
	}
	
	/**
	 * Returns the parent page of an entity
	 *
	 * @return \StaticPage|false
	 */
	public function getParentPage() {
		
		$parent_guid = $this->parent_guid;
		if (empty($parent_guid)) {
			return false;
		}
		
		return elgg_call(ELGG_IGNORE_ACCESS, function() use ($parent_guid) {
			$parent_entity = get_entity($parent_guid);
			if (!$parent_entity instanceof \StaticPage) {
				return false;
			}
			
			return $parent_entity;
		});
	}
	
	/**
	 * Returns the latest editor entity for this page, or false if there is none
	 *
	 * @return false|\ElggUser
	 */
	public function getLastEditor() {
		$revision = $this->getLastRevision();
		if (empty($revision)) {
			return false;
		}
		
		$user = $revision->getOwnerEntity();
		if (empty($user)) {
			return false;
		}
		
		return $user;
	}
	
	/**
	 * Get the last revision of a static page
	 *
	 * @return false|\ElggAnnotation
	 */
	public function getLastRevision() {
		
		$revisions = elgg_call(ELGG_IGNORE_ACCESS, function() {
			return $this->getAnnotations([
				'annotation_name' => 'static_revision',
				'limit' => 1,
				'order_by' => new OrderByClause('time_created', 'DESC'),
			]);
		});
		
		return $revisions ? $revisions[0] : false;
	}
	
	/**
	 * Is the page out-of-date
	 * This can be influenced using an event
	 *
	 * @return bool
	 */
	public function isOutOfDate(): bool {
		
		if (!static_out_of_date_enabled()) {
			return false;
		}
		
		$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');
		$compare_ts = time() - ($days * 24 * 60 * 60);
		
		$result = ($this->time_updated < $compare_ts);
		
		return (bool) elgg_trigger_event_results('out_of_date:state', 'static', ['entity' => $this], $result);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function toObject(array $params = []) {
		$object = parent::toObject($params);
		
		$last_editor = $this->getLastEditor();
		if ($last_editor instanceof \ElggUser) {
			// Change to owner of the static page, to allow the last editor to find it even if private (for example in search)
			$object->owner_guid = $last_editor->guid;
		}
		
		return $object;
	}
}
