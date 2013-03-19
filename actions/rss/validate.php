<?php
/**
 * Elgg RSS Simple Feed Validation Actionå
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

elgg_load_library('elgg:simplepie');

$feed_url = get_input('feed_url');

$feed = new SimplePie();
$feed->set_feed_url($feed_url);
$feed->enable_cache(FALSE);
$feed->init();
$feed->handle_content_type();

if ($error = $feed->error()) {
	//register_error($error);
    register_error(elgg_echo('rss:error:invalidurl'));
}

echo json_encode(array(
	'feed_link' => $feed->get_link(),
));

forward(REFERER);