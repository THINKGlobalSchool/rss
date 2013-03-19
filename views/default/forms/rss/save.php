<?php
/**
 * Elgg RSS Save form
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

elgg_load_css('elgg.rss');
elgg_load_js('elgg.rss');

// Get values/sticky values
$title          = elgg_extract('title', $vars);
$description	= elgg_extract('description', $vars);
$feed_url       = elgg_extract('feed_url', $vars);
$tags 			= elgg_extract('tags', $vars);
$access_id 		= elgg_extract('access_id', $vars, ACCESS_DEFAULT);
$container_guid = elgg_extract('container_guid', $vars);
$guid 		 	= elgg_extract('guid', $vars, NULL);
		
// If we have an entity, we're editing
if ($guid) {
	// Hidden field to identify rss feed
	$entity_guid_input = elgg_view('input/hidden', array('name' => 'guid', 'value' => $guid));
}
	
$title_label = elgg_echo('title');
$title_input = elgg_view('input/text', array(
	'value' => $title,
	'name' => 'title',
));

$description_label = elgg_echo('description');
$description_input = elgg_view('input/longtext', array(
	'value' => $description,
	'name' => 'description', 
));

$url_label = elgg_echo('rss:label:url');
$url_input = elgg_view('input/url', array(
	'value' => $feed_url,
	'name' => 'feed_url',
	'id' => 'rss-url',
));

$tags_label =  elgg_echo('tags');
$tags_input = elgg_view('input/tags', array(
	'id' => 'tags',
	'name' => 'tags',
	'value' => $tags
));

$access_label =  elgg_echo('access');
$access_input = elgg_view('input/access', array(
	'id' => 'access',
	'name' => 'access_id',
	'value' => $access_id
));

$save_input = elgg_view('input/submit', array(
	'id' => 'rss-save-input',
	'name' => 'rss_save_input',
	'value' => elgg_echo('save')
));

$container_guid_input = elgg_view('input/hidden', array(
	'name' => 'container_guid',
	'value' => $container_guid,
));

$content = <<<HTML
	<div class='rss-form'>
		<div>
			<label>$title_label</label>
			$title_input
		</div><br />
		<div>
			<label>$url_label</label>
			$url_input
			<div id='rss-feed-preview'>
			</div>
		</div><br />
		<div>
			<label>$description_label</label>
			$description_input
		</div><br />
		<div>
			<label>$tags_label</label>
			$tags_input
		</div><br />
		<div>
			<label>$access_label</label>
			$access_input
		</div><br />
		<div class="elgg-foot">
			$save_input
		</div>
		$entity_guid_input
		$container_guid_input
	</div>
HTML;

echo $content;