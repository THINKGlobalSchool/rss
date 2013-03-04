<?php
/**
 * Elgg RSS English Language Translation
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

$english = array(
	// Generic
	'rss' => 'RSS Feeds',
	'rss:enablegroup' => 'Enable group RSS feeds',

	// Titles 
	'rss:title:owned' => '%s\'s RSS Feeds',
	'rss:title:friends' => 'Friends\' RSS Feeds',
	'rss:title:edit' => 'Edit RSS Feed',
	'rss:title:add' => 'Add RSS Feed',

	// Labels 
	'rss:add' => 'Add RSS Feed',
	'rss:label:noresults' => 'No Results',
	'rss:label:groupfeeds' => 'Group RSS Feeds',
	'rss:label:url' => 'Feed URL',

	// Notifications
	'rss:notification:subject' => 'New RSS Feed',
	'rss:notification:body' => "%s created a new RSS Feed titled: %s\n\n%s\n\nTo view the feed click here:\n%s
",

	// River
	'river:create:object:rss_feed' => '%s created a RSS Feed titled %s',
	'river:comment:object:rss_feed' => '%s commented on a RSS Feed titled %s',

	// Messages
	'rss:success:save' => 'Successfully saved RSS Feed',
	'rss:success:delete' => 'RSS Feed successfully deleted',
	'rss:error:save' => 'Error saving RSS Feed',
	'rss:error:delete' => 'There was an error deleting the RSS Feed',
	'rss:error:requiredfields' => 'One or more required fields are missing',
	'rss:error:invalidurl' => 'Invalid Feed URL',
	'rss:error:notfound' => 'RSS Feed Not Found',
);

add_translation('en',$english);
