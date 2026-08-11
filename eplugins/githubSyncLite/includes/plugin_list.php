<?php

/**
 * githubSyncLite — plugin list source.
 *
 * Fetches the list of plugin folders present in a GitHub repo's plugins
 * directory — 'eplugins' (Lite layout) or 'e107_plugins' (standard layout),
 * per the 'plugins_folder' preference — with a single, small GitHub Contents
 * API call, and stores it in the PLUGIN PREFERENCES (not the system cache:
 * the core sync itself clears the system cache when it finishes, which used
 * to wipe the list, and admin cache clears did the same). The stored list
 * has no expiry — it is used until a manual "Refresh plugin list" replaces
 * it. The plugins-folder name is stored with the list, so a list made for
 * one layout is never served for the other.
 *
 * One deliberate API call on refresh only; every normal page load reads
 * the stored preference. This keeps well clear of GitHub's unauthenticated
 * rate limit.
 *
 * No database table. Standalone (no dependency on the full githubSync
 * plugin).
 */
class githubSyncLite_plugin_list
{
	/** Plugin-pref key the list is stored under. */
	const PREF_KEY = 'plugin_list';

	/** Legacy system-cache tag (pre-0.3.1 storage) — cleared on refresh. */
	const CACHE_TAG = 'githubSyncLite_plugins';

	/**
	 * Whitelist the plugins-folder name. The value becomes a GitHub API URL
	 * segment, so only the two known layouts are ever accepted; anything
	 * else falls back to the Lite default. (Duplicated in the sync engine
	 * on purpose — both files are standalone by design.)
	 *
	 * @param mixed $value
	 * @return string  'eplugins' or 'e107_plugins'
	 */
	public static function normalizePluginsFolder($value)
	{
		return in_array($value, array('eplugins', 'e107_plugins'), true) ? $value : 'eplugins';
	}

	/**
	 * Return the stored plugin-folder list, or null if nothing is stored
	 * yet or the stored list belongs to a different plugins-folder setting
	 * (caller should prompt for a refresh). Never hits the network.
	 *
	 * @param string $pluginsFolder  current 'plugins_folder' preference
	 * @return array|null  array of folder names, or null if not stored
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

		// Stored format: {'folder': <plugins folder>, 'list': [...]}.
		// A list made for the other layout counts as stale — prompt for a
		// refresh instead.
		if (!is_array($data) || !isset($data['folder'], $data['list'])
			|| $data['folder'] !== $pluginsFolder || !is_array($data['list']))
		{
			return null;
		}

		return $data['list'];
	}

	/**
	 * Fetch the plugin-folder list fresh from GitHub and store it in the
	 * system cache. One Contents API call. Returns the list on success or
	 * false on failure (reason reported via getMessage()).
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

		// Store in the PLUGIN PREFS (survives every cache clear — the core
		// sync itself clears the system cache when it finishes, which used to
		// wipe the list). The plugins-folder name is stored alongside the list
		// so getCached() can reject a list made for the other layout.
		e107::getPlugConfig('githubSyncLite')
			->set(self::PREF_KEY, json_encode(array('folder' => $plugDir, 'list' => $folders)))
			->save(false, true, false);

		// Drop the legacy pre-0.3.1 system-cache copy if one is still around.
		e107::getCache()->clear_sys(self::CACHE_TAG);

		return $folders;
	}

	/**
	 * Clear the stored plugin list (used by the manual refresh before a
	 * fresh fetch, and available for a plain "clear" action). Also drops
	 * the legacy pre-0.3.1 system-cache copy.
	 */
	public static function clearCache()
	{
		e107::getPlugConfig('githubSyncLite')->remove(self::PREF_KEY)->save(false, true, false);
		e107::getCache()->clear_sys(self::CACHE_TAG);
	}

	/**
	 * The six plugins Lite ships and installs by default — always
	 * pre-checked in the selection UI.
	 *
	 * @return array
	 */
	public static function basePlugins()
	{
		return array('navigation', 'news', 'page', 'siteinfo', 'tinymce4', 'user');
	}

	/**
	 * Is a plugin folder already present on disk (e_PLUGIN . {folder}/ —
	 * the LOCAL plugins directory, whatever this site names it)?
	 * Local filesystem check only — no network. NOTE: on-disk is NOT the
	 * same as installed — a standard e107 ships every core plugin folder
	 * on disk. Use isInstalled() for the real installation state.
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
