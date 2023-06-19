<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.1.1
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/2.0/
 */


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');

// Everybody likes hooks
$hooks = array(
	'integrate_pre_include' => '$sourcedir/emailInactiveUsers.php',
	'integrate_admin_areas' => 'eiu_admin_areas',
	'integrate_modify_modifications' => 'eiu_modifications',
	'integrate_menu_buttons' => 'eiu_menu',
);

foreach ($hooks as $hook => $function)
	remove_integration_function($hook, $function);
