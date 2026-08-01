<?php

/**
 * githubSyncLite — admin/admin_config.php  (mode: config)
 *
 * Source repository settings, using e107's NATIVE e_admin_ui prefs system:
 * the $prefs array declares the fields, and the core renders the form, the
 * Save button, CSRF, and persistence (etrigger_save). No manual $_POST
 * handling. Values are read elsewhere via e107::getPlugConfig('githubSyncLite').
 *
 * Set once; the day-to-day screen is Core Sync (admin_sync.php).
 * Uses its own dispatcher; no dependency on the full githubSync plugin;
 * no database table.
 */

require_once('../../../class2.php');

if (!getperms('P'))
{
	e107::redirect('admin');
	exit;
}

e107_require_once('admin_menu.php'); // shared dispatcher


class githubSyncLite_config_ui extends e_admin_ui
{
	protected $pluginTitle = 'Github Sync Lite';
	protected $pluginName  = 'githubSyncLite';
	protected $table       = ''; // prefs only — no table
	protected $pid         = '';

	protected $defaultAction = 'prefs';

	/**
	 * Native prefs declaration. The core builds the form + Save from this.
	 * 'data' => 'str'/'int' routes each value through $tp->toDB on save.
	 */
	protected $prefs = array(
		'organization' => array(
			'title' => 'Organization',
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'str',
			'help'  => 'GitHub organization or user, e.g. Jimmi08',
			'writeParms' => array('size' => 'xlarge', 'default' => 'Jimmi08'),
		),
		'repo' => array(
			'title' => 'Repository',
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'str',
			'help'  => 'Repository name, e.g. e107-2.4.x-Lite',
			'writeParms' => array('size' => 'xlarge', 'default' => 'e107-2.4.x-Lite'),
		),
		'branch' => array(
			'title' => 'Branch',
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'str',
			'help'  => 'Branch to sync from, e.g. main',
			'writeParms' => array('size' => 'xlarge', 'default' => 'main'),
		),
		'public_repo' => array(
			'title' => 'Public repository',
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => 'On for a public repo (no token needed). Off for a private repo (token required).',
			'writeParms' => array('default' => 1),
		),
		'token' => array(
			'title' => 'GitHub token',
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'str',
			'help'  => 'Personal Access Token — required only for a private repository. Leave empty for a public repo.',
			'writeParms' => array('size' => 'xxlarge', 'maxlength' => 255, 'default' => ''),
		),
	);

	public function init()
	{
		// main-admin only: these settings drive on-disk overwrites.
		if (!getperms('0'))
		{
			e107::getMessage()->addError('Only the main admin can change the sync source.');
			e107::redirect(e_ADMIN . 'admin.php');
			exit;
		}
	}

	public function renderHelp()
	{
		$text  = 'The <strong>source repository</strong> is where Core Sync pulls the Lite core from.';
		$text .= '<ul>';
		$text .= '<li><strong>Organization / Repository / Branch</strong> — the GitHub location, '
			. 'e.g. <em>Jimmi08 / e107-2.4.x-Lite / main</em>.</li>';
		$text .= '<li><strong>Public repository</strong> — leave on for a public repo. Turn it off only '
			. 'for a private repo, which then needs a token.</li>';
		$text .= '<li><strong>GitHub token</strong> — a Personal Access Token, needed only for private '
			. 'repositories. It is also used (when present) to raise the API rate limit for the plugin-list '
			. 'refresh on the Core Sync screen.</li>';
		$text .= '</ul>';
		$text .= 'You normally set this once. The everyday screen is <strong>Core Sync</strong>.';

		return array(
			'caption' => LAN_HELP,
			'text'    => $text,
		);
	}
}


class githubSyncLite_config_form_ui extends e_admin_form_ui
{
}


new githubSyncLite_adminArea();

require_once(e_ADMIN . 'auth.php');
e107::getAdminUI()->runPage();

require_once(e_ADMIN . 'footer.php');
exit;
