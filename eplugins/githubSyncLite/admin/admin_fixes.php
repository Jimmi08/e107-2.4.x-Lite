<?php

/**
 * githubSyncLite — admin/admin_fixes.php  (mode: fixes)
 *
 * One-click repair for settings that an upstream core sync (or an e107
 * update run) resets, and that cannot be restored through the normal admin
 * screens because of a core bug.
 *
 * Currently one fix: the news URL override profile.
 *
 * WHY THIS PAGE EXISTS
 * --------------------
 * e107 supports overriding a core URL profile by placing a config file in
 * e107_core/override/url/<module>/ — the router documents this as the only
 * sanctioned way to overload a core module. The file is picked up correctly
 * at runtime, but it never appears in Admin → Settings → URLs for the
 * "news" module, so it cannot be selected:
 *
 *   eRouter::adminReadModules() scans the override directory and finds
 *   "news", then drops it again in the clean-up pass
 *
 *       if(in_array($l, $plugins) && !in_array($l, $ret['plugin'])) unset(...)
 *
 *   whose intent is "an override of a plugin that is not installed". The
 *   news module collides with the news PLUGIN directory, and news never
 *   reaches $ret['plugin'] because the plugin loop skips anything that is
 *   already a core module ("DON'T ALLOW PLUGINS TO OVERRIDE CORE"). The
 *   override is therefore discarded for every core module whose name is
 *   also a plugin folder.
 *
 * The runtime path is unaffected: eRouter::buildGlobalConfig() reads the
 * pref url_config['news'] directly, and eRouter::adminBuildConfig() keeps
 * any readable value containing a slash. So writing the pref here works —
 * only the <select> on the URL configuration screen cannot offer it.
 *
 * CAUTION: pressing "Update" on Admin → Settings → URLs submits whatever
 * the (incomplete) dropdown shows and silently overwrites the pref again.
 * Re-run this fix afterwards.
 *
 * The applied value is a constant in the code — nothing is taken from the
 * request — so the action has no parameters to tamper with. It is
 * idempotent and gated on the main admin plus the admin form token.
 */

require_once('../../../class2.php');

if (!getperms('P'))
{
	e107::redirect('admin');
	exit;
}

e107_require_once('admin_menu.php'); // shared dispatcher


class githubSyncLite_fixes_ui extends e_admin_ui
{
	protected $pluginTitle = 'Github Sync Lite';
	protected $pluginName  = 'githubSyncLite';
	protected $table       = ''; // prefs only — no table
	protected $pid         = '';

	protected $defaultAction = 'main';

	/** Module the override profile belongs to. */
	const FIX_MODULE = 'news';

	/** Pref value that selects the override profile (location/sub-profile). */
	const FIX_LOCATION = 'override/sef';

	/**
	 * Path of the override profile file this fix activates. The file name
	 * must match one that also exists in the core url directory, otherwise
	 * the core would never resolve it.
	 *
	 * @return string
	 */
	protected function profilePath()
	{
		return e_CORE . 'override/url/' . self::FIX_MODULE . '/sef_url.php';
	}

