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

global $txt;

$txt['eiu_enable'] = 'Enable the mod';
$txt['eiu_enable_sub'] = 'The master setting, needs to be on for the mod to work.';
$txt['eiu_disable_removal'] = 'Disable the deletion of users feature';
$txt['eiu_disable_removal_sub'] = 'If checked, the mod will only send mails to those users who meet the criteria but will not delete them, after their grace period has ended and if the user hasn\'t log in yet, the mod will consider them again for another mail.';
$txt['eiu_title'] = 'Email Inactive Users';
$txt['eiu_general'] = 'General settings';
$txt['eiu_list'] = 'User list';
$txt['eiu_list_title'] = 'Users marked for deletion';
$txt['eiu_list_title_potential'] = 'Users marked for potential deletion';
$txt['eiu_list_will_be_deleted'] = 'Will be deleted';
$txt['eiu_list_noUsers'] = 'There aren\'t any users marked for potential deletion';
$txt['eiu_list_name'] = 'Name';
$txt['eiu_list_login'] = 'Last login';
$txt['eiu_list_mail'] = 'mail sent';
$txt['eiu_list_delete'] = 'Mark for deletion';
$txt['eiu_list_dont_delete'] = 'Don\'t delete';
$txt['eiu_list_posts'] = 'Posts';
$txt['eiu_list_send'] = 'Send';
$txt['eiu_deleted'] = 'You have successfully marked these users for deletion, the next time the scheduled task is executed, those users will be deleted.';
$txt['eiu_dont'] = 'You successfully marked these users as untouchables, the mod will not mark them again for deletion.';
$txt['eiu_desc'] = ' This is your main admin panel for the email inactive users mod. <br/> This mod will add a scheduled task where users who haven\'t been active in x days will be sent a customizable email, then a grace period starts and if the user hasn\'t been logged in after the grade period it gets marked for deletion, the admin then proceed to mark them for deletion, users marked for deletion will be delete the next time the scheduled task is executed, you can configure how many days.<br /> The mod uses the mail queue, if you have a large mail queue it is advisable to set a large number of days to wait before deletion, this is because the deletion time period starts to count after the admin has set an account for deletion and not after the mail was sent so its possible that the account will be deleted before getting the mail.<br /> The "don\'t delete" option allows you to permanently remove an user from getting deleted by this mod even if the user complies with all the other requirements.';
$txt['eiu_inactiveFor'] = 'How many days since their last log in to receive the email.';
$txt['eiu_inactiveFor_sub'] = 'In days, if no value is set, the mod will use the default value: 15';
$txt['eiu_sinceMail'] = 'How many days since the user was marked for deletion';
$txt['eiu_sinceMail_sub'] = 'In days, it starts to count after the admin has set an account for deletion. if no value was set, the mod will use the default value: 15';
$txt['eiu_groups'] = 'select the groups who will be affected by this mod';
$txt['eiu_groups_sub'] = 'This is a multi-select box, only the users who have the selected groups as primary or secondary groups would be affected by the mod, the default admin group cannot be selected.';
$txt['eiu_message'] = 'The mail body';
$txt['eiu_message_sub'] = 'You can use the following wildcards:<br />
	{user_name} will be converted to their real name<br />
	{display_name} will be converted to the user\'s display name.<br />
	{last_login} will show to them their last logged in date.<br />
	{forum_name} will print your forum\'s name<br />
	{forum_url} will print your forum\'s url<br />
	If no value is set, the mod will use the default message.';
$txt['eiu_subject'] = 'The subject for the mail';
$txt['eiu_subject_sub'] = 'Cannot use HTML here, if let empty the mod will use the default value.';
$txt['eiu_posts'] = 'Minimum posts made';
$txt['eiu_posts_sub'] = 'The number of posts the user made, if an user has more posts than this limit (s)he won\'t be considered by the mod.<br />If left empty, the mod will use the default value: 5.';
$txt['eiu_custom_message'] = 'Hello {user_name} we\'ve missed you at {forum_url} and we like you to come back and say hi again.';
$txt['eiu_custom_subject'] = 'Hello from {forum_name}.';
