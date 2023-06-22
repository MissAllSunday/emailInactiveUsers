<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.2
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2023 Michel Mendiola
 * @license http://www.mozilla.org/MPL/2.0/
 */

if (!defined('SMF'))
	die('No direct access...');

function eiu_admin_areas(array &$areas): void
{
	global $txt;

	loadLanguage('emailInactiveUsers');

	$areas['config']['areas']['eiu'] = [
		'label' => $txt['eiu_title'],
		'file' => 'emailInactiveUsers.php',
		'function' => 'eiu_subactions',
		'icon' => 'mail',
		'subsections' => [
			'general' => [$txt['eiu_general']],
			'list' => [$txt['eiu_list']]
		],
	];
}

function eiu_subactions(bool $return_config = false): void
{
	global $txt, $scripturl, $context, $sourcedir, $modSettings;

	loadLanguage('emailInactiveUsers');

	require_once($sourcedir . '/ManageSettings.php');

	$context['page_title'] = $txt['eiu_title'];

	$subActions = [
		'general' => 'eiu_settings',
		'list' => 'eiu_list',
	];

	loadGeneralSettingParameters($subActions, 'general');

	$context[$context['admin_menu_name']]['tab_data'] = [
		'title' => $context['page_title'],
		'description' => $txt['eiu_desc'],
		'tabs' => [
			'general' => [
			],
			'list' => [
			]
		],
	];

	$subActions[$_REQUEST['sa']]();
}

function eiu_settings(bool $return_config = false): array
{
	global $context, $scripturl, $txt;

	$config_vars = [
		['check', 'eiu_enable', 'subtext' => $txt['eiu_enable_sub']],
		['check', 'eiu_disable_removal', 'subtext' => $txt['eiu_disable_removal_sub']],
		['int', 'eiu_inactiveFor', 'size' => 3, 'subtext' => $txt['eiu_inactiveFor_sub']],
		['int', 'eiu_sinceMail', 'size' => 3, 'subtext' => $txt['eiu_sinceMail_sub']],
		['int', 'eiu_posts', 'size' => 3, 'subtext' => $txt['eiu_posts_sub']],
	];

	// Are there any selectable groups?
	$groups = eiu_membergroups();

	if (!empty($groups)) {
		$config_vars[] = ['select', 'eiu_groups',
			$groups,
			'subtext' => $txt['eiu_groups_sub'],
			'multiple' => true,
		];
	}

	$config_vars[] = ['large_text', 'eiu_message', '6', 'subtext' => $txt['eiu_message_sub']];
	$config_vars[] = ['text', 'eiu_subject', 'subtext' => $txt['eiu_subject_sub']];

	if ($return_config) {
		return $config_vars;
	}

	$context['post_url'] = $scripturl . '?action=admin;area=eiu;save;sa=general';
	$context['settings_title'] = $txt['eiu_title'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div>' . $txt['modification_no_misc_settings'] . '</div>';

		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		checkSession();

		saveDBSettings($config_vars);
		redirectexit('action=admin;area=eiu;sa=general');
	}

	prepareDBSettingContext($config_vars);

	return [];
}

function eiu_list(): void
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
		$usersToMark = [];
		$usersToProtect = [];
		$_SESSION['meiu'] = [];

		checkSession('request', '', false);

		// Marking for deletion?
		if (!empty($_POST['user']))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET to_delete = {int:toDelete}
				WHERE id_member IN ({array_int:users})',
				[
					'toDelete' => 3,
					'users' => array_map('intval', (array) $_POST['user']),
				]
			);

			// Tell the user about it.
			$_SESSION['meiu'][] = 'deleted';
		}

		/* Marked as "untouchable"? code position is important here.
		If you decide to check both deletion and don't delete for the same user,
		this one will be the one who will prevail. */
		if (!empty($_POST['dont']))
		{
			$request = $smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET to_delete = {int:toDelete}, inactive_mail = 0
				WHERE id_member IN ({array_int:users})',
				array(
					'toDelete' => 4,
					'users' => array_map('intval', (array) $_POST['dont']),
				)
			);

			// Tell the user about it.
			$_SESSION['meiu'][] = 'dont';
		}

		// Clean the old cache entry only if there was any change.
		if (!empty($_POST['dont']) || !empty($_POST['user'])) {
			cache_put_data('eiu_users-2', null, 120);
		}

		// Redirect and tell the user.
		redirectexit('action=admin;area=eiu;sa=list');
	}
}

