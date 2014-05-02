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
- Once the user has received the email and the grace period has expired, (s)he will appear on the mod's user list, on that list you can:
	- Mark the user for deletion. The user will be deleted the next time the scheduled task gets executed, there is a small chance for the user to skip deletion if (s)he logs in between the time the admin marked their account for deletion and the time the scheduled task gets executed, this timeframe, however could be very small and it cannot be larger than a full day.
	- Mark the user as "untouchable" This special setting tells the mod to completely ignore this account, that is, the account won't be eligible for deletion even if it complies with all of the criteria.
- A sub-menu in the admin button in the main menu will be added if the mod found 1 or more people ready to be marked for deletion.
- Option for disabling users removal.

##### Needs PHP 5.3 or greater, SMF 2.0 or greater.

##### License
This mod is license under the [MPL 2.0 license](http://www.mozilla.org/MPL/2.0/).

##### Note
Special thanks to [Kolya ](http://www.simplemachines.org/community/index.php?action=profile;u=8490) for allowing this mod to be open source and free for everyone.