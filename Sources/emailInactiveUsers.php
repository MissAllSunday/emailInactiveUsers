<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

if (!defined('SMF'))
	die('No direct access...');

function eiu_admin_areas(&$areas)
{
	global $txt;
	loadLanguage('emailInactiveUsers');

	$areas['config']['areas']['eiu'] = array(
		'label' => $txt['eiu_title'],
		'file' => 'emailInactiveUsers.php',
		'function' => 'eiu_subactions',
		'icon' => 'members.gif',
		'subsections' => array(
			'general' => array($txt['eiu_general']),
			'list' => array($txt['eiu_list'])
		),
	);
}

function eiu_subactions($return_config = false)
{
	global $txt, $scripturl, $context, $sourcedir, $modSettings;

	loadLanguage('emailInactiveUsers');

	require_once($sourcedir . '/ManageSettings.php');

	$context['page_title'] = $txt['eiu_title'];

	$subActions = array(
		'general' => 'eiu_settings',
		'list' => 'eiu_list',
	);

	loadGeneralSettingParameters($subActions, 'general');

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => $txt['eiu_desc'],
		'tabs' => array(
			'general' => array(
			),
			'list' => array(
			)
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function eiu_settings(&$return_config = false)
{
	global $context, $scripturl, $txt;

	$config_vars = array(
		array('check', 'eiu_enable', 'subtext' => $txt['eiu_enable_sub']),
		array('check', 'eiu_disable_removal', 'subtext' => $txt['eiu_disable_removal_sub']),
		array('int', 'eiu_inactiveFor', 'size' => 3, 'subtext' => $txt['eiu_inactiveFor_sub']),
		array('int', 'eiu_sinceMail', 'size' => 3, 'subtext' => $txt['eiu_sinceMail_sub']),
		array('int', 'eiu_posts', 'size' => 3, 'subtext' => $txt['eiu_posts_sub']),
	);

	// Are there any selectable groups?
	$groups = eiu_membergroups();

	if (!empty($groups))
		$config_vars[] = array('select', 'eiu_groups',
			$groups,
			'subtext' => $txt['eiu_groups_sub'],
			'multiple' => true,
		);

	$config_vars[] = array('large_text', 'eiu_message', '6', 'subtext' => $txt['eiu_message_sub']);
	$config_vars[] = array('text', 'eiu_subject', 'subtext' => $txt['eiu_subject_sub']);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=eiu;save;sa=general';
	$context['settings_title'] = $txt['eiu_title'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		checkSession();

		saveDBSettings($config_vars);
		redirectexit('action=admin;area=eiu;sa=general');
	}

	prepareDBSettingContext($config_vars);
}

function eiu_list()
{
	global $context, $txt, $smcFunc, $modSettings;

	loadTemplate('emailInactiveUsers');

	$context['sub_template'] = 'user_list';

	// Get the users ready to be marked for deletion.
	$context['toMark'] = eiu_getUsers();

	// Any message?
	if (!empty($_SESSION['meiu']))
	{
		$context['meiu'] = $_SESSION['meiu'];
		unset($_SESSION['meiu']);
	}

	// Saving?
	if (isset($_REQUEST['delete']))
	{
		$usersToMark = array();
		$usersToProtect = array();
		$_SESSION['meiu'] = array();

		checkSession('request', '', false);

		// Marking for deletion?
		if (!empty($_POST['user']))
		{
			// Safety.
			foreach ($_POST['user'] as $u)
				$usersToMark[] = (int) $u;

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET to_delete = {int:toDelete}
				WHERE id_member IN ({array_int:users})',
				array(
					'toDelete' => 3,
					'users' => $usersToMark,
				)
			);

			// Tell the user about it.
			$_SESSION['meiu'][] = 'deleted';
		}

		/* Marked as "untouchable"? code position is important here.
		If you decide to check both deletion and don't delete for the same user, this one will be the one who will prevail. */
		if (!empty($_POST['dont']))
		{
			// Safety.
			foreach ($_POST['dont'] as $u)
				$usersToProtect[] = (int) $u;

			$request = $smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET to_delete = {int:toDelete}, inactive_mail = 0
				WHERE id_member IN ({array_int:users})',
				array(
					'toDelete' => 4,
					'users' => $usersToProtect,
				)
			);

			// Tell the user about it.
			$_SESSION['meiu'][] = 'dont';
		}

		// Clean the old cache entry only if there was any change.
		if (!empty($_POST['dont']) || !empty($_POST['user']))
			cache_put_data('eiu_users-2', null, 120);

		// Redirect and tell the user.
		redirectexit('action=admin;area=eiu;sa=list');
	}
}

