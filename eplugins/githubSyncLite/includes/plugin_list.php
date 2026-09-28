<?php

/**
 * Plugin list source: the plugin folders present in a GitHub repo's plugins
 * directory, plus the admin's selection of the ones a core sync should
 * extract. One Contents API call on refresh; every page load reads the
 * stored copy.
 *
 * Stored in the PLUGIN PREFERENCES, not the system cache — the core sync
 * clears the system cache when it finishes, which used to wipe the list.
 * Stored format:
 *
 *   {"folder": "eplugins", "list": ["banner", ...], "selected": ["news", ...]}
 *
 * 'folder' is stored with the list so a list made for one layout is never
 * served for the other. SECURITY: 'list' is the whitelist for the selection
 * — a name can only be selected when it is in the list, whatever the browser
 * posts. Refresh MERGES the new list with the old selection and reports what
 * it had to drop.
 */
class githubSyncLite_plugin_list
{
	/** Plugin-pref key the list is stored under. */
	const PREF_KEY = 'plugin_list';

	/** Legacy system-cache tag (pre-0.3.1 storage) — cleared on refresh. */
	const CACHE_TAG = 'githubSyncLite_plugins';

	/**
	 * SECURITY: the value becomes a GitHub API URL segment, so only the two
	 * known layouts are accepted. Duplicated in the sync engine on purpose —
	 * both files are standalone by design.
	 *
	 * @param mixed $value
	 * @return string  'eplugins' or 'e107_plugins'
	 */
	public static function normalizePluginsFolder($value)
	{
		return in_array($value, array('eplugins', 'e107_plugins'), true) ? $value : 'eplugins';
	}

	/**
	 * The stored data, or null when nothing is stored or it belongs to a
	 * different plugins-folder setting (caller should prompt for a refresh).
	 * Never hits the network. 'selected' is always present and always a subset
	 * of 'list'; data stored without it reads as basePlugins() ∩ list.
	 *
	 * @param string $pluginsFolder  current 'plugins_folder' preference
	 * @return array|null  array('folder', 'list', 'selected'), or null
	 */
	public static function getCached($pluginsFolder = 'eplugins')
	{
		$pluginsFolder = self::normalizePluginsFolder($pluginsFolder);

		$raw = e107::getPlugConfig('githubSyncLite')->get(self::PREF_KEY, '');
		if (!is_string($raw) || $raw === '')
		{
			return null;
		}

		$data = json_decode($raw, true);

		// Stored format: {'folder': <plugins folder>, 'list': [...], 'selected': [...]}.
		// A list made for the other layout counts as stale — prompt for a
		// refresh instead.
		if (!is_array($data) || !isset($data['folder'], $data['list'])
			|| $data['folder'] !== $pluginsFolder || !is_array($data['list']))
		{
			return null;
		}

		$list = self::cleanNames($data['list']);

		// Old (0.3.x) format without a selection: base plugins only.
		$selected = (isset($data['selected']) && is_array($data['selected']))
			? self::cleanNames($data['selected'])
			: self::basePlugins();

		return array(
			'folder'   => $pluginsFolder,
			'list'     => $list,
			'selected' => self::mergeSelection($selected, $list),
		);
	}

	/**
	 * The stored selection, ready for the sync engine's 'plugins' param. Empty
	 * when nothing is stored — a core sync then writes nothing under the
	 * plugins directory.
	 *
	 * @param string $pluginsFolder  current 'plugins_folder' preference
	 * @return array
	 */
	public static function getSelected($pluginsFolder = 'eplugins')
	{
		$cached = self::getCached($pluginsFolder);

		return ($cached === null) ? array() : $cached['selected'];
	}

	/**
	 * Store a selection posted from the Core Sync screen. SECURITY: only names
	 * present in the stored list are kept, no exceptions; the base plugins are
	 * always added. 'list' and 'folder' are untouched.
	 *
	 * @param array  $posted         raw gsl_plugins[] values from the form
	 * @param string $pluginsFolder  current 'plugins_folder' preference
	 * @return array  the selection actually saved
	 */
	public static function saveSelection(array $posted, $pluginsFolder = 'eplugins')
	{
		$cached = self::getCached($pluginsFolder);
		if ($cached === null)
		{
			return array();
		}

		$selected = self::mergeSelection(self::cleanNames($posted), $cached['list']);

		self::store($cached['folder'], $cached['list'], $selected);

		return $selected;
	}

