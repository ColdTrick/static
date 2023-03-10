<?php

namespace ColdTrick\StaticPages\Plugins;

/**
 * CSV Exporter events
 */
class CSVExporter {
	
	/**
	 * Add last editor information to static export
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addLastEditor(\Elgg\Event $event) {
		
		if ($event->getParam('subtype') !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$values = [
			elgg_echo('static:csv_exporter:last_editor:guid') => 'static_last_editor_guid',
			elgg_echo('static:csv_exporter:last_editor:name') => 'static_last_editor_name',
			elgg_echo('static:csv_exporter:last_editor:username') => 'static_last_editor_username',
			elgg_echo('static:csv_exporter:last_editor:email') => 'static_last_editor_email',
			elgg_echo('static:csv_exporter:last_editor:profile_url') => 'static_last_editor_profile_url',
		];
		
		if (!(bool) $event->getParam('readable')) {
			$values = array_values($values);
		}
		
		return array_merge($event->getValue(), $values);
	}
	
	/**
	 * Export last editor information
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return void|string
	 */
	public static function exportLastEditor(\Elgg\Event $event) {
		$return_value = $event->getValue();
		if (!is_null($return_value)) {
			// someone already provided output
			return;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		$last_editor = $entity->getLastEditor();
		if (empty($last_editor)) {
			return;
		}
		
		switch ($event->getParam('exportable_value')) {
			case 'static_last_editor_guid':
				return $last_editor->guid;
				break;
			case 'static_last_editor_name':
				return $last_editor->getDisplayName();
				break;
			case 'static_last_editor_username':
				return $last_editor->username;
				break;
			case 'static_last_editor_email':
				return $last_editor->email;
				break;
			case 'static_last_editor_profile_url':
				return $last_editor->getURL();
				break;
		}
	}
	
	/**
	 * Add last revision information to static export
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addLastRevision(\Elgg\Event $event) {
		
		if ($event->getParam('subtype') !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$values = [
			elgg_echo('static:csv_exporter:last_revision:timestamp') => 'static_last_revision_timestamp',
			elgg_echo('static:csv_exporter:last_revision:timestamp:readable') => 'static_last_revision_timestamp_readable',
		];
		
		if (!(bool) $event->getParam('readable')) {
			$values = array_values($values);
		}
		
		return array_merge($event->getValue(), $values);
	}
	
	/**
	 * Export last revison information
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return void|string
	 */
	public static function exportLastRevision(\Elgg\Event $event) {
		
		if (!is_null($event->getValue())) {
			// someone already provided output
			return;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		$last_revision = $entity->getLastRevision();
		if (empty($last_revision)) {
			return;
		}
		
		switch ($event->getParam('exportable_value')) {
			case 'static_last_revision_timestamp':
				return $last_revision->time_created;
				break;
			case 'static_last_revision_timestamp_readable':
				return csv_exported_get_readable_timestamp($last_revision->time_created);
				break;
		}
	}
	
	/**
	 * Add out-of-date information to static export
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addOutOfDate(\Elgg\Event $event) {
		
		if ($event->getParam('subtype') !== \StaticPage::SUBTYPE) {
			return;
		}
		
		if (!static_out_of_date_enabled()) {
			return;
		}
		
		$values = [
			elgg_echo('static:csv_exporter:out_of_date:state') => 'static_out_of_date_state',
		];
		
		if (!(bool) $event->getParam('readable')) {
			$values = array_values($values);
		}
		
		return array_merge($event->getValue(), $values);
	}
	
	/**
	 * Export out-of-date information
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return void|string
	 */
	public static function exportOutOfDate(\Elgg\Event $event) {
		
		if (!is_null($event->getValue())) {
			// someone already provided output
			return;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		switch ($event->getParam('exportable_value')) {
			case 'static_out_of_date_state':
				return $entity->isOutOfDate() ? 'yes' : 'no';
				break;
		}
	}
	
	/**
	 * Add parent and main pages to export
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addParentPages(\Elgg\Event $event) {
		
		if ($event->getParam('subtype') !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$values = [
			elgg_echo('static:csv_exporter:parent:title') => 'static_parent_title',
			elgg_echo('static:csv_exporter:parent:guid') => 'static_parent_guid',
			elgg_echo('static:csv_exporter:parent:url') => 'static_parent_url',
			elgg_echo('static:csv_exporter:main:title') => 'static_main_title',
			elgg_echo('static:csv_exporter:main:guid') => 'static_main_guid',
			elgg_echo('static:csv_exporter:main:url') => 'static_main_url',
		];
		
		if (!(bool) $event->getParam('readable')) {
			$values = array_values($values);
		}
		
		return array_merge($event->getValue(), $values);
	}
	
	/**
	 * Export parent and main pages
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return void|string
	 */
	public static function exportParentPages(\Elgg\Event $event) {
		
		if (!is_null($event->getValue())) {
			// someone already provided output
			return;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		switch ($event->getParam('exportable_value')) {
			case 'static_parent_title':
				$parent = $entity->getParentPage();
				if (empty($parent)) {
					return '';
				}
				return $parent->getDisplayName();
			case 'static_parent_guid':
				$parent = $entity->getParentPage();
				if (empty($parent)) {
					return '';
				}
				return $parent->guid;
			case 'static_parent_url':
				$parent = $entity->getParentPage();
				if (empty($parent)) {
					return '';
				}
				return $parent->getURL();
			case 'static_main_title':
				$main = $entity->getRootPage();
				if ($main->guid === $entity->guid) {
					return '';
				}
				return $main->getDisplayName();
			case 'static_main_guid':
				$main = $entity->getRootPage();
				if ($main->guid === $entity->guid) {
					return '';
				}
				return $main->guid;
			case 'static_main_url':
				$main = $entity->getRootPage();
				if ($main->guid === $entity->guid) {
					return '';
				}
				return $main->getURL();
		}
	}
}
