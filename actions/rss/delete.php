<?php
/**
 * Elgg RSS Delete Action
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

$guid = get_input('guid', null);
$rss = get_entity($guid);

if (elgg_instanceof($rss, 'object', 'rss_feed') && $rss->canEdit()) {
	$container = get_entity($rss->container_guid);
	if ($rss->delete()) {
		system_message(elgg_echo('rss:success:delete'));
		if (elgg_instanceof($container, 'group')) {
			forward("rss/group/{$container->guid}/owner");
		} else {
			forward("rss/owner/{$container->username}");
		}
	} else {
		register_error(elgg_echo('rss:error:delete'));
	}
} else {
	register_error(elgg_echo('rss:error:notfound'));
}

forward(REFERER);