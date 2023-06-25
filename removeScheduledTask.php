<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.2.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2023 Michel Mendiola
 * @license http://www.mozilla.org/MPL/2.0/
 */


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

db_extend('packages');

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = {string:name}',
	[
		'name' => 'emailInactiveUsers',
	]
);