function eiu_menu(array &$menu_buttons): void
{
	global $scripturl, $txt;

	loadTemplate('emailInactiveUsers');
	loadLanguage('emailInactiveUsers');

	// Are there any users waiting for the final delete check?
	$users = eiu_getUsers();

	if (!empty($users)) {
		$menu_buttons['admin']['sub_buttons']['eiu'] = [
			'title' => $txt['eiu_list_title'] . ' [' . count($users) . ']',
			'href' => $scripturl . '?action=admin;area=eiu;sa=list',
			'show' => true,
		];
	}

	// If someone wants to do something with this info, let them.
	$context['eiu'] = $users;

	eiu_care();
}

function eiu_getUsers(int $to_delete = 2): array
{
	global $smcFunc;

	if (($usersToDelete = cache_get_data('eiu_users-'. $to_delete, 3600)) === null)
	{
		// Get the users marked for deletion.
		$request = $smcFunc['db_query']('', '
				SELECT id_member, inactive_mail, last_login, member_name, real_name, posts, sent_mail
				FROM {db_prefix}members
				WHERE to_delete = {int:toDelete}',
				[
					'toDelete' => $to_delete
				]
			);

		$usersToDelete = [];

		while($row = $smcFunc['db_fetch_assoc']($request)) {
			$usersToDelete[$row['id_member']] = [
				'id' => $row['id_member'],
				'name' => !empty($row['member_name']) ? $row['member_name'] : $row['real_name'],
				'last_login' => timeformat($row['last_login']),
				'mail_sent' => timeformat($row['sent_mail']),
				'grace' => timeformat($row['inactive_mail']),
				'posts' => $row['posts'],
			];
		}

		$smcFunc['db_free_result']($request);

		// You're not going to use this that often so give it an entire hour.
		cache_put_data('eiu_users-'. $to_delete, $usersToDelete, 3600);
	}

	return $usersToDelete;
}

/*
 * This function mimics SMF's Subs-Members::deleteMembers minus the checks and permissions.
 * Since this is meant to be executed by the scheduled task and the scheduled task can be executed by anyone,
 * (including guest and bots), we have to make sure no permissions/checks will be executed.
 * Suffice to say, this function shouldn't be run/used as replacement for the original one.
 */
