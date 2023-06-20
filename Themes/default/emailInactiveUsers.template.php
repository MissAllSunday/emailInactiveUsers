<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

function template_user_list(): void
{
	global $context, $txt, $scripturl;

	// Any message?
	if (!empty($context['meiu'])) {
		foreach ($context['meiu'] as $messageKey) {
			echo '
	<div class="infobox" id="profile_success">
		', $txt['eiu_' . $messageKey], '
	</div>';
		}
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['eiu_list_title_potential'] ,'
		</h3>
	</div>';

	if (empty($context['toMark']))
		echo '
	<p class="information">
		', $txt['eiu_list_noUsers'] ,'
	</div>';

	else
	{
		echo '
	<script type="text/javascript">
		function checkAll(theForm) {
            let checkType = jQuery(theForm).attr("class");
          
            jQuery(theForm).change(function () {
				jQuery("input:checkbox." + checkType).prop("checked", jQuery(this).prop("checked"));
			 });
		}
	</script>';
		
		echo '
<div class="windowbg noup">
	<form 
		action="'. $scripturl .'?action=admin;area=eiu;sa=list;delete"
		method="post" 
		name="userlist" 
		id="userList">
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col" >
						', $txt['eiu_list_posts'] ,'
					</th>
					<th scope="col">
						', $txt['eiu_list_name'] ,'
					</th>
					<th scope="col">
						', $txt['eiu_list_login'] ,'
					</th>
					<th scope="col">
						', $txt['eiu_list_mail'] ,'
					</th>
					<th scope="col">
						', $txt['eiu_list_dont_delete'] ,'
						<input type="checkbox" class="dont" onclick="checkAll(this);" />
					</th>
					<th scope="col" class="check centercol">
						', $txt['eiu_list_delete'] ,'
 						<input type="checkbox" class="delete" onclick="checkAll(this);" />
					</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['toMark'] as $user)
			echo '
				<tr class="windowbg">
					<td class="centercol">
						', $user['posts'] ,'
					</td>
					<td>
						<a href="', $scripturl ,'?action=profile;u=', $user['id'] ,'" target="_blank">', $user['name'] ,'</a>
					</td>
					<td>
						', $user['last_login'] ,'
					</td>
					<td>
						', $user['mail_sent'] ,'
					</td>
					<td class="check centercol">
						<input type="checkbox" name="dont[]" class="input_check dont" value="', $user['id'] ,'">
					</td>
					<td class="check centercol">
						<input type="checkbox" name="user[]" class="input_check delete" value="', $user['id'] ,'">
					</td>
				</tr>';

		echo '
			</tbody>
		</table>
		<div class="flow_auto">
			<div class="additional_row">
				<input type="hidden" 
					id="'. $context['session_var'] .'" 
					name="'. $context['session_var'] .'"
					value="'. $context['session_id'] .'" />
				<input type="submit"
					value="'. $txt['eiu_list_send'] .'"
					class="button you_sure" />
			</div>	
		</div>
	</form>
</div>';
	}
}