	public function mainPage()
	{
		$this->addTitle('Site fixes');

		$mes = e107::getMessage();
		$frm = e107::getForm();
		$req = $this->getRequest();

		if (!getperms('0'))
		{
			$mes->addError('Only the main admin can apply site fixes.');
			return $mes->render();
		}

		// --- POST: apply the fix --------------------------------------------
		if ($req->getPosted('apply_news_url'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			elseif (!is_readable($this->profilePath()))
			{
				$mes->addError('The override profile file is missing — nothing was changed.');
			}
			else
			{
				$this->applyNewsUrlFix($mes);
			}
		}

		$out  = $mes->render();
		$out .= e107::getRender()->tablerender(
			'News URL override profile',
			$this->renderNewsUrlFix($frm),
			'gsl-fix-newsurl',
			true
		);

		return $out;
	}

	// =====================================================================
	// News URL override fix
	// =====================================================================

	/**
	 * Write the pref exactly the way the URL configuration screen would,
	 * then drop the caches that hold the compiled rule set and any page
	 * rendered with the previous URL scheme.
	 *
	 * e_url_list mirrors what e107_admin/eurl.php stores for any non-'core'
	 * selection; leaving it out would diverge from a normal admin save.
	 *
	 * @param object $mes message handler
	 * @return void
	 */
	protected function applyNewsUrlFix($mes)
	{
		$config = e107::getConfig();

		$urlConfig = $config->get('url_config');
		if (!is_array($urlConfig))
		{
			$urlConfig = array();
		}

		$urlConfig[self::FIX_MODULE] = self::FIX_LOCATION;

		$config->set('url_config', $urlConfig)->save(false, true, false);
		$config->setPref('e_url_list/' . self::FIX_MODULE, self::FIX_MODULE)->save(false, true, false);

		eRouter::clearCache();
		e107::getCache()->clearAll('content'); // pages may embed the old URL scheme

		// $message = false — the confirmation below is added to the stack by
		// hand; letting the logger do it as well would show it twice.
		e107::getLog()->addSuccess('githubSyncLite: news URL override profile re-applied ('
			. self::FIX_LOCATION . ').', false)->save('GSL_01');

		$mes->addSuccess('Applied. url_config[' . self::FIX_MODULE . '] = '
			. self::FIX_LOCATION . ', URL cache and content cache cleared.');
		$mes->addInfo('Check a few live URLs now. Do not press Update on '
			. 'Admin → Settings → URLs afterwards — it would overwrite this again.');
	}

	/**
	 * Current state + the button. Everything shown here is read live, so the
	 * page doubles as a check of whether the fix is currently in effect.
	 *
	 * @param object $frm form handler
	 * @return string
	 */
	protected function renderNewsUrlFix($frm)
	{
		$path      = $this->profilePath();
		$hasFile   = is_readable($path);
		$urlConfig = e107::getPref('url_config', array());
		$current   = is_array($urlConfig) && isset($urlConfig[self::FIX_MODULE])
			? (string) $urlConfig[self::FIX_MODULE]
			: '(not set)';
		$applied   = ($current === self::FIX_LOCATION);

		$eUrlList  = e107::getPref('e_url_list', array());
		$eUrlNews  = is_array($eUrlList) && !empty($eUrlList[self::FIX_MODULE])
			? (string) $eUrlList[self::FIX_MODULE]
			: '(off)';

		$cacheFile = defined('e_CACHE_URL') ? e_CACHE_URL . 'config.php' : '';
		$cacheInfo = ($cacheFile !== '' && file_exists($cacheFile))
			? 'present — rebuilt on the next request after a change'
			: 'not present — will be rebuilt on the next request';

		$rows   = array();
		$rows[] = array('Override profile file', $hasFile
			? '<span class="label label-success">found</span> ' . htmlspecialchars($path, ENT_QUOTES, 'utf-8')
			: '<span class="label label-danger">missing</span> ' . htmlspecialchars($path, ENT_QUOTES, 'utf-8')
				. ' — deploy the file first; this fix cannot select a profile that does not exist');
		$rows[] = array('url_config[' . self::FIX_MODULE . ']', $applied
			? '<span class="label label-success">' . htmlspecialchars($current, ENT_QUOTES, 'utf-8') . '</span> — override active'
			: '<span class="label label-warning">' . htmlspecialchars($current, ENT_QUOTES, 'utf-8') . '</span> — override NOT active');
		$rows[] = array('e_url_list[' . self::FIX_MODULE . ']', htmlspecialchars($eUrlNews, ENT_QUOTES, 'utf-8'));
		$rows[] = array('URL cache', $cacheInfo);

		$out  = '<p>Re-selects the news URL override profile. Needed after a core sync, '
			. 'an e107 update, or any save of the URL configuration screen — that screen '
			. 'cannot offer the override profile because of a core discovery bug, so it '
			. 'resets the selection to one of the built-in profiles.</p>';
		$out .= $this->renderRows($rows);

		$out .= $frm->open('gsl_fix_newsurl', 'post', e_SELF . '?mode=fixes&action=main');
		$out .= $frm->token();

		if ($hasFile)
		{
			$out .= $frm->admin_button('apply_news_url', 1, 'other',
				$applied ? 'Re-apply news URL override' : 'Apply news URL override');
		}
		else
		{
			$out .= '<div class="alert alert-warning">Button disabled — the profile file is missing.</div>';
		}

		$out .= $frm->close();

		return $out;
	}

	// =====================================================================
	// helpers
	// =====================================================================

	/**
	 * @param array $rows list of [label, html] pairs (label escaped here,
	 *                    the value side is pre-escaped by the callers)
	 * @return string
	 */
	protected function renderRows(array $rows)
	{
		$html = "<table class='table table-striped'><tbody>";
		foreach ($rows as $row)
		{
			$html .= '<tr><td style="width:28%"><strong>'
				. htmlspecialchars($row[0], ENT_QUOTES, 'utf-8')
				. '</strong></td><td>' . $row[1] . '</td></tr>';
		}
		$html .= '</tbody></table>';

		return $html;
	}

	public function renderHelp()
	{
		$text  = '<strong>Site fixes</strong> restores settings that a core sync or an e107 update resets.';
		$text .= '<br><br><strong>News URL override profile</strong> writes url_config[news] = '
			. self::FIX_LOCATION . ' and clears the URL and content caches — the same thing '
			. 'the URL configuration screen would do, if it could list the override profile.';
		$text .= '<br>It cannot: eRouter::adminReadModules() discards an override whose module name '
			. 'is also a plugin folder, which is always the case for news. The runtime honours the '
			. 'pref regardless, which is why writing it directly works.';
		$text .= '<br><br>Safe to run repeatedly. Main admin only.';

		return array(
			'caption' => LAN_HELP,
			'text'    => $text,
		);
	}
}


class githubSyncLite_fixes_form_ui extends e_admin_form_ui
{
}


new githubSyncLite_adminArea();

require_once(e_ADMIN . 'auth.php');
e107::getAdminUI()->runPage();

require_once(e_ADMIN . 'footer.php');
exit;
