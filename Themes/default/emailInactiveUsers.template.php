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
	<table class="table_grid" cellspacing="0" width="100%">
		<thead>
			<tr class="catbg">
				<th scope="col" class=" first_th">
				</th>
				<th scope="col" class=" first_th">
				</th>
				<th scope="col" class=" first_th">
				</th>
				<th scope="col" class=" last_th">

				</th>
			</tr>
		</thead>
		<tbody>
			<tr  id="letterm">
				<td class="windowbg2">
					name
				</td>
				<td class="windowbg lefttext">
					last login
				</td>
				<td class="windowbg2">
					main sent
				</td>
				<td class="windowbg2">
					delete
				</td>
			</tr>
		</tbody>
	</table>';
}