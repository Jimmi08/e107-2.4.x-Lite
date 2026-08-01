<?php

/**
 * githubSyncLite — admin/admin_sync.php  (mode: sync, default)
 *
 * The everyday screen:
 *   (1) Core sync — downloads the Lite core from the configured repo,
 *       EXCLUDING eplugins/ (handled by the LITE-modified engine copy).
 *   (2) Plugins — plugin list from the SYSTEM cache as checkboxes, with a
 *       manual "Refresh plugin cache" (one GitHub API call). Display only
 *       for now; selection is wired up in a later phase.
 *
 * Source settings live on the separate "Source" screen (admin_config.php),
 * read here via e107::getPlugConfig('githubSyncLite'). Uses the e107 admin
 * dispatcher (e_admin_ui + runPage) so the admin header/footer and language
 * constants load correctly. No sync logic here — delegated to the bundled
 * engine copy. No table; no dependency on the full githubSync plugin.
 */

require_once('../../../class2.php');

if (!getperms('P'))
{
	e107::redirect('admin');
	exit;
}

e107_require_once('admin_menu.php'); // shared dispatcher
e107_require_once(e_PLUGIN . 'githubSyncLite/includes/github_sync_engine.php'); // bundled engine (core sync skips eplugins/)
e107_require_once(e_PLUGIN . 'githubSyncLite/includes/plugin_list.php');         // plugin-folder list + system cache


class githubSyncLite_ui extends e_admin_ui
{
	protected $pluginTitle = 'Github Sync Lite';
	protected $pluginName  = 'githubSyncLite';
	protected $table       = ''; // prefs only — no table
	protected $pid         = '';

	protected $defaultAction = 'main';

	/**
	 * Current source repo, read from plugin prefs (set on the Source screen).
	 */
	protected function sourceConfig()
	{
		$cfg = e107::getPlugConfig('githubSyncLite');

		return array(
			'organization' => $cfg->get('organization', ''),
			'repo'         => $cfg->get('repo', ''),
			'branch'       => $cfg->get('branch', ''),
			'token'        => $cfg->get('token', ''),
			'public_repo'  => (int) $cfg->get('public_repo', 1),
		);
	}

	/**
	 * True when organization/repo/branch are all set.
	 */
	protected function sourceIsSet()
	{
		$c = $this->sourceConfig();
		return ($c['organization'] !== '' && $c['repo'] !== '' && $c['branch'] !== '');
	}

