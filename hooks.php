<?php

/*
 * email Inactive Users
 *
 * @package eIU mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Everybody likes hooks
$hooks = array(
	'integrate_pre_include' => '$sourcedir/emailInactiveUsers.php',
);

foreach ($hooks as $hook => $function)
	add_integration_function($hook, $function);
