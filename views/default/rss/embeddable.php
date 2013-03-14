<?php
/**
 * Elgg RSS feed embeddable view view
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 * @uses $vars['entity_guid']
 */

$rss = get_entity(elgg_extract('entity_guid', $vars));

if (!elgg_instanceof($rss, 'object', 'rss_feed')) {
	return;
}

$feed = elgg_view('rss/feed', array(
	'sources' => array(
		$rss->title => $rss->feed_url,
	),
));

$encoded_content = rawurlencode($feed);
echo "[generic embed={$encoded_content}]";