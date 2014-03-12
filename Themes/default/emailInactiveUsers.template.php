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
	global $context, $txt, $scripturl;

	// Any message?
	if (!empty($context['meiu']))
		foreach($context['meiu'] as $m)
			echo '
	<div class="windowbg" id="profile_success">
		', $txt['eiu_'. $m] ,'
	</div>';

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
	<form action="', $scripturl ,'?action=admin;area=eiu;sa=list;delete" method="post" name="userlist" id="userList">
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
					<th scope="col">
						', $txt['eiu_list_delete'] ,'
					</th>
					<th scope="col" class=" last_th">
						', $txt['eiu_list_delete'] ,' <input type="checkbox" onchange="checkAll(this)" name="check_all" class="input_check">
					</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['toDelete'] as $user)
			echo '
				<tr  id="letterm">
					<td class="windowbg2">
						', $user['name'] ,'<br />
						', $txt['eiu_list_posts'] ,' ', $user['posts'] ,'
					</td>
					<td class="windowbg lefttext">
						', $user['last_login'] ,'
					</td>
					<td class="windowbg2">
						', $user['mail_sent'] ,'
					</td>
					<td class="windowbg2">
						<input type="checkbox" name="dont[]" class="input_check" value="', $user['id'] ,'">
					</td>
					<td class="windowbg2">
						<input type="checkbox" name="user[]" class="input_check" value="', $user['id'] ,'">
					</td>
				</tr>';

		echo '
			</tbody>
		</table>';

		echo'
			<div style="float:right;">
				<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" value="', $txt['eiu_list_send'] ,'" class="button_submit" />
			</div>
			<div class="clear"></div>';

		echo '
		</form>';
	}
}