function eiu_deleteMembers(array $users): void
{
	global $sourcedir, $modSettings, $user_info, $smcFunc, $cache_enable;

	$users = array_filter(array_map('intval', array_unique($users)));

	if (empty($users)) {
		return;
	}

	@set_time_limit(600);
	setMemoryLimit('128M');

	// Get their names for logging purposes.
	$request = $smcFunc['db_query']('', '
	SELECT id_member, member_name
	FROM {db_prefix}members
	WHERE id_member IN ({array_int:user_list})
	LIMIT {int:limit}',
		array(
			'user_list' => $users,
			'limit' => count($users),
		)
	);

	$user_log_details = [];

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$user_log_details[$row['id_member']] = array($row['id_member'], $row['member_name']);
	}

	$smcFunc['db_free_result']($request);

	if (empty($user_log_details)) {
		return;
	}

	// Log the action - regardless of who is deleting it.
	$log_changes = [];
	foreach ($user_log_details as $user)
	{
		$log_changes[] = [
			'action' => 'delete_member',
			'log_type' => 'admin',
			'extra' => [
				'member' => $user[0],
				'name' => $user[1],
				'member_acted' => $user_info['name'],
			],
		];

		// Remove any cached data if enabled.
		if (!empty($cache_enable) && $cache_enable >= 2)
			cache_put_data('user_settings-' . $user[0], null, 60);
	}

	// Make these peoples' posts guest posts.
	$smcFunc['db_query']('', '
	UPDATE {db_prefix}messages
	SET id_member = {int:guest_id}' . (!empty($modSettings['deleteMembersRemovesEmail']) ? ',
		poster_email = {string:blank_email}' : '') . '
	WHERE id_member IN ({array_int:users})',
		[
			'guest_id' => 0,
			'blank_email' => '',
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	UPDATE {db_prefix}polls
	SET id_member = {int:guest_id}
	WHERE id_member IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	// Make these peoples' posts guest first posts and last posts.
	$smcFunc['db_query']('', '
	UPDATE {db_prefix}topics
	SET id_member_started = {int:guest_id}
	WHERE id_member_started IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	UPDATE {db_prefix}topics
	SET id_member_updated = {int:guest_id}
	WHERE id_member_updated IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	$smcFunc['db_query']('', '
	UPDATE {db_prefix}log_actions
	SET id_member = {int:guest_id}
	WHERE id_member IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	$smcFunc['db_query']('', '
	UPDATE {db_prefix}log_banned
	SET id_member = {int:guest_id}
	WHERE id_member IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	$smcFunc['db_query']('', '
	UPDATE {db_prefix}log_errors
	SET id_member = {int:guest_id}
	WHERE id_member IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	// Delete the member.
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}members
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// Delete any drafts...
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}user_drafts
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// Delete anything they liked.
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}user_likes
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// Delete their mentions
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}mentions
	WHERE id_member IN ({array_int:members})',
		[
			'members' => $users,
		]
	);

	// Delete the logs...
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_actions
	WHERE id_log = {int:log_type}
		AND id_member IN ({array_int:users})',
		[
			'log_type' => 2,
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_boards
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_comments
	WHERE id_recipient IN ({array_int:users})
		AND comment_type = {string:warntpl}',
		[
			'users' => $users,
			'warntpl' => 'warntpl',
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_group_requests
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_mark_read
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_notify
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_online
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_subscribed
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}log_topics
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// Make their votes appear as guest votes - at least it keeps the totals right.
	$smcFunc['db_query']('', '
	UPDATE {db_prefix}log_polls
	SET id_member = {int:guest_id}
	WHERE id_member IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	// Delete personal messages.
	require_once($sourcedir . '/PersonalMessage.php');
	deleteMessages(null, null, $users);

	$smcFunc['db_query']('', '
	UPDATE {db_prefix}personal_messages
	SET id_member_from = {int:guest_id}
	WHERE id_member_from IN ({array_int:users})',
		[
			'guest_id' => 0,
			'users' => $users,
		]
	);

	// They no longer exist, so we don't know who it was sent to.
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}pm_recipients
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// Delete avatar.
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments(array('id_member' => $users));

	// It's over, no more moderation for you.
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}moderators
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}group_moderators
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// If you don't exist we can't ban you.
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}ban_items
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// Remove individual theme settings.
	$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}themes
	WHERE id_member IN ({array_int:users})',
		[
			'users' => $users,
		]
	);

	// These users are nobody's buddy anymore.
	$request = $smcFunc['db_query']('', '
	SELECT id_member, pm_ignore_list, buddy_list
	FROM {db_prefix}members
	WHERE FIND_IN_SET({raw:pm_ignore_list}, pm_ignore_list) != 0 OR FIND_IN_SET({raw:buddy_list}, buddy_list) != 0',
		[
			'pm_ignore_list' => implode(', pm_ignore_list) != 0 OR FIND_IN_SET(', $users),
			'buddy_list' => implode(', buddy_list) != 0 OR FIND_IN_SET(', $users),
		]
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$smcFunc['db_query']('', '
		UPDATE {db_prefix}members
		SET
			pm_ignore_list = {string:pm_ignore_list},
			buddy_list = {string:buddy_list}
		WHERE id_member = {int:id_member}',
			[
				'id_member' => $row['id_member'],
				'pm_ignore_list' => implode(',', array_diff(explode(',', $row['pm_ignore_list']), $users)),
				'buddy_list' => implode(',', array_diff(explode(',', $row['buddy_list']), $users)),
			]
		);
	$smcFunc['db_free_result']($request);

	// Make sure no member's birthday is still sticking in the calendar...
	updateSettings(array(
		'calendar_updated' => time(),
	));

	// Integration rocks!
	call_integration_hook('integrate_delete_members', array($users));

	updateStats('member');

	require_once($sourcedir . '/Logging.php');
	logActions($log_changes);
}

