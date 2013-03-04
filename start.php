<?php
/**
 * Elgg RSS start.php
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 *
 */

elgg_register_event_handler('init', 'system', 'rss_init');

function rss_init() {
	// Register and load library
	elgg_register_library('elgg:rss', elgg_get_plugins_path() . 'rss/lib/rss.php');
	elgg_load_library('elgg:rss');

	// Page handler
	elgg_register_page_handler('rss','rss_page_handler');

	// Register CSS
	$r_css = elgg_get_simplecache_url('css', 'rss/css');
	elgg_register_simplecache_view('css/rss/css');
	elgg_register_css('elgg.rss', $r_css);
		
	// Register JS library
	$r_js = elgg_get_simplecache_url('js', 'rss/rss');
	elgg_register_simplecache_view('js/rss/rss');
	elgg_register_js('elgg.rss', $r_js);
	
	// Add to main menu
	$item = new ElggMenuItem('rss', elgg_echo('rss'), 'rss');
	elgg_register_menu_item('site', $item);

	// Notifications
	register_notification_object('object', 'rss_feed', elgg_echo('rss:notification:subject'));
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'rss_notify_message');
	
	// Register actions
	$action_base = elgg_get_plugins_path() . 'rss/actions/rss';
	elgg_register_action('rss/save', "$action_base/save.php");
	elgg_register_action('rss/delete', "$action_base/delete.php");

	// Entity url and icon handlers
	elgg_register_entity_url_handler('object', 'rss_feed', 'rss_url_handler');

	// Register type
	elgg_register_entity_type('object', 'rss_feed');
	
	// Add group option
	add_group_tool_option('rss', elgg_echo('rss:enablegroup'), TRUE);
	
	// Profile block hook	
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'rss_owner_block_menu');
}

/**
 * Serves pages for URLs like:
 *
 *  All feeds:       rss/all
 *  User's feeds:    rss/owner/<username>
 *  Friends' feeds:  rss/friends/<username>
 *  View feed:       rss/view/<guid>/<title>
 *  New feed:        rss/add/<guid>
 *  Edit feed:       rss/edit/<guid>
 *  Group feeds:     rss/group/<guid>/owner
 *
 * @param string $page
 */
function rss_page_handler($page) {
	elgg_push_context('rss');
	elgg_push_breadcrumb(elgg_echo('rss'), 'rss');
	
	elgg_load_css('elgg.rss');
	elgg_load_js('elgg.rss');
	
	$page_type = $page[0];

	switch ($page_type) {
		case 'owner': 
			$user = get_user_by_username($page[1]);
			$params = rss_get_page_content_list($user->guid);
			break;
		case 'friends': 
			$user = get_user_by_username($page[1]);
			$params = rss_get_page_content_friends($user->guid);
			break;
		case 'group': 
			$params = rss_get_page_content_list($page[1]);
			break;
		case 'add':
			$params = rss_get_page_content_edit($page_type, $page[1]);
			break;
		case 'edit':
			$params = rss_get_page_content_edit($page_type, $page[1]);
			break;
		case 'view': 
			$params = rss_get_page_content_view($page[1]);
			break;
		case 'all':
		default:
			$params = rss_get_page_content_list();
			break;
	}
	
	$body = elgg_view_layout($params['layout'] ? $params['layout'] : 'content', $params);
	echo elgg_view_page($params['title'], $body);

	return TRUE;
}

/**
 * Populates the getUrl() method for a rss feeds
 *
 * @param ElggEntity entity
 * @return string request url
 */
function rss_url_handler($entity) {
	return elgg_get_site_url() . "rss/view/{$entity->guid}/";
}

/**
 * Plugin hook to add rss feeds to the profile block
 * 	
 * @param unknown_type $hook
 * @param unknown_type $type
 * @param unknown_type $value
 * @param unknown_type $params
 * @return unknown
 */
function rss_owner_block_menu($hook, $type, $value, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "rss/owner/{$params['entity']->username}";
		$item = new ElggMenuItem('rss', elgg_echo('rss'), $url);
		$value[] = $item;
	} else {
		if ($params['entity']->rss_enable == 'yes') {
			$url = "rss/group/{$params['entity']->guid}/all";
			$item = new ElggMenuItem('rss', elgg_echo('rss:label:groupfeeds'), $url);
			$value[] = $item;
		}
	}
	return $value;
}

/**
 * Set the notification message for rss feeds
 * 
 * @param string $hook    Hook name
 * @param string $type    Hook type
 * @param string $message The current message body
 * @param array  $params  Parameters about the blog posted
 * @return string
 */
function rss_notify_message($hook, $type, $message, $params) {
	$entity = $params['entity'];
	$to_entity = $params['to_entity'];
	$method = $params['method'];
	if (elgg_instanceof($entity, 'object', 'rss_feed')) {
		$descr = $entity->description;
		$title = $entity->title;
		$owner = $entity->getOwnerEntity();
		return elgg_echo('rss:notification:body', array(
			$owner->name,
			$title,
			$descr,
			$entity->getURL()
		));
	}
	return null;
}