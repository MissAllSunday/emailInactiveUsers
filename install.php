<?php

/*
 * email Inactive Users
 *
 * @package eIU mod
 * @version 1.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/2.0/
 */


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $context;

db_extend('packages');

if (empty($context['uninstalling']))
{
	$table = array(
		'table_name' => '{db_prefix}inactive_users',
		'columns' => array(
			array(
				'name' => 'id_member',
				'type' => 'int',
				'size' => 10,
				'null' => false
			),
			array(
				'name' => 'mail',
				'type' => 'int',
				'size' => 1,
				'null' => false
			),
			array(
				'name' => 'delete',
				'type' => 'int',
				'size' => 1,
				'null' => false
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_member')
			),
		),
		'if_exists' => 'ignore',
		'error' => 'fatal',
		'parameters' => array(),
	);

	// Create the scheduled task
	$smcFunc['db_insert'](
		'insert',
		'{db_prefix}scheduled_tasks',
		array(
			'id_task' => 'int',
			'next_time' => 'int',
			'time_offset' => 'int',
			'time_regularity' => 'int',
			'time_unit' => 'string',
			'disabled' => 'int',
			'task' => 'string',
		),
		array(
			0, 0, 0, 1, 'd', 0, 'emailInactiveUsers',
		),
		array(
			'id_task',
		)
	);
}