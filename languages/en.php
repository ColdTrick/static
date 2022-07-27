<?php

return [
	'item:object:static' => "Static page",
	'collection:object:static' => "Static pages",
	'river:object:static:comment' => "%s commented on the static page %s",
	
	// settings
	'static:settings:general:title' => "General settings",
	'static:settings:enable_groups' => "Enable static pages in groups",
	
	'static:settings:out_of_date:title' => "Out-of-date settings",
	'static:settings:out_of_date:description' => "With the out-of-date settings you can have a listing of content which is considered out-of-date. Also notifications will be sent to the last editor so that they can check if the content is still relevant.",
	'static:settings:out_of_date_days' => "Number of days before content is out-of-date",
	'static:settings:out_of_date_days:help' => "Enter a number of days after which content is considered out-of-date. A notification will also be shown on the full view of the static page to indicate the content is out-of-date. 0 or empty to not enable this feature.",
	'static:settings:out_of_date:reminder_interval' => "After a number of days send a reminder of out-of-date content",
	'static:settings:out_of_date:reminder_interval:help' => "Enter a number of days after which content is re-checked if it's still out-of-date. Setting this will delay the showing of the out-of-date message on the full view of the static page for non-editors. 0 or empty to not have reminders.",
	'static:settings:out_of_date:reminder_repeat' => "Number of times the reminder interval should be re-checked",
	'static:settings:out_of_date:reminder_repeat:help' => "To send out the reminder of out-of-date content multiple times, set a number here. 0 means no reminders.",
	
	'static:admin:empty' => "No static pages created",
	'static:all' => "Manage static pages",
	'static:edit' => "Create/Edit a static page",
	'static:new:permalink' => "Permalink",
	'static:new:parent' => "Select parent page",
	'static:new:parent:top_level' => "No parent page / Top Level",
	'static:edit:menu:parent:direct_child' => "Direct subpage of toppage",
	'static:new:comment' => "Allow comments",
	'static:new:moderators' => "Assign moderators",
	
	'static:groups:title' => "Manage group static pages",
	'static:groups:owner_block' => "Group static pages",
	'groups:tool:static' => "Enable static pages",
	'groups:tool:static:description' => "Create static pages for the group. These can only be managed by the group owners/admins.",

	'add:object:static' => "Add static page",
	'static:add:subpage' => "Create a subpage",
		
	'static:revisions' => "Revisions",
	
	// last editor
	'static:menu:owner_block:last_editor' => "Static pages",
	'static:last_editor:title' => "Static pages: %s",
	
	// out of date
	'static:menu:filter:out_of_date' => "All out-of-date content",
	'static:menu:filter:out_of_date:mine' => "My out-of-date content",
	'static:menu:filter:out_of_date:group' => "Group out-of-date content",
	
	'static:out_of_date:message' => 'The content on this page is considered out-of-date. It may no longer be relevant.',
	'static:out_of_date:message:mark' => 'This page is up-to-date.',
	
	'static:out_of_date:title' => "Out-of-date content",
	'static:out_of_date:owner:title' => "Out-of-date content for: %s",
	'static:out_of_date:none' => "No outdated content was found",
	'static:out_of_date:include_groups' => "Include group content",
	
	'static:out_of_date:notification:section:new' => "Newly out-of-date content",
	'static:out_of_date:notification:section:reminder' => "Reminder %s for out-of-date content",
	'static:out_of_date:notification:subject' => "Your static content is out-of-date",
	'static:out_of_date:notification:message' => "Some of your static content pages are out-of-date. Please have a look if the information is still current/useful.

%s

Click here for a complete list:
%s",
	
	// widgets
	'static:widgets:static_groups:title' => "Static pages",
	'static:widgets:static_groups:description' => "Show the group static pages",
	
	// actions
	'static:action:edit:error:title_description' => "Please enter a Title and Description",
	'static:action:edit:error:friendly_title' => "The permalink is already taken, please choose another one",
	'static:action:edit:success' => "Save successful",
	
	// csv exporter
	'static:csv_exporter:last_editor:guid' => "Last editor GUID",
	'static:csv_exporter:last_editor:name' => "Last editor name",
	'static:csv_exporter:last_editor:username' => "Last editor username",
	'static:csv_exporter:last_editor:email' => "Last editor email address",
	'static:csv_exporter:last_editor:profile_url' => "Last editor profile URL",
	
	'static:csv_exporter:last_revision:timestamp' => "Last revision time created",
	'static:csv_exporter:last_revision:timestamp:readable' => "Last revision time created (readable)",
	
	'static:csv_exporter:out_of_date:state' => "Is out-of-date",
	
	'static:csv_exporter:parent:title' => "Parent page title",
	'static:csv_exporter:parent:guid' => "Parent page GUID",
	'static:csv_exporter:parent:url' => "Parent page URL",
	'static:csv_exporter:main:title' => "Main page title",
	'static:csv_exporter:main:guid' => "Main page GUID",
	'static:csv_exporter:main:url' => "Main page URL",

	'static:upgrade:2022061401:title' => "Rename static thumbnails",
	'static:upgrade:2022061401:description' => "Renames the thumbnails of static pages to the default entity icon name",
];