function eiu_menu(&$menu_buttons)
{
	global $scripturl, $txt;

	loadTemplate('emailInactiveUsers');
	loadLanguage('emailInactiveUsers');

	// Are there any users waiting for the final delete check?
	$users = eiu_getUsers();

	if (!empty($users))
		$menu_buttons['admin']['sub_buttons']['eiu'] = array(
			'title' => $txt['eiu_list_title'] .' ['. count($users) .']',
			'href' => $scripturl . '?action=admin;area=eiu;sa=list',
			'show' => true,
		);

	// If someone wants to do something with this info, let them.
	$context['eiu'] = $users;

	eiu_care();
}

function eiu_getUsers($to_delete = 2)
{
	global $smcFunc;

	if (($usersToDelete = cache_get_data('eiu_users-'. $to_delete, 3600)) == null)
	{
		// Get the users marked for deletion.
		$request = $smcFunc['db_query']('', '
				SELECT id_member, inactive_mail, last_login, member_name, real_name, posts, sent_mail
				FROM {db_prefix}members
				WHERE to_delete = {int:toDelete}',
				array(
					'toDelete' => $to_delete
				)
			);

		$usersToDelete = array();

		while($row = $smcFunc['db_fetch_assoc']($request))
			$usersToDelete[$row['id_member']] = array(
				'id' => $row['id_member'],
				'name' => !empty($row['member_name']) ? $row['member_name'] : $row['real_name'],
				'last_login' => timeformat($row['last_login']),
				'mail_sent' => timeformat($row['sent_mail']),
				'grace' => timeformat($row['inactive_mail']),
				'posts' => $row['posts'],
			);

		$smcFunc['db_free_result']($request);

		// You're not going to use this that often so give it an entire hour.
		cache_put_data('eiu_users-'. $to_delete, $usersToDelete, 3600);
	}

	return $usersToDelete;
}

/*
 * This function mimics SMF's Subs-Members::deleteMembers minus the checks and permissions.
 * Since this is meant to be executed by the scheduled task and the scheduled task can be executed by anyone, including guest and bots,
 * we gotta make sure no permissions/checks will be executed.
 * Suffice to say, this function shouldn't be run/used as replacement for the original one.
 */
