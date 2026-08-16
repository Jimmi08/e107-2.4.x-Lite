<?php

/**
 * githubSyncLite — shared admin dispatcher.
 *
 * Standalone: no dependency on the full githubSync plugin.
 * Four modes:
 *   sync   (default) — core sync + plugin selection   (admin_sync.php)
 *   config           — source repository settings      (admin_config.php)
 *   debug            — connection diagnostics          (admin_debug.php)
 *   fixes            — one-click post-sync repairs       (admin_fixes.php)
 */

e107::coreLan('db', true); // DBLAN_* copy reused on the confirmation screen

class githubSyncLite_adminArea extends e_admin_dispatcher
{
	protected $defaultMode   = 'sync';
	protected $defaultAction = 'main';

	protected $modes = array(
		'sync' => array(
			'controller' => 'githubSyncLite_ui',
			'path'       => null,
			'ui'         => 'githubSyncLite_form_ui',
			'uipath'     => null,
		),
		'config' => array(
			'controller' => 'githubSyncLite_config_ui',
			'path'       => null,
			'ui'         => 'githubSyncLite_config_form_ui',
			'uipath'     => null,
		),
		'debug' => array(
			'controller' => 'githubSyncLite_debug_ui',
			'path'       => null,
			'ui'         => 'githubSyncLite_debug_form_ui',
			'uipath'     => null,
		),
		'fixes' => array(
			'controller' => 'githubSyncLite_fixes_ui',
			'path'       => null,
			'ui'         => 'githubSyncLite_fixes_form_ui',
			'uipath'     => null,
		),
	);

	protected $adminMenu = array(
		'sync/main' => array(
			'caption' => 'Core Sync',
			'perm'    => '0',
			'icon'    => 'fas-sync',
			'url'     => '{e_PLUGIN}githubSyncLite/admin/admin_sync.php',
		),
		'config/prefs' => array(
			'caption' => 'Source',
			'perm'    => '0',
			'icon'    => 'fas-cog',
			'url'     => '{e_PLUGIN}githubSyncLite/admin/admin_config.php',
		),
		'debug/main' => array(
			'caption' => 'Diagnostics',
			'perm'    => '0',
			'icon'    => 'fas-stethoscope',
			'url'     => '{e_PLUGIN}githubSyncLite/admin/admin_debug.php',
		),
		'fixes/main' => array(
			'caption' => 'Site fixes',
			'perm'    => '0',
			'icon'    => 'fas-wrench',
			'url'     => '{e_PLUGIN}githubSyncLite/admin/admin_fixes.php',
		),
	);

	protected $menuTitle = 'Github Sync Lite';
}