	/**
	 * Fetch the list fresh from GitHub (one Contents API call) and store it.
	 * The selection is merged, not reset:
	 *   selected = (old selected ∩ new list) ∪ (basePlugins() ∩ new list)
	 * Dropped entries are reported. The old data must still be stored when this
	 * runs — do NOT call clearCache() first.
	 *
	 * @param array $p  organization, repo, branch, token, public_repo, plugins_folder
	 * @return array|false
	 */
	public static function refresh(array $p)
	{
		$mes = e107::getMessage();

		$org    = trim((string) ($p['organization'] ?? ''));
		$repo   = trim((string) ($p['repo'] ?? ''));
		$branch = trim((string) ($p['branch'] ?? ''));
		$token  = trim((string) ($p['token'] ?? ''));
		$plugDir = self::normalizePluginsFolder($p['plugins_folder'] ?? 'eplugins');

		if ($org === '' || $repo === '' || $branch === '')
		{
			$mes->addError('Set organization, repo and branch before refreshing the plugin list.');
			return false;
		}

		// Validate the segments the same way the sync engine does, before they
		// go into the API URL — reject anything that isn't a plain GitHub
		// path segment (blocks '/', '#', '?', '..' and other URL-altering input).
		foreach (array('organization' => $org, 'repo' => $repo, 'branch' => $branch) as $label => $seg)
		{
			if (!preg_match('/^[A-Za-z0-9._-]+$/', $seg) || strpos($seg, '..') !== false)
			{
				$mes->addError('Invalid ' . $label . ' — only letters, digits, dot, underscore and hyphen are allowed.');
				return false;
			}
		}

		// $plugDir is whitelisted above ('eplugins'/'e107_plugins' only), so it
		// is safe to place in the URL path.
		$url = 'https://api.github.com/repos/' . rawurlencode($org) . '/' . rawurlencode($repo)
			. '/contents/' . $plugDir . '?ref=' . rawurlencode($branch);

		// SSL verification stays ON in production; relaxed only under e_DEBUG
		// (local development), matching the sync engine's behaviour.
		$verifySsl = !(defined('e_DEBUG') && e_DEBUG);

		$headers = array(
			'Accept: application/vnd.github+json',
			'User-Agent: e107-githubSyncLite',
		);
		if ($token !== '')
		{
			// Authenticated calls get a much higher rate limit.
			$headers[] = 'Authorization: token ' . $token;
		}

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => $verifySsl,
			CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
			CURLOPT_TIMEOUT        => 30,
		));
		$body     = curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlErr  = curl_error($ch);
		curl_close($ch);

		if ($curlErr !== '')
		{
			$mes->addError('Could not reach GitHub to refresh the plugin list.');
			return false;
		}
		if ($httpCode === 404)
		{
			$mes->addError('No ' . $plugDir . '/ folder found in ' . htmlspecialchars($org . '/' . $repo, ENT_QUOTES, 'utf-8') . ' (branch ' . htmlspecialchars($branch, ENT_QUOTES, 'utf-8') . '). Check the \'Repo plugins folder\' setting on the Source screen.');
			return false;
		}
		if ($httpCode === 403)
		{
			$mes->addError('GitHub API rate limit reached (HTTP 403). Add a token for a higher limit, or try again later.');
			return false;
		}
		if ($httpCode !== 200)
		{
			$mes->addError("GitHub API returned HTTP {$httpCode} while listing plugins.");
			return false;
		}

		$data = json_decode($body, true);
		if (!is_array($data))
		{
			$mes->addError('Unexpected response from GitHub while listing plugins.');
			return false;
		}

		// Keep only directory entries; collect their names. Names come from a
		// remote API, so validate them like any other path segment before they
		// are stored and later rendered as checkbox values / checked on disk.
		$folders = array();
		foreach ($data as $entry)
		{
			if (isset($entry['type'], $entry['name']) && $entry['type'] === 'dir'
				&& preg_match('/^[A-Za-z0-9._-]+$/', (string) $entry['name'])
				&& strpos((string) $entry['name'], '..') === false)
			{
				$folders[] = (string) $entry['name'];
			}
		}
		sort($folders, SORT_STRING);

		// Merge the previous selection (if any, and only if it was made for
		// the same plugins-folder layout) with the fresh list. Anything that
		// is no longer in the repo is dropped — and reported, never silently.
		$oldSelected = self::getSelected($plugDir);
		$selected    = self::mergeSelection($oldSelected, $folders);

		$dropped = array_values(array_diff($oldSelected, $folders));
		if (!empty($dropped))
		{
			$safeDropped = array_map(static function ($name) {
				return htmlspecialchars($name, ENT_QUOTES, 'utf-8');
			}, $dropped);
			$mes->addInfo(count($dropped) . ' previously selected plugin folder(s) no longer exist in the repo and were removed from the selection: <strong>'
				. implode('</strong>, <strong>', $safeDropped) . '</strong>');
		}

		// Store in the PLUGIN PREFS (survives every cache clear — the core
		// sync itself clears the system cache when it finishes, which used to
		// wipe the list). The plugins-folder name is stored alongside the list
		// so getCached() can reject a list made for the other layout.
		self::store($plugDir, $folders, $selected);

		// Drop the legacy pre-0.3.1 system-cache copy if one is still around.
		e107::getCache()->clear_sys(self::CACHE_TAG);

		return $folders;
	}

	/**
	 * Clear the stored data — list and selection together; they live in one
	 * preference and are never split. Not used by refresh(), which needs the
	 * old selection to merge.
	 *
	 * @return void
	 */
	public static function clearCache()
	{
		e107::getPlugConfig('githubSyncLite')->remove(self::PREF_KEY)->save(false, true, false);
		e107::getCache()->clear_sys(self::CACHE_TAG);
	}

	/**
	 * Plugins always part of the selection.
	 *
	 * @return array
	 */
	public static function basePlugins()
	{
		return array('navigation', 'news', 'page', 'siteinfo', 'tinymce4', 'user');
	}

	/**
	 * Reduce an arbitrary array (decoded JSON or raw $_POST) to de-duplicated
	 * plain folder names: letters, digits, dot, underscore, hyphen; no '..'.
	 * SECURITY: passing here is not acceptance — names are still matched
	 * against the stored list in mergeSelection().
	 *
	 * @param array $names
	 * @return array
	 */
	private static function cleanNames(array $names)
	{
		$clean = array();
		foreach ($names as $name)
		{
			if (!is_string($name) || $name === '' || $name === '.'
				|| !preg_match('/^[A-Za-z0-9._-]+$/', $name) || strpos($name, '..') !== false)
			{
				continue;
			}
			$clean[$name] = $name;
		}

		return array_values($clean);
	}

	/**
	 * The one rule for a selection: (wanted ∩ list) ∪ (basePlugins() ∩ list).
	 * SECURITY: the list is the whitelist. Result keeps the list's order.
	 *
	 * @param array $wanted  candidate names (old selection, or posted values)
	 * @param array $list    stored plugin-folder list
	 * @return array
	 */
	private static function mergeSelection(array $wanted, array $list)
	{
		$keep = array_merge($wanted, self::basePlugins());

		return array_values(array_intersect($list, $keep));
	}

	/**
	 * Write the whole structure to the plugin preference in one go.
	 *
	 * @param string $folder    'eplugins' or 'e107_plugins' (already whitelisted)
	 * @param array  $list      plugin-folder list
	 * @param array  $selected  selection (already a subset of $list)
	 * @return void
	 */
	private static function store($folder, array $list, array $selected)
	{
		e107::getPlugConfig('githubSyncLite')
			->set(self::PREF_KEY, json_encode(array(
				'folder'   => $folder,
				'list'     => array_values($list),
				'selected' => array_values($selected),
			)))
			->save(false, true, false);
	}

	/**
	 * Is the folder present in the local plugins directory? Filesystem check
	 * only. On disk is NOT installed — a standard e107 ships every core plugin
	 * folder; use isInstalled() for the real state.
	 *
	 * @param string $folder
	 * @return bool
	 */
	public static function existsOnDisk($folder)
	{
		$folder = trim((string) $folder, '/');
		if ($folder === '' || strpos($folder, '..') !== false || strpos($folder, '/') !== false)
		{
			return false;
		}

		return is_dir(e_PLUGIN . $folder);
	}

	/**
	 * Is the plugin actually INSTALLED on this site (registered in the
	 * plugin table), as opposed to merely present on disk?
	 *
	 * @param string $folder
	 * @return bool
	 */
	public static function isInstalled($folder)
	{
		$folder = trim((string) $folder, '/');
		if ($folder === '' || strpos($folder, '..') !== false || strpos($folder, '/') !== false)
		{
			return false;
		}

		return e107::isInstalled($folder);
	}
}