function eiu_deleteMembers($users)
{
	global $sourcedir, $modSettings, $smcFunc;

	// Try give us a while to sort this out...
	@set_time_limit(600);
	// Try to get some more memory.
	if (@ini_get('memory_limit') < 128)
		@ini_set('memory_limit', '128M');

	// If it's not an array, make it so!
	if (!is_array($users))
		$users = array($users);
	else
		$users = array_unique($users);

	// Make sure there's no void user in here.
	$users = array_diff($users, array(0));

	// How many are they deleting?
	if (empty($users))
		return;

	else
		foreach ($users as $k => $v)
			$users[$k] = (int) $v;

	// Get their names for logging purposes.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name, CASE WHEN id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups) != 0 THEN 1 ELSE 0 END AS is_admin
		FROM {db_prefix}members
		WHERE id_member IN ({array_int:user_list})
		LIMIT ' . count($users),
		array(
			'user_list' => $users,
			'admin_group' => 1,
		)
	);
	$admins = array();
	$user_log_details = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['is_admin'])
			$admins[] = $row['id_member'];

		$user_log_details[$row['id_member']] = array($row['id_member'], $row['member_name']);
	}
	$smcFunc['db_free_result']($request);

	if (empty($user_log_details))
		return;

	// Make sure they aren't trying to delete administrators if they aren't one.  But don't bother checking if it's just themself.
	if (!empty($admins))
		$users = array_diff($users, $admins);
		foreach ($admins as $id)
			unset($user_log_details[$id]);

	// No one left?
	if (empty($users))
		return;

	// Log the action - regardless of who is deleting it.
	if (!empty($user_info['id']))
	{
		$log_inserts = array();
		foreach ($user_log_details as $user)
		{
			// Add it to the administration log for future reference.
			$log_inserts[] = array(
				time(), 3, $user_info['id'], $user_info['ip'], 'delete_member',
				0, 0, 0, serialize(array('member' => $user[0], 'name' => $user[1], 'member_acted' => $user_info['name'])),
			);

			// Remove any cached data if enabled.
			if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
				cache_put_data('user_settings-' . $user[0], null, 60);
		}

		// Do the actual logging...
		if (!empty($log_inserts) && !empty($modSettings['modlog_enabled']))
			$smcFunc['db_insert']('',
				'{db_prefix}log_actions',
				array(
					'log_time' => 'int', 'id_log' => 'int', 'id_member' => 'int', 'ip' => 'string-16', 'action' => 'string',
					'id_board' => 'int', 'id_topic' => 'int', 'id_msg' => 'int', 'extra' => 'string-65534',
				),
				$log_inserts,
				array('id_action')
			);
	}

	// Make these peoples' posts guest posts.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}messages
		SET id_member = {int:guest_id}, poster_email = {string:blank_email}
		WHERE id_member IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'blank_email' => '',
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}polls
		SET id_member = {int:guest_id}
		WHERE id_member IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	// Make these peoples' posts guest first posts and last posts.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_member_started = {int:guest_id}
		WHERE id_member_started IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_member_updated = {int:guest_id}
		WHERE id_member_updated IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_actions
		SET id_member = {int:guest_id}
		WHERE id_member IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_banned
		SET id_member = {int:guest_id}
		WHERE id_member IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_errors
		SET id_member = {int:guest_id}
		WHERE id_member IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	// Delete the member.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}members
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);

	// Delete the logs...
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_actions
		WHERE id_log = {int:log_type}
			AND id_member IN ({array_int:users})',
		array(
			'log_type' => 2,
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_boards
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_comments
		WHERE id_recipient IN ({array_int:users})
			AND comment_type = {string:warntpl}',
		array(
			'users' => $users,
			'warntpl' => 'warntpl',
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_group_requests
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_karma
		WHERE id_target IN ({array_int:users})
			OR id_executor IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_mark_read
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_notify
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_online
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_subscribed
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_topics
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}collapsed_categories
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);

	// Make their votes appear as guest votes - at least it keeps the totals right.
	//!!! Consider adding back in cookie protection.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_polls
		SET id_member = {int:guest_id}
		WHERE id_member IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	// Delete personal messages.
	require_once($sourcedir . '/PersonalMessage.php');
	deleteMessages(null, null, $users);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}personal_messages
		SET id_member_from = {int:guest_id}
		WHERE id_member_from IN ({array_int:users})',
		array(
			'guest_id' => 0,
			'users' => $users,
		)
	);

	// They no longer exist, so we don't know who it was sent to.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}pm_recipients
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);

	// Delete avatar.
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments(array('id_member' => $users));

	// It's over, no more moderation for you.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}moderators
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}group_moderators
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);

	// If you don't exist we can't ban you.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ban_items
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);

	// Remove individual theme settings.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}themes
		WHERE id_member IN ({array_int:users})',
		array(
			'users' => $users,
		)
	);

	// These users are nobody's buddy nomore.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, pm_ignore_list, buddy_list
		FROM {db_prefix}members
		WHERE FIND_IN_SET({raw:pm_ignore_list}, pm_ignore_list) != 0 OR FIND_IN_SET({raw:buddy_list}, buddy_list) != 0',
		array(
			'pm_ignore_list' => implode(', pm_ignore_list) != 0 OR FIND_IN_SET(', $users),
			'buddy_list' => implode(', buddy_list) != 0 OR FIND_IN_SET(', $users),
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET
				pm_ignore_list = {string:pm_ignore_list},
				buddy_list = {string:buddy_list}
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $row['id_member'],
				'pm_ignore_list' => implode(',', array_diff(explode(',', $row['pm_ignore_list']), $users)),
				'buddy_list' => implode(',', array_diff(explode(',', $row['buddy_list']), $users)),
			)
		);
	$smcFunc['db_free_result']($request);

	// Make sure no member's birthday is still sticking in the calendar...
	updateSettings(array(
		'calendar_updated' => time(),
	));

	updateStats('member');
}

function eiu_membergroups()
{
	global $smcFunc, $modSettings, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE id_group > {int:admin}',
		array(
			'admin' => 1,
		)
	);

	$return = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$return[$row['id_group']] = $row['group_name'];

	$smcFunc['db_free_result']($request);

	return $return;
}

/* DUH! WINNING! */
function eiu_care()
{
	global $context;

	if (isset($context['current_action']) && $context['current_action'] == 'credits')
		$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">Mail inactive users &copy Suki</a>';
}
