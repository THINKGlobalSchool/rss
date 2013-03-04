<?php
/**
 * Elgg RSS Save Action
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

// Get inputs
$guid 				= get_input('guid');
$title              = get_input('title');
$description		= get_input('description');
$feed_url           = get_input('feed_url');          
$tags 				= string_to_tag_array(get_input('tags'));
$access 			= get_input('access_id');
$container_guid 	= get_input('container_guid', NULL);

// Sticky form
elgg_make_sticky_form('rss-save-form');
if (!$title || !$feed_url) {
	register_error(elgg_echo('rss:error:requiredfields'));
	forward(elgg_get_site_url() . 'rss/add');
}

// Editing
if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'rss_feed') && $entity->canEdit()) {
		$rss = $entity;
	} else {
		register_error(elgg_echo('rss:error:save'));
		forward(REFERER);
	}
} else { // New 
	$rss = new ElggObject();
	$rss->subtype = 'rss_feed';
	$rss->container_guid = $container_guid;
}

$rss->title = $title;
$rss->description = $description;
$rss->feed_url = $feed_url;
$rss->tags = $tags;
$rss->access_id = $access;

// If error saving, register error and return
if (!$rss->save()) {
	register_error(elgg_echo('rss:error:save'));
	forward(REFERER);
}

// Clear sticky form
elgg_clear_sticky_form('rss-save-form');

if (!$guid) {
	// Add to river
	add_to_river('river/object/rss_feed/create', 'create', elgg_get_logged_in_user_guid(), $rss->getGUID());
}

// Forward on
system_message(elgg_echo('rss:success:save'));

if (elgg_instanceof(get_entity($container_guid), 'group')) {
	forward("rss/group/{$container_guid}/owner");
} else {
	forward("rss");
}