function eiu_membergroups(): array
{
	global $smcFunc, $modSettings, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE id_group > {int:admin}',
		[
			'admin' => 1,
		]
	);

	$return = [];

	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$return[$row['id_group']] = $row['group_name'];
	}

	$smcFunc['db_free_result']($request);

	return $return;
}

/* DUH! WINNING! */
function eiu_care(): void
{
	global $context;

	if (!isset($context['current_action']) || $context['current_action'] !== 'credits') {
		return;
	}

	$context['copyrights']['mods'][] = '
		<a href="https:///missallsunday.com" target="_blank" title="Free SMF mods">
			Mail inactive users &copy Suki
		</a>';
}

/**
 * Send mails to inactive users, also delete them if they haven't logged in since then.
 *
 * Column to_delete holds the current state of the account:
 * 0 normal, up to date user
 * 1 mail has been sent, user starts her/his grace period after mail was sent.
 * 2 User has not been logged and her/his grace period is over, marked for admin deletion. These are the users who will appear on the mods user list.
 * 3 Admin has marked this user for deletion and will be deleted next time the scheduled task is executed.
 * 4 "Untouchable" means the user will not be fetched by the mod even if the account complies with all of the criteria to be marked for deletion.
 * Column inactive_mail holds a different unix timestamp depending on the account current state:
 * 0 if the user is up to date.
 * It will hold the date the mail was sent to the mail queue when the user to_delete column is set to 1. This time is then used to check if their grace period is over or for resetting their status.
 *
 * @return boolean true The code inside the scheduled task was executed.
 */
