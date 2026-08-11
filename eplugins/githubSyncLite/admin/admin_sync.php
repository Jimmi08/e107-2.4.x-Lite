<?php

/**
 * githubSyncLite — admin/admin_sync.php  (mode: sync, default)
 *
 * The everyday screen:
 *   (1) Core sync — downloads the Lite core from the configured repo,
 *       EXCLUDING the plugins folder (handled by the LITE-modified engine
 *       copy). The source repo's layout (eplugins vs e107_plugins, 'e' vs
 *       'e107_' folder prefix) comes from the Source screen preferences.
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
e107_require_once(e_PLUGIN . 'githubSyncLite/includes/github_sync_engine.php'); // bundled engine (core sync skips the plugins folder)
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

		// Layout prefs are whitelisted on read as well as on save — they end
		// up in an API URL segment and in archive path prefixes, so anything
		// outside the two known layouts falls back to the Lite defaults.
		$pluginsFolder = githubSyncLite_plugin_list::normalizePluginsFolder($cfg->get('plugins_folder', 'eplugins'));

		$folderPrefix = (string) $cfg->get('folder_prefix', 'e');
		if (!in_array($folderPrefix, array('e', 'e107_'), true))
		{
			$folderPrefix = 'e';
		}

		return array(
			'organization'   => $cfg->get('organization', ''),
			'repo'           => $cfg->get('repo', ''),
			'branch'         => $cfg->get('branch', ''),
			'token'          => $cfg->get('token', ''),
			'public_repo'    => (int) $cfg->get('public_repo', 1),
			'plugins_folder' => $pluginsFolder,
			'folder_prefix'  => $folderPrefix,
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
					$mes->addSuccess(count($list) . ' plugin folder(s) found in the repo and stored (kept in plugin settings — survives cache clears).');
				}
			}
		}

		$c = $this->sourceConfig();

		// --- Core sync form -------------------------------------------------
		$repoUrl = $c['public_repo']
			? "https://github.com/{$c['organization']}/{$c['repo']}/tree/{$c['branch']}"
			: '';

		$safeRepo   = htmlspecialchars($c['organization'] . '/' . $c['repo'], ENT_QUOTES, 'utf-8');
		$safeBranch = htmlspecialchars($c['branch'], ENT_QUOTES, 'utf-8');

		$sourceCell = $safeRepo;
		if ($repoUrl !== '')
		{
			$safeUrl    = htmlspecialchars($repoUrl, ENT_QUOTES, 'utf-8');
			$sourceCell = "<a href='{$safeUrl}' target='_blank' rel='noopener'>{$safeRepo}</a>";
		}

		$body  = '<p>Downloads <strong>core</strong> files from the source repo and extracts them over this '
			. 'installation, <strong>overwriting existing core files</strong>.</p>';
		$body .= "<table class='table table-striped'><tbody>";
		$body .= "<tr><td style='width:28%'><strong>Source repo</strong></td><td>" . $sourceCell
			. " &middot; branch <strong>" . $safeBranch . "</strong> (change on the <strong>Source</strong> screen)</td></tr>";
		$body .= "<tr><td><strong>Repo layout</strong></td><td>core folders <strong>" . $c['folder_prefix']
			. "*</strong>, plugins in <strong>" . $c['plugins_folder'] . "/</strong></td></tr>";
		$body .= "<tr><td><strong>Download to</strong></td><td>" . e_SYSTEM . "temp (extracted from there)</td></tr>";
		$body .= "<tr><td><strong>Never touched</strong></td><td>the local <strong>"
			. e107::getFolder('PLUGINS') . "</strong> directory — plugins are pulled selectively, not with the core</td></tr>";
		$body .= '</tbody></table>';

		// The run button lives in the Plugins section (right above the plugin
		// table) so it is clear the run relates to the whole selection below,
		// not just the core files described here — see renderRunToolbar().

		// --- Plugin selection (display only) --------------------------------
		$plugins = $this->renderPluginSelection();

		$out  = $mes->render();
		$out .= e107::getRender()->tablerender('Core sync (excludes ' . $c['plugins_folder'] . '/)', $body, 'gsl-core', true);
		$out .= e107::getRender()->tablerender('Plugins', $plugins, 'gsl-plugins', true);

		return $out;
	}

	/**
	 * Delegates the work to the bundled engine (type 'core', which the
	 * LITE-modified engine copy extracts WITHOUT the plugins folder).
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
			'organization'   => $c['organization'],
			'repo'           => $c['repo'],
			'branch'         => $c['branch'],
			'folder'         => '',      // unused for 'core'
			'type'           => 'core',  // engine skips the plugins folder
			'token'          => $c['token'],
			'public_repo'    => $c['public_repo'],
			'plugins_folder' => $c['plugins_folder'],
			'folder_prefix'  => $c['folder_prefix'],
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
			$mes->addInfo(count($result['skipped']) . ' item(s) skipped (includes the plugins folder, which core sync never writes).');
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
	 * The "Run core sync" form, placed right above the plugin table. The
	 * check/uncheck-all buttons live INSIDE the same form so everything sits
	 * on one row — they are type="button", so they can never submit it.
	 * They touch only .gsl-plugin-select checkboxes; base plugins keep their
	 * state and are handled manually.
	 *
	 * @param bool $withCheckButtons  FALSE when there is no plugin table yet.
	 * @return string
	 */
	protected function renderRunToolbar($withCheckButtons = true)
	{
		$frm = e107::getForm();

		$toolbar  = "<script>function gslSetAll(state){var b=document.querySelectorAll('.gsl-plugin-select');"
			. "for(var i=0;i<b.length;i++){b[i].checked=state;}}</script>";
		$toolbar .= $frm->open('gsl_core', 'post', e_SELF . '?mode=sync&action=main');
		$toolbar .= $frm->token();
		$toolbar .= $frm->admin_button('run_core_sync', 1, 'delete', 'Run core sync');
		if ($withCheckButtons)
		{
			$toolbar .= " <button type='button' class='btn btn-default' onclick='gslSetAll(true)'>Check all</button>";
			$toolbar .= " <button type='button' class='btn btn-default' onclick='gslSetAll(false)'>Uncheck all</button>";
		}
		$toolbar .= $frm->close();

		return "<div style='margin-bottom:10px'>" . $toolbar . "</div>";
	}

	/**
	 * Display only. Renders the plugin list from the stored preference as
	 * checkboxes, pre-checking the six base plugins and anything actually
	 * installed. "Refresh plugin list" makes ONE GitHub API call to re-read
	 * the repo's plugins folder (per the 'plugins_folder' preference). No
	 * download yet — that is a later phase.
	 */
	protected function renderPluginSelection()
	{
		$frm = e107::getForm();
		$c   = $this->sourceConfig();

		$refresh  = $frm->open('gsl_refresh', 'post', e_SELF . '?mode=sync&action=main');
		$refresh .= $frm->token();
		$refresh .= $frm->admin_button('refresh_plugins', 1, 'other', 'Refresh plugin list');
		$refresh .= $frm->close();

		$cached = githubSyncLite_plugin_list::getCached($c['plugins_folder']);

		if ($cached === null)
		{
			$note  = "<div class='alert alert-info'>";
			$note .= "No plugin list stored yet (or the stored list was made for a different "
				. "plugins-folder setting). Click <strong>Refresh plugin list</strong> to read the "
				. "repo's <strong>" . $c['plugins_folder'] . "/</strong> folder once and store it in the "
				. "plugin settings. The stored list survives cache clears and is reused on every visit "
				. "until you refresh again.";
			$note .= "</div>";

			return $note . $this->renderRunToolbar(false) . $refresh;
		}

		$base = githubSyncLite_plugin_list::basePlugins();

		$rows = '';
		foreach ($cached as $folder)
		{
			$isBase    = in_array($folder, $base, true);
			$onDisk    = githubSyncLite_plugin_list::existsOnDisk($folder);
			$installed = githubSyncLite_plugin_list::isInstalled($folder);
			$checked   = ($isBase || $installed);

			// Base plugins get their own class so the check/uncheck-all
			// buttons skip them — base is always handled manually.
			$boxClass = $isBase ? 'gsl-plugin-base' : 'gsl-plugin-select';

			$labelBits = array();
			if ($isBase)
			{
				$labelBits[] = "<span class='label label-primary'>base</span>";
			}
			// Three real states: installed (registered in the plugin table),
			// merely on disk (a standard e107 ships every core plugin folder,
			// so on-disk alone means nothing), or not present at all.
			if ($installed)
			{
				$labelBits[] = "<span class='label label-success'>installed</span>";
			}
			elseif ($onDisk)
			{
				$labelBits[] = "<span class='label label-info'>on disk</span>";
			}
			else
			{
				$labelBits[] = "<span class='label label-default'>not present</span>";
			}

			$safeFolder = htmlspecialchars($folder, ENT_QUOTES, 'utf-8');

			$rows .= "<tr>";
			$rows .= "<td style='width:5%' class='center'>"
				. $frm->checkbox('gsl_plugins[]', $safeFolder, $checked, array('class' => $boxClass))
				. "</td>";
			$rows .= "<td>{$safeFolder}</td>";
			$rows .= "<td>" . implode(' ', $labelBits) . "</td>";
			$rows .= "</tr>";
		}

		$legend  = "<table class='table table-striped'><tbody>";
		$legend .= "<tr><td style='width:28%'><strong>Checked</strong></td>"
			. "<td>the six base plugins, plus anything actually installed here</td></tr>";
		$legend .= "<tr><td><span class='label label-primary'>base</span></td>"
			. "<td>always synced; the check/uncheck-all buttons skip these — change them manually</td></tr>";
		$legend .= "<tr><td><span class='label label-success'>installed</span></td>"
			. "<td>registered on this site</td></tr>";
		$legend .= "<tr><td><span class='label label-info'>on disk</span></td>"
			. "<td>folder exists in " . e107::getFolder('PLUGINS') . " but the plugin is not installed</td></tr>";
		$legend .= "<tr><td><span class='label label-default'>not present</span></td>"
			. "<td>no local folder</td></tr>";
		$legend .= "<tr><td><strong>List source</strong></td>"
			. "<td>the repo's <strong>" . $c['plugins_folder'] . "/</strong> folder, stored in the plugin "
			. "settings &middot; the list itself does not download anything yet (selection is wired up in "
			. "the next phase)</td></tr>";
		$legend .= '</tbody></table>';

		$toolbar = $this->renderRunToolbar(true);

		$table  = "<table class='table table-striped'>";
		$table .= "<thead><tr><th style='width:5%'></th><th>Plugin folder</th><th>Status</th></tr></thead>";
		$table .= "<tbody>{$rows}</tbody>";
		$table .= "</table>";

		return $legend . $toolbar . $table . $refresh;
	}

	public function renderHelp()
	{
		$text  = '<strong>Core Sync</strong> downloads the Lite core from the configured repo, '
			. 'overwriting core files but never touching the local plugins directory. The source repo\'s '
			. 'layout (plugins folder name and core-folder prefix) is set on the <strong>Source</strong> screen.';
		$text .= '<br><br><strong>Plugins</strong> lists the repo\'s plugin folders from a stored copy '
			. '(kept in the plugin settings, so it survives cache clears). Use <em>Refresh plugin list</em> '
			. 'to re-read it from GitHub (one API call); it is then reused until you refresh again. '
			. 'Selecting and installing plugins is a later phase.';
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
