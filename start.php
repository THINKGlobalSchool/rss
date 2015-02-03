<?php
/**
 * Elgg RSS start.php
 *
 * @package RSS
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.com/
 *
 */

elgg_register_event_handler('init', 'system', 'rss_init');

function rss_init() {
	// Register and load library
	elgg_register_library('elgg:rss', elgg_get_plugins_path() . 'rss/lib/rss.php');
	elgg_load_library('elgg:rss');

	// Register simplepie library
	elgg_register_library('elgg:simplepie', elgg_get_plugins_path() . 'rss/vendors/simplepie.inc');

	// Page handler
	elgg_register_page_handler('rss','rss_page_handler');

	// Register CSS
	$r_css = elgg_get_simplecache_url('css', 'rss/css');
	elgg_register_css('elgg.rss', $r_css);
	elgg_load_css('elgg.rss');
		
	// Register JS library
	$r_js = elgg_get_simplecache_url('js', 'rss/rss');
	elgg_register_js('elgg.rss', $r_js);
	elgg_load_js('elgg.rss');

	// Register jquery feeds JS library
	$r_js = elgg_get_simplecache_url('js', 'rss/jquery_feeds');
	elgg_register_js('jquery.feeds', $r_js);

	elgg_load_js('jquery.feeds');
	
	// Add to main menu
	$item = new ElggMenuItem('rss', elgg_echo('rss'), 'rss');
	elgg_register_menu_item('site', $item);

	// Customize the rss feed embed entity menu
	if (elgg_is_active_plugin('tgsembed')) {
		elgg_register_plugin_hook_handler('register', 'menu:simpleicon-entity', 'rss_feed_setup_simpleicon_entity_menu');
	}

	// Notifications
	elgg_register_notification_event('object', 'rss_feed', array('create'));
	elgg_register_plugin_hook_handler('prepare', 'notification:publish:object:rss_feed', 'rss_prepare_notification');

	
	// Register actions
	$action_base = elgg_get_plugins_path() . 'rss/actions/rss';
	elgg_register_action('rss/save', "$action_base/save.php");
	elgg_register_action('rss/delete', "$action_base/delete.php");
	elgg_register_action('rss/validate', "$action_base/validate.php");

	// Entity url and icon handlers
	elgg_register_plugin_hook_handler('entity:url', 'object', 'rss_url_handler');

	// Register type
	elgg_register_entity_type('object', 'rss_feed');
	
	// Add group option
	add_group_tool_option('rss', elgg_echo('rss:enablegroup'), TRUE);
	
	// Profile block hook	
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'rss_owner_block_menu');

	// Ajax whitelist
	elgg_register_ajax_view('rss/embeddable');
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
 * Returns the URL from an rss entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function rss_url_handler($hook, $type, $url, $params) {
	$entity = $params['entity'];

	// Check that the entity is an rss object
	if (!elgg_instanceof($entity, 'object', 'rss_feed')) {
		return;
	}

	return "rss/view/{$entity->guid}/";
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
 * Add 'embed rss' item for photo simpleicon entity menu
 *
 * @param sting  $hook   view
 * @param string $type   input/tags
 * @param mixed  $return  Value
 * @param mixed  $params Params
 *
 * @return array
 */
function rss_feed_setup_simpleicon_entity_menu($hook, $type, $return, $params) {
	if (get_input('embed_spot_content')) {
		$entity = $params['entity'];
		
		if (elgg_instanceof($entity, 'object', 'rss_feed')) {
			// Item to add object to portfolio
			$options = array(
				'name' => 'embed_feed',
				'text' => elgg_echo('rss:label:embedfeed'),
				'title' => 'embed_feed',
				'href' => "#{$entity->guid}",
				'class' => 'elgg-rss-embed-feed elgg-button elgg-button-action',
				'section' => 'info',
			);
			
			$return[] = ElggMenuItem::factory($options);
			return $return;
		}
	}
	return $return;
}

/**
 * Prepare a notification message about a new rss feed
 *
 * @param string                          $hook         Hook name
 * @param string                          $type         Hook type
 * @param Elgg_Notifications_Notification $notification The notification to prepare
 * @param array                           $params       Hook parameters
 * @return Elgg_Notifications_Notification
 */
function rss_prepare_notification($hook, $type, $notification, $params) {
	$entity = $params['event']->getObject();
	$owner = $params['event']->getActor();
	$recipient = $params['recipient'];
	$language = $params['language'];
	$method = $params['method'];

	// Title for the notification
	$notification->subject = elgg_echo('rss:notification:subject');

    // Message body for the notification
	$notification->body = elgg_echo('rss:notification:body', array(
			$owner->name,
			$entity->title,
			$entity->description,
			$entity->getURL()
	), $language);

    // The summary text is used e.g. by the site_notifications plugin
    $notification->summary = elgg_echo('rss:notification:summary', array($entity->title), $language);

    return $notification;
}