	public function mainPage()
	{
		$this->addTitle('Core Sync');

		$mes = e107::getMessage();
		$frm = e107::getForm();
		$req = $this->getRequest();

		// main-admin only: this overwrites core files on disk
		if (!getperms('0'))
		{
			$mes->addError('Only the main admin can use Github Sync Lite.');
			return $mes->render();
		}

		// Source must be configured first.
		if (!$this->sourceIsSet())
		{
			$mes->addInfo('No source repository set yet. Open the <strong>Source</strong> screen and save '
				. 'the organization, repository and branch first.');
			return $mes->render();
		}

		// --- POST: run core sync --------------------------------------------
		if ($req->getPosted('run_core_sync'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			else
			{
				$this->runCoreSync();
			}
		}

		// --- POST: refresh plugin cache (one deliberate GitHub API call) ------
		if ($req->getPosted('refresh_plugins'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			else
			{
				githubSyncLite_plugin_list::clearCache();
				$list = githubSyncLite_plugin_list::refresh($this->sourceConfig());
				if ($list !== false)
				{
					$mes->addSuccess(count($list) . ' plugin folder(s) found in the repo and cached.');
				}
			}
		}

		$c = $this->sourceConfig();

		// --- Core sync form -------------------------------------------------
		$repoUrl = $c['public_repo']
			? "https://github.com/{$c['organization']}/{$c['repo']}/tree/{$c['branch']}"
			: '';

		$body  = '<p>Downloads the Lite <strong>core</strong> from <strong>'
			. htmlspecialchars($c['organization'] . '/' . $c['repo'], ENT_QUOTES, 'utf-8')
			. '</strong> (branch <strong>' . htmlspecialchars($c['branch'], ENT_QUOTES, 'utf-8')
			. '</strong>) into <strong>' . e_SYSTEM . 'temp</strong> and extracts it, '
			. '<strong>overwriting existing core files</strong>. The contents of '
			. '<strong>eplugins/</strong> are not touched.</p>';
		if ($repoUrl !== '')
		{
			$safe = htmlspecialchars($repoUrl, ENT_QUOTES, 'utf-8');
			$body .= "<p>Source: <a href='{$safe}' target='_blank' rel='noopener'>{$safe}</a> "
				. "&middot; change it on the <strong>Source</strong> screen.</p>";
		}

		$run  = $frm->open('gsl_core', 'post', e_SELF . '?mode=sync&action=main');
		$run .= $frm->token();
		$run .= $body;
		$run .= $frm->admin_button('run_core_sync', 1, 'delete', 'Run core sync');
		$run .= $frm->close();

		// --- Plugin selection (display only) --------------------------------
		$plugins = $this->renderPluginSelection();

		$out  = $mes->render();
		$out .= e107::getRender()->tablerender('Core sync (excludes eplugins/)', $run, 'gsl-core', true);
		$out .= e107::getRender()->tablerender('Plugins', $plugins, 'gsl-plugins', true);

		return $out;
	}

	/**
	 * Delegates the work to the bundled engine (type 'core', which the
	 * LITE-modified engine copy extracts WITHOUT eplugins/).
	 */
	protected function runCoreSync()
	{
		$mes = e107::getMessage();
		$c   = $this->sourceConfig();

		if (version_compare(PHP_VERSION, '7.4', '<'))
		{
			$mes->addError('Github Sync Lite requires PHP 7.4 or newer. You are on PHP ' . PHP_VERSION . '.');
			return;
		}

		$engine = new github_sync_engine();
		$result = $engine->sync(array(
			'organization' => $c['organization'],
			'repo'         => $c['repo'],
			'branch'       => $c['branch'],
			'folder'       => '',      // unused for 'core'
			'type'         => 'core',  // engine skips eplugins/
			'token'        => $c['token'],
			'public_repo'  => $c['public_repo'],
		));

		if ($result === false)
		{
			return; // engine already reported the reason
		}

		if (!empty($result['success']))
		{
			$mes->addSuccess(count($result['success']) . ' file(s)/folder(s) synced.');
		}
		if (!empty($result['skipped']))
		{
			$mes->addInfo(count($result['skipped']) . ' item(s) skipped (includes eplugins/, which core sync never writes).');
		}
		if (!empty($result['error']))
		{
			$failed = array_map(static function ($e) {
				return htmlspecialchars($e, ENT_QUOTES, 'utf-8');
			}, $result['error']);
			$mes->addWarning(count($result['error']) . ' item(s) failed:<br>' . implode('<br>', $failed));
		}

		e107::getCache()->clearAll('system');
		$mes->addInfo('Tip: if some new folders were skipped on the first pass, run the core sync a second time.');
	}

	/**
	 * Display only. Renders the plugin list from the SYSTEM cache as
	 * checkboxes, pre-checking the six base plugins and anything already on
	 * disk. "Refresh plugin cache" makes ONE GitHub API call to re-read the
	 * repo's eplugins/ folder. No download yet — that is a later phase.
	 */
	protected function renderPluginSelection()
	{
		$frm = e107::getForm();

		$refresh  = $frm->open('gsl_refresh', 'post', e_SELF . '?mode=sync&action=main');
		$refresh .= $frm->token();
		$refresh .= $frm->admin_button('refresh_plugins', 1, 'other', 'Refresh plugin cache');
		$refresh .= $frm->close();

		$cached = githubSyncLite_plugin_list::getCached();

		if ($cached === null)
		{
			$note  = "<div class='alert alert-info'>";
			$note .= "No plugin list cached yet. Click <strong>Refresh plugin cache</strong> to read the "
				. "repo's <strong>eplugins/</strong> folder once and store it. The cached list is then reused "
				. "on every visit until you refresh again.";
			$note .= "</div>";

			return $note . $refresh;
		}

		$base = githubSyncLite_plugin_list::basePlugins();

		$rows = '';
		foreach ($cached as $folder)
		{
			$isBase  = in_array($folder, $base, true);
			$onDisk  = githubSyncLite_plugin_list::existsOnDisk($folder);
			$checked = ($isBase || $onDisk);

			$labelBits = array();
			if ($isBase)
			{
				$labelBits[] = "<span class='label label-primary'>base</span>";
			}
			$labelBits[] = $onDisk
				? "<span class='label label-success'>installed</span>"
				: "<span class='label label-default'>not installed</span>";

			$safeFolder = htmlspecialchars($folder, ENT_QUOTES, 'utf-8');

			$rows .= "<tr>";
			$rows .= "<td style='width:5%' class='center'>"
				. $frm->checkbox('gsl_plugins[]', $safeFolder, $checked)
				. "</td>";
			$rows .= "<td>{$safeFolder}</td>";
			$rows .= "<td>" . implode(' ', $labelBits) . "</td>";
			$rows .= "</tr>";
		}

		$table  = "<p class='e-help'>Checked = the six base plugins, plus anything already present in "
			. "<strong>eplugins/</strong> on disk. This list is read from the cache; it does not download "
			. "anything yet (selection is wired up in the next phase).</p>";
		$table .= "<table class='table table-striped'>";
		$table .= "<thead><tr><th style='width:5%'></th><th>Plugin folder</th><th>Status</th></tr></thead>";
		$table .= "<tbody>{$rows}</tbody>";
		$table .= "</table>";

		return $table . $refresh;
	}

	public function renderHelp()
	{
		$text  = '<strong>Core Sync</strong> downloads the Lite core from the configured repo, '
			. 'overwriting core files but never touching <strong>eplugins/</strong>.';
		$text .= '<br><br><strong>Plugins</strong> lists the repo\'s plugin folders from a cached copy. '
			. 'Use <em>Refresh plugin cache</em> to re-read the list from GitHub (one API call); it is then '
			. 'reused until you refresh again. Selecting and installing plugins is a later phase.';
		$text .= '<br><br>Set the repo on the <strong>Source</strong> screen. Main admin only. Tested on Lite / PHP 7.4.';

		return array(
			'caption' => LAN_HELP,
			'text'    => $text,
		);
	}
}


class githubSyncLite_form_ui extends e_admin_form_ui
{
}


new githubSyncLite_adminArea();

require_once(e_ADMIN . 'auth.php');
e107::getAdminUI()->runPage();

require_once(e_ADMIN . 'footer.php');
exit;
