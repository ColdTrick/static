<?php

namespace ColdTrick\StaticPages;

use Elgg\Database\Seeds\Seed;
use Elgg\Database\Update;
use Elgg\Exceptions\Seeding\MaxAttemptsException;

/**
 * Static pages seeder
 */
class Seeder extends Seed {
	
	/**
	 * {@inheritdoc}
	 */
	public function seed() {
		$this->advance($this->getCount());
		
		$site = elgg_get_site_entity();
		$session_manager = elgg()->session_manager;
		$logged_in = $session_manager->getLoggedInUser();
		$admin = $this->createUser([
			'admin' => true,
			'validated' => true,
		]);
		$session_manager->setLoggedInUser($admin);
		
		$allow_groups = elgg_get_plugin_setting('enable_groups', 'static') === 'yes';
		
		while ($this->getCount() < $this->limit) {
			$container = $site;
			if ($allow_groups && $this->faker()->boolean(60)) {
				$container = $this->getRandomGroup();
			}
			
			if ($container instanceof \ElggGroup) {
				$container->enableTool('static');
			}
			
			try {
				/* @var $entity \StaticPage */
				$entity = $this->createObject([
					'subtype' => \StaticPage::SUBTYPE,
					'owner_guid' => $container->guid,
					'container_guid' => $container->guid,
					'enable_comments' => $this->faker()->boolean() ? 'yes' : 'no',
					'parent_guid' => 0,
					'friendly_title' => static_make_friendly_title('static-' . $this->faker()->words(3, true)),
					'order' => 0,
				]);
			} catch (MaxAttemptsException $e) {
				// unable to create static page with the given options
				continue;
			}
			
			$this->createComments($entity);
			$this->createLikes($entity);
			
			$this->addRevisions($entity);
			$this->addModerators($entity);
			$this->addSubpages($entity);
			
			$this->advance();
		}
		
		$admin->delete();
		
		if ($logged_in) {
			$session_manager->setLoggedInUser($logged_in);
		} else {
			$session_manager->removeLoggedInUser();
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function unseed() {
		/* @var $entities \ElggBatch */
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'metadata_name' => '__faker',
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
		]);
		
		/* @var $entity \StaticPage */
		foreach ($entities as $entity) {
			if ($entity->delete()) {
				$this->log("Deleted static page {$entity->guid}");
			} else {
				$this->log("Failed to delete static page {$entity->guid}");
				$entities->reportFailure();
				continue;
			}
			
			$this->advance();
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function getType(): string {
		return \StaticPage::SUBTYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getCountOptions(): array {
		return [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
		];
	}
	
	/**
	 * Backdate a revision
	 *
	 * @param \ElggAnnotation $revision     revision
	 * @param int             $time_created new time
	 *
	 * @return void
	 */
	protected function backdateRevision(\ElggAnnotation $revision, int $time_created): void {
		$update = Update::table('annotations');
		$update->set('time_created', $update->param($time_created, ELGG_VALUE_TIMESTAMP))
			->where($update->compare('id', '=', $revision->id, ELGG_VALUE_ID));
		
		elgg()->db->updateData($update);
	}
	
	/**
	 * Add revisions to the static page
	 *
	 * @param \StaticPage $entity static page
	 *
	 * @return void
	 */
	protected function addRevisions(\StaticPage $entity): void {
		$initial = $entity->annotate('static_revision', $entity->description, $entity->access_id, $this->getRandomUser()->guid);
		if (!is_bool($initial)) {
			$initial = elgg_get_annotation_from_id($initial);
			$this->backdateRevision($initial, $entity->time_created);
		}
		
		if ($this->faker()->boolean(75)) {
			return;
		}
		
		$since = $this->create_since;
		$this->setCreateSince($entity->time_created);
		
		for ($i = 0; $i < $this->faker()->numberBetween(1, 4); $i++) {
			$text = $this->faker()->text($this->faker()->numberBetween(500, 1000));
			
			$id = $entity->annotate('static_revision', $text, $entity->access_id, $this->getRandomUser()->guid);
			if (!is_bool($id)) {
				$revision = elgg_get_annotation_from_id($id);
				
				$this->backdateRevision($revision, $this->getRandomCreationTimestamp());
			}
		}
		
		$this->setCreateSince($since);
	}
	
	/**
	 * Add moderators to the static page
	 *
	 * @param \StaticPage $entity static page
	 *
	 * @return void
	 */
	protected function addModerators(\StaticPage $entity): void {
		if ($this->faker()->boolean(75)) {
			return;
		}
		
		$moderators = [];
		for ($i = 0; $i < $this->faker()->numberBetween(1, 3); $i++) {
			$moderators[] = $this->getRandomUser($moderators)->guid;
		}
		
		$entity->moderators = $moderators;
	}
	
	/**
	 * Add subpages to the static page
	 *
	 * @param \StaticPage $entity static page
	 * @param int         $depth  recursion level
	 *
	 * @return void
	 */
	protected function addSubpages(\StaticPage $entity, int $depth = 1): void {
		if ($this->faker()->boolean(75) || $depth > 2) {
			return;
		}
		
		for ($i = 0; $i < $this->faker()->numberBetween(1, 4); $i++) {
			try {
				/* @var $subpage \StaticPage */
				$subpage = $this->createObject([
					'subtype' => \StaticPage::SUBTYPE,
					'owner_guid' => $entity->owner_guid,
					'container_guid' => $entity->container_guid,
					'enable_comments' => $this->faker()->boolean() ? 'yes' : 'no',
					'parent_guid' => $entity->guid,
					'friendly_title' => static_make_friendly_title($this->faker()->words(3, true)),
					'order' => $i,
				]);
			} catch (MaxAttemptsException $e) {
				// unable to create static page with the given options
				continue;
			}
			
			$this->createComments($subpage);
			$this->createLikes($subpage);
			
			$this->addRevisions($subpage);
			$this->addModerators($subpage);
			
			$subpage->addRelationship($entity->getRootPage()->guid, 'subpage_of');
			$this->addSubpages($subpage, $depth + 1);
			
			$this->advance();
		}
	}
}
