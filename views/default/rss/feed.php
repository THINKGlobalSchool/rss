<?php
/**
 * Elgg RSS feed view
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 * @uses $vars['sources']
 */

$sources = elgg_extract('sources', $vars);

foreach ($sources as $name => $url) {
	$feed_sources .= elgg_view('input/hidden', array(
		'name' => $name,
		'value' => $url,
		'class' => '_rss-feed-source'
	));
}

$content = <<<HTML
	<div class='elgg-rss-feed'>
		$feed_sources
	</div>
HTML;

echo $content;