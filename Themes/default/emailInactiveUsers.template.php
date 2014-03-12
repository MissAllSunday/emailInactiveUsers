<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

function template_user_list()
{
	global $context, $txt, $cripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['eiu_list_title'] ,'
		</h3>
	</div>';

	if (empty($context['toDelete']))
		echo '
	<div class="windowbg nopadding">
		<span class="topslice">
			<span></span>
		</span>
		<div class="content">
			', $txt['eiu_list_noUsers'] ,'
		</div>
		<span class="botslice">
			<span></span>
		</span>
	</div>';

	else
	{
		echo '
	<form action="" method="post" name="userlist" id="userList">
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th scope="col" class=" first_th">
						', $txt['eiu_list_name'] ,'
					</th>
					<th scope="col">
						', $txt['eiu_list_login'] ,'
					</th>
					<th scope="col">
						', $txt['eiu_list_mail'] ,'
					</th>
					<th scope="col" class=" last_th">
						', $txt['eiu_list_delete'] ,' <input type="checkbox" onchange="checkAll()" name="check_all">
					</th>
				</tr>
			</thead>
			<tbody>';

			foreach ($context['toDelete'] as $user)
				echo '
				<tr  id="letterm">
					<td class="windowbg2">
						', $user['name'] ,'
					</td>
					<td class="windowbg lefttext">
						', $user['login'] ,'
					</td>
					<td class="windowbg2">
						', $user['mail'] ,'
					</td>
					<td class="windowbg2">
						<input type="checkbox" name="user[]" class="userID" value="', $user['id'] ,'">
					</td>
				</tr>
			</tbody>
		</table>';

		echo'
			<div style="float:right;">
				<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" value="', $txt['Breeze_noti_send'] ,'" class="button_submit" />
			</div>
			<div class="clear"></div>';

		echo '
		</form>';
	}
}