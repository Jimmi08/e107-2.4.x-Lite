<?php

/**
 * githubSyncLite — shared admin dispatcher.
 *
 * Standalone: no dependency on the full githubSync plugin.
 * Two modes:
 *   sync   (default) — core sync + plugin selection   (admin_sync.php)
 *   config           — source repository settings      (admin_config.php)
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
	);

	protected $menuTitle = 'Github Sync Lite';
}
