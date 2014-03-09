emailInactiveUsers
===================

##### Sends emails to inactive users and deletes them if they haven't logged in on the forum.

##### Some features:

- Chose how many days the user needs to be inactive for the mod to send the mail.
- Chose how many days since the mail was sent for the user to be deleted.
- Customize the mail body and subject, you can use the following wildcards:
	- {user_name}' => user's real name,
	- {display_name}' => user's display name,
	- {last_login}' => user's last time s(he) logged in on the forum,
	- {forum_name}' the forum's name,
	- {forum_url}' => the forum's url,
- Check which usergroups will be affected by this mod. Main admin group cannot be affected.

##### Needs PHP 5.3 or greater, SMF 2.0 or greater.
