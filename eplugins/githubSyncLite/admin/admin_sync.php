<?php

/**
 * githubSyncLite — admin/admin_sync.php  (mode: sync, default)
 *
 * The everyday screen:
 *   (1) Core sync — downloads the Lite core from the configured repo and
 *       extracts it over this installation. From the SAME archive it also
 *       extracts the SELECTED plugin folders (see below); every other entry
 *       under the repo's plugins folder is skipped by the LITE-modified
 *       engine copy. The source repo's layout (eplugins vs e107_plugins,
 *       'e' vs 'e107_' folder prefix) comes from the Source screen prefs.
 *   (2) Plugins — the repo's plugin folders (stored in the plugin prefs,
 *       one GitHub API call on "Refresh plugin list") as checkboxes. The
 *       selection is saved in the plugin prefs and used by the core sync.
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
e107_require_once(e_PLUGIN . 'githubSyncLite/includes/github_sync_engine.php'); // bundled engine (core sync + selected plugin folders)
e107_require_once(e_PLUGIN . 'githubSyncLite/includes/plugin_list.php');         // plugin-folder list + selection (plugin prefs)


class githubSyncLite_ui extends e_admin_ui
{
	protected $pluginTitle = 'Github Sync Lite';
	protected $pluginName  = 'githubSyncLite';
	protected $table       = ''; // prefs only — no table
	protected $pid         = '';

	protected $defaultAction = 'main';

	/**
	 * Current source repo, read from plugin prefs (set on the Source screen).
	 *
	 * @return array
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
	 *
	 * @return bool
	 */
	protected function sourceIsSet()
	{
		$c = $this->sourceConfig();
		return ($c['organization'] !== '' && $c['repo'] !== '' && $c['branch'] !== '');
	}

	/**
	 * Core Sync screen: handles the three POST actions (save selection, run
	 * core sync, refresh plugin list) and renders the page.
	 *
	 * @return string
	 */
	public function mainPage()
	{
		$this->addTitle('Core Sync');

		$mes = e107::getMessage();
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

		// --- POST: save plugin selection -------------------------------------
		// The posted names are only ever matched against the STORED list
		// (the whitelist) inside saveSelection(); nothing from $_POST becomes
		// a path segment.
		if ($req->getPosted('save_selection'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			else
			{
				$this->saveSelection();
			}
		}

		// --- POST: run core sync --------------------------------------------
		// The run button sits in the same form as the checkboxes, so the
		// selection shown on screen is saved first — what you see is what
		// gets synced. The engine then receives the STORED selection.
		if ($req->getPosted('run_core_sync'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			else
			{
				$this->saveSelection();
				$this->runCoreSync();
			}
		}

		// --- POST: refresh plugin list (one deliberate GitHub API call) ------
		// No clearCache() beforehand: refresh() merges the stored selection
		// with the new list, so the old data must still be there.
		if ($req->getPosted('refresh_plugins'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			else
			{
				$list = githubSyncLite_plugin_list::refresh($this->sourceConfig());
				if ($list !== false)
				{
					$mes->addSuccess(count($list) . ' plugin folder(s) found in the repo and stored (kept in plugin settings — survives cache clears). Your selection was kept.');
				}
			}
		}

		$c = $this->sourceConfig();

		// --- Core sync description ------------------------------------------
		$repoUrl = $c['public_repo']
			? "https://github.com/{$c['organization']}/{$c['repo']}/tree/{$c['branch']}"
			: '';

		$safeRepo    = htmlspecialchars($c['organization'] . '/' . $c['repo'], ENT_QUOTES, 'utf-8');
		$safeBranch  = htmlspecialchars($c['branch'], ENT_QUOTES, 'utf-8');
		$safePlugDir = htmlspecialchars($c['plugins_folder'], ENT_QUOTES, 'utf-8');
		$safePrefix  = htmlspecialchars($c['folder_prefix'], ENT_QUOTES, 'utf-8');
		$safeLocal   = htmlspecialchars(e107::getFolder('PLUGINS'), ENT_QUOTES, 'utf-8');

		$sourceCell = $safeRepo;
		if ($repoUrl !== '')
		{
			$safeUrl    = htmlspecialchars($repoUrl, ENT_QUOTES, 'utf-8');
			$sourceCell = "<a href='{$safeUrl}' target='_blank' rel='noopener'>{$safeRepo}</a>";
		}

		$selectedCount = count(githubSyncLite_plugin_list::getSelected($c['plugins_folder']));

		$body  = '<p>Downloads <strong>core</strong> files from the source repo and extracts them over this '
			. 'installation, <strong>overwriting existing core files</strong>. The <strong>selected plugin '
			. 'folders</strong> below are extracted from the same archive — no separate download per plugin.</p>';
		$body .= "<table class='table table-striped'><tbody>";
		$body .= "<tr><td style='width:28%'><strong>Source repo</strong></td><td>" . $sourceCell
			. " &middot; branch <strong>" . $safeBranch . "</strong> (change on the <strong>Source</strong> screen)</td></tr>";
		$body .= "<tr><td><strong>Repo layout</strong></td><td>core folders <strong>" . $safePrefix
			. "*</strong>, plugins in <strong>" . $safePlugDir . "/</strong></td></tr>";
		$body .= "<tr><td><strong>Download to</strong></td><td>" . htmlspecialchars(e_SYSTEM, ENT_QUOTES, 'utf-8') . "temp (extracted from there)</td></tr>";
		$body .= "<tr><td><strong>Plugins written</strong></td><td>only the <strong>" . $selectedCount
			. "</strong> selected folder(s) from the repo's <strong>" . $safePlugDir . "/</strong>, into the local <strong>"
			. $safeLocal . "</strong> directory &middot; everything else under " . $safePlugDir . "/ is skipped</td></tr>";
		$body .= '</tbody></table>';

		// The run button lives in the Plugins section (right above the plugin
		// table, inside the same form as the checkboxes) so it is clear the
		// run relates to the whole selection below, not just the core files
		// described here — see renderPluginSelection().

		// --- Plugin selection -------------------------------------------------
		$plugins = $this->renderPluginSelection();

		$out  = $mes->render();
		$out .= e107::getRender()->tablerender('Core sync (+ selected plugins from ' . $safePlugDir . '/)', $body, 'gsl-core', true);
		$out .= e107::getRender()->tablerender('Plugins', $plugins, 'gsl-plugins', true);

		return $out;
	}

	/**
	 * Save the posted gsl_plugins[] checkboxes as the stored selection and
	 * report how many folders are selected. Only names present in the
	 * stored list survive (whitelist); the base plugins are always added.
	 * Does nothing (and says so) when no list is stored yet.
	 *
	 * @return void
	 */
	protected function saveSelection()
	{
		$mes = e107::getMessage();
		$c   = $this->sourceConfig();
		$req = $this->getRequest();

		if (githubSyncLite_plugin_list::getCached($c['plugins_folder']) === null)
		{
			$mes->addInfo('No plugin list stored yet — nothing to select. Click <strong>Refresh plugin list</strong> first.');
			return;
		}

		$posted = $req->getPosted('gsl_plugins', array());
		if (!is_array($posted))
		{
			$posted = array();
		}

		$selected = githubSyncLite_plugin_list::saveSelection($posted, $c['plugins_folder']);

		$mes->addSuccess(count($selected) . ' plugin folder(s) selected (including the base plugins) — saved in the plugin settings.');
	}

	/**
	 * Delegates the work to the bundled engine (type 'core'). The LITE-
	 * modified engine copy extracts the core folders plus ONLY the plugin
	 * folders passed in 'plugins' (the stored selection); everything else
	 * under the repo's plugins folder is skipped.
	 *
	 * @return void
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

		// The stored selection is already a subset of the stored list; the
		// engine re-validates every name before it becomes a path segment.
		$plugins = githubSyncLite_plugin_list::getSelected($c['plugins_folder']);

		$engine = new github_sync_engine();
		$result = $engine->sync(array(
			'organization'   => $c['organization'],
			'repo'           => $c['repo'],
			'branch'         => $c['branch'],
			'folder'         => '',      // unused for 'core'
			'type'           => 'core',  // core folders + selected plugin folders only
			'token'          => $c['token'],
			'public_repo'    => $c['public_repo'],
			'plugins_folder' => $c['plugins_folder'],
			'folder_prefix'  => $c['folder_prefix'],
			'plugins'        => $plugins,
		));

		if ($result === false)
		{
			return; // engine already reported the reason
		}

		$safePlugDir = htmlspecialchars($c['plugins_folder'], ENT_QUOTES, 'utf-8');

		if (!empty($result['success']))
		{
			$mes->addSuccess(count($result['success']) . ' file(s)/folder(s) synced.');
		}

		if (empty($plugins))
		{
			$mes->addInfo('No plugin folders were included in this run (nothing selected) — nothing was written under ' . $safePlugDir . '/.');
		}
		else
		{
			$safeNames = array_map(static function ($name) {
				return htmlspecialchars($name, ENT_QUOTES, 'utf-8');
			}, $plugins);
			$mes->addInfo(count($plugins) . ' plugin folder(s) included in this run from ' . $safePlugDir . '/: <strong>'
				. implode('</strong>, <strong>', $safeNames) . '</strong>');
		}

		if (!empty($result['skipped']))
		{
			// Count how many of the skipped archive entries sit under the
			// repo's plugins folder ({zipBase}/{plugins_folder}/...), so the
			// admin can see the unselected plugin folders were left alone.
			$plugPattern  = '#^[^/]+/' . preg_quote($c['plugins_folder'], '#') . '/#';
			$skippedPlugs = count(preg_grep($plugPattern, $result['skipped']));

			$mes->addInfo(count($result['skipped']) . ' item(s) skipped — ' . $skippedPlugs . ' of them under the repo\'s '
				. $safePlugDir . '/ directory (plugin folders not in your selection), the rest repo housekeeping files.');
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
	 * The buttons placed right above the plugin table: "Run core sync",
	 * "Save selection" and the check/uncheck-all helpers. Buttons only — the
	 * caller (renderPluginSelection()) wraps them in the form that also
	 * contains the checkbox table, so the checkboxes are posted with them.
	 * The check/uncheck-all buttons are type="button", so they can never
	 * submit. They touch only .gsl-plugin-select checkboxes; base plugins
	 * (.gsl-plugin-base) keep their state and are handled manually.
	 *
	 * @param bool $withCheckButtons  FALSE when there is no plugin table yet.
	 * @return string
	 */
	protected function renderRunToolbar($withCheckButtons = true)
	{
		$frm = e107::getForm();

		$toolbar  = "<script>function gslSetAll(state){var b=document.querySelectorAll('.gsl-plugin-select');"
			. "for(var i=0;i<b.length;i++){b[i].checked=state;}}</script>";
		$toolbar .= $frm->admin_button('run_core_sync', 1, 'delete', 'Run core sync');
		if ($withCheckButtons)
		{
			$toolbar .= ' ' . $frm->admin_button('save_selection', 1, 'update', 'Save selection');
			$toolbar .= " <button type='button' class='btn btn-default' onclick='gslSetAll(true)'>Check all</button>";
			$toolbar .= " <button type='button' class='btn btn-default' onclick='gslSetAll(false)'>Uncheck all</button>";
		}

		return "<div style='margin-bottom:10px'>" . $toolbar . "</div>";
	}

	/**
	 * Renders the plugin list from the stored preference as checkboxes,
	 * checked from the STORED SELECTION. The installed / on-disk / not-present
	 * labels are informational only. The toolbar and the checkbox table sit
	 * inside ONE form ($frm->open() → toolbar → table → $frm->close()), so
	 * "Save selection" and "Run core sync" post the checkboxes; "Refresh
	 * plugin list" is a separate form below (one GitHub API call).
	 *
	 * @return string
	 */
	protected function renderPluginSelection()
	{
		$frm = e107::getForm();
		$c   = $this->sourceConfig();

		$safePlugDir = htmlspecialchars($c['plugins_folder'], ENT_QUOTES, 'utf-8');
		$safeLocal   = htmlspecialchars(e107::getFolder('PLUGINS'), ENT_QUOTES, 'utf-8');

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
				. "repo's <strong>" . $safePlugDir . "/</strong> folder once and store it in the "
				. "plugin settings. The stored list survives cache clears and is reused on every visit "
				. "until you refresh again. Until then a core sync writes nothing under " . $safePlugDir . "/.";
			$note .= "</div>";

			$form  = $frm->open('gsl_core', 'post', e_SELF . '?mode=sync&action=main');
			$form .= $frm->token();
			$form .= $this->renderRunToolbar(false);
			$form .= $frm->close();

			return $note . $form . $refresh;
		}

		$base     = githubSyncLite_plugin_list::basePlugins();
		$selected = $cached['selected'];

		$rows = '';
		foreach ($cached['list'] as $folder)
		{
			$isBase    = in_array($folder, $base, true);
			$onDisk    = githubSyncLite_plugin_list::existsOnDisk($folder);
			$installed = githubSyncLite_plugin_list::isInstalled($folder);
			$checked   = in_array($folder, $selected, true);

			// Base plugins get their own class so the check/uncheck-all
			// buttons skip them — base is always handled manually. They stay
			// enabled (no 'disabled') so they post normally.
			$boxClass = $isBase ? 'gsl-plugin-base' : 'gsl-plugin-select';

			$labelBits = array();
			if ($isBase)
			{
				$labelBits[] = "<span class='label label-primary'>base</span>";
			}
			// Three real states: installed (registered in the plugin table),
			// merely on disk (a standard e107 ships every core plugin folder,
			// so on-disk alone means nothing), or not present at all.
			// Informational only — the checkbox state comes from the stored
			// selection.
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
			. "<td>your saved selection — the plugin folders a core sync extracts from the repo archive "
			. "(" . count($selected) . " selected). Change it and click <strong>Save selection</strong>; "
			. "<strong>Run core sync</strong> saves it too before it runs.</td></tr>";
		$legend .= "<tr><td><span class='label label-primary'>base</span></td>"
			. "<td>always selected; the check/uncheck-all buttons skip these</td></tr>";
		$legend .= "<tr><td><span class='label label-success'>installed</span></td>"
			. "<td>registered on this site (informational only)</td></tr>";
		$legend .= "<tr><td><span class='label label-info'>on disk</span></td>"
			. "<td>folder exists in " . $safeLocal . " but the plugin is not installed (informational only)</td></tr>";
		$legend .= "<tr><td><span class='label label-default'>not present</span></td>"
			. "<td>no local folder (informational only)</td></tr>";
		$legend .= "<tr><td><strong>List source</strong></td>"
			. "<td>the repo's <strong>" . $safePlugDir . "/</strong> folder, stored in the plugin "
			. "settings &middot; <em>Refresh plugin list</em> re-reads it and keeps your selection; "
			. "folders that disappeared from the repo are dropped and reported</td></tr>";
		$legend .= '</tbody></table>';

		$table  = "<table class='table table-striped'>";
		$table .= "<thead><tr><th style='width:5%'></th><th>Plugin folder</th><th>Status</th></tr></thead>";
		$table .= "<tbody>{$rows}</tbody>";
		$table .= "</table>";

		// ONE form: toolbar (run / save / check-all) + checkbox table.
		$form  = $frm->open('gsl_core', 'post', e_SELF . '?mode=sync&action=main');
		$form .= $frm->token();
		$form .= $this->renderRunToolbar(true);
		$form .= $table;
		$form .= $frm->close();

		return $legend . $form . $refresh;
	}

	/**
	 * Help panel text.
	 *
	 * @return array
	 */
	public function renderHelp()
	{
		$text  = '<strong>Core Sync</strong> downloads the Lite core from the configured repo and '
			. 'overwrites core files. From the same archive it also writes the <strong>selected</strong> '
			. 'plugin folders into the local plugins directory; everything else under the repo\'s plugins '
			. 'folder is skipped. With nothing selected, nothing is written there at all. The source repo\'s '
			. 'layout (plugins folder name and core-folder prefix) is set on the <strong>Source</strong> screen.';
		$text .= '<br><br><strong>Plugins</strong> lists the repo\'s plugin folders from a stored copy '
			. '(kept in the plugin settings, so it survives cache clears). Tick the folders you want and click '
			. '<em>Save selection</em>; the six base plugins are always included. Use <em>Refresh plugin list</em> '
			. 'to re-read the list from GitHub (one API call) — your selection is kept, and folders that no '
			. 'longer exist in the repo are dropped and reported.';
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
