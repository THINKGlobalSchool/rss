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
 * @uses $vars['url']
 */

$url = elgg_extract('url', $vars);

$content = <<<HTML
	<div class='elgg-rss-feed' data-feed_url={$url}>
	</div>
HTML;

echo $content;