function scheduled_emailInactiveUsers(): bool
{
	global $smcFunc, $modSettings, $txt, $sourcedir, $mbname;
	global $scripturl;

	// The mod must be enabled
	if (empty($modSettings['eiu_enable'])) {
		return true;
	}

	loadLanguage('emailInactiveUsers');

	// Today is a good day to do stuff don't you think?
	$today = time();
	$additionalGroups = [];

	// Is there any custom message?
	$customMessage = !empty($modSettings['eiu_message']) ? $modSettings['eiu_message'] : $txt['eiu_custom_message'];
	$customSubject = !empty($modSettings['eiu_subject']) ? $modSettings['eiu_subject'] : $txt['eiu_custom_subject'];
	$postLimit = !empty($modSettings['eiu_posts']) ? $modSettings['eiu_posts'] : 5;

	// How many days must the user needs to be inactive to get the mail? lets stay safe here and declare a default value too.
	$inactiveFor = 86400 * (!empty($modSettings['eiu_inactiveFor']) ? $modSettings['eiu_inactiveFor'] : 15);

	// The user hasn't been logged in since the mail was sent huh? how many days are we gonna wait until the account gets marked for deletion?
	$sinceMail = 86400 * (!empty($modSettings['eiu_sinceMail']) ? $modSettings['eiu_sinceMail'] : 15);

	// The groups from which the users will be fetched from.
	$inGroups = !empty($modSettings['eiu_groups']) ? $smcFunc['json_decode']($modSettings['eiu_groups']) : [];

	// There's gotta be at least 1 group.
	if (empty($inGroups) || !is_array($inGroups))
		return true;

	// Don't count the main admin group, AKA id_group 1
	$inGroups = array_diff($inGroups, [1]);

	// We gotta do a nasty thing here, we have to format a "FIND_IN_SET" for each selected group. Thanks to this we need PHP 5.3
	$additionalGroups = array_map(
		function($k) {
			return ' OR FIND_IN_SET('. $k .', additional_groups)';
		}, $inGroups);

	// For those who still want to use this but don't have php 5.3
	/* foreach ($inGroups as $k)
			$additionalGroups[] = ' OR FIND_IN_SET('. $k .', additional_groups)'; */

	// Right, we got all we need, lets do some expensive queries.
	$request = $smcFunc['db_query']('', '
			SELECT id_member, email_address, inactive_mail, member_name, real_name, last_login
			FROM {db_prefix}members
			WHERE inactive_mail = 0
				AND posts <= {int:postLimit}
				AND last_login < {int:inactiveFor}
				AND date_registered < {int:inactiveFor}
				AND is_activated = 1
				AND to_delete = 0
				AND email_address is NOT NULL
				AND (id_group IN ({array_int:groups})
					OR id_post_group IN ({array_int:groups})
					'. (implode(' ', $additionalGroups)). ')',
		array(
			'inactiveFor' => $today - $inactiveFor,
			'groups' => $inGroups,
			'postLimit' => $postLimit,
		)
	);

	$messages = [];

	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Lets create the message. Replace our wildcards with the actual data.
		$replacements = [
			'{user_name}' => $row['real_name'],
			'{display_name}' => $row['member_name'],
			'{last_login}' => timeformat($row['last_login']),
			'{forum_name}' => $mbname,
			'{forum_url}' => $scripturl,
		];

		// Split the replacements up into two arrays, for use with str_replace.
		$find = [];
		$replace = [];

		foreach ($replacements as $f => $r)
		{
			$find[] = $f;
			$replace[] = $r;
		}

		$messages[$row['id_member']] = [
			'message' => str_replace($find, $replace, $customMessage),
			'mail' => $row['email_address'],
			'subject' => str_replace($find, $replace, $customSubject),
		];
	}

	// Do we find someone?
	if (!empty($messages))
	{
		// Gotta use a function in a far far away file...
		require_once($sourcedir . '/Subs-Post.php');

		// Send the mail away!
		foreach($messages as $m)
			sendmail($m['mail'], $m['subject'], $m['message'], null, null, false);

		// OK, next thing, mark those users. Set sent_mail to the date the mail was sent, set inactive_mail to the future date where their grace period will end. Set to_delete as 1
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET sent_mail = {int:today}, to_delete = {int:to_delete}, inactive_mail = {int:sinceMail}
			WHERE id_member IN ({array_int:id_members})',
			array(
				'sinceMail' => $today + $sinceMail,
				'today' => $today,
				'id_members' => array_keys($messages),
				'to_delete' => 1,
			)
		);
	}

	// Next step. Find out if any user marked for potential deletion or deletion has logged in since then and reset their status. Don't reset the untouchable status...
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}members
		SET inactive_mail = {int:cero}, to_delete = {int:cero}, sent_mail = {int:cero}
		WHERE sent_mail > {int:cero}
			AND last_login > sent_mail
			AND to_delete >= {int:cero}
			AND to_delete <= {int:tres}',
		[
			'cero' => 0,
			'tres' => 3,
		]
	);

	/* Next. Find all users whose email was sent and see if their grace period time has expired, if so, mark them for deletion. If deletion is disabe then reset their status back to 0.
	 * Include the group check since its possible the users has changed their group since then.
	 * It is also possible for an user to log in at the very last minute that is, after the admin set the account for deletion but before the next scheduled task is executed... lucky bastard! */
	$request = $smcFunc['db_query']('', '
		UPDATE {db_prefix}members
		SET to_delete = '. (!empty($modSettings['eiu_disable_removal']) ? '{int:cero}' : '{int:dos}') .'
		WHERE inactive_mail < {int:today}
			AND last_login < inactive_mail
			AND posts <= {int:postLimit}
			AND date_registered < {int:today}
			AND is_activated = {int:uno}
			AND to_delete = {int:uno}
			AND (id_group IN ({array_int:groups})
				OR id_post_group IN ({array_int:groups})
				'. (!empty($additionalGroups) ? implode(' ', $additionalGroups) : ''). ')',
		[
			'groups' => $inGroups,
			'postLimit' => $postLimit,
			'cero' => 0,
			'uno' => 1,
			'dos' => 2,
			'today' => $today,
		]
	);

	// We don't want to delete them...
	if (!empty($modSettings['eiu_disable_removal']))
		return true;

	// Last step. Find those user the admin has decided to delete. No further checks here, it is all based on having "to_delete" set to 3.
	$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE to_delete = {int:toDelete}',
		array(
			'toDelete' => 3,
		)
	);

	$usersToDelete = [];

	while($row = $smcFunc['db_fetch_assoc']($request))
		$usersToDelete[] = $row['id_member'];

	// Any lucky ones?
	if (!empty($usersToDelete))
	{
		// This is a very expensive function :(
		eiu_deleteMembers($usersToDelete);

		// Re-build the "to delete" cache.
		cache_put_data('eiu_users-2', null, 3600);
	}

	// And... we're done!
	return true;
}
