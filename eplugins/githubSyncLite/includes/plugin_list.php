<?php

/**
 * githubSyncLite — plugin list source.
 *
 * Fetches the list of plugin folders present in a GitHub repo's eplugins/
 * directory (a single, small GitHub Contents API call) and caches it in
 * e107's SYSTEM cache (esystem/cache/). The cached list has NO time
 * expiry — it is used until a manual "Refresh plugin cache" clears it, or
 * a system cache clear removes it.
 *
 * One deliberate API call on refresh only; every normal page load reads
 * the cache. This keeps well clear of GitHub's unauthenticated rate limit.
 *
 * No database table. Standalone (no dependency on the full githubSync
 * plugin).
 */
class githubSyncLite_plugin_list
{
	/** System-cache tag for the plugin list. */
	const CACHE_TAG = 'githubSyncLite_plugins';

	/**
	 * Return the cached plugin-folder list, or null if nothing is cached
	 * yet (caller should prompt for a refresh). Never hits the network.
	 *
	 * @return array|null  array of folder names, or null if not cached
	 */
	public static function getCached()
	{
		// retrieve_sys(tag, MaximumAge=false => no expiry, ForcedCheck=true)
		$raw = e107::getCache()->retrieve_sys(self::CACHE_TAG, false, true);
		if ($raw === false || $raw === null || $raw === '')
		{
			return null;
		}

		$list = json_decode($raw, true);

		return is_array($list) ? $list : null;
	}

	/**
	 * Fetch the plugin-folder list fresh from GitHub and store it in the
	 * system cache. One Contents API call. Returns the list on success or
	 * false on failure (reason reported via getMessage()).
	 *
	 * @param array $p  organization, repo, branch, token, public_repo
	 * @return array|false
	 */
	public static function refresh(array $p)
	{
		$mes = e107::getMessage();

		$org    = trim((string) ($p['organization'] ?? ''));
		$repo   = trim((string) ($p['repo'] ?? ''));
		$branch = trim((string) ($p['branch'] ?? ''));
		$token  = trim((string) ($p['token'] ?? ''));

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

		$url = 'https://api.github.com/repos/' . rawurlencode($org) . '/' . rawurlencode($repo)
			. '/contents/eplugins?ref=' . rawurlencode($branch);

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
			$mes->addError('No eplugins/ folder found in ' . htmlspecialchars($org . '/' . $repo, ENT_QUOTES, 'utf-8') . ' (branch ' . htmlspecialchars($branch, ENT_QUOTES, 'utf-8') . ').');
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

		// Keep only directory entries; collect their names.
		$folders = array();
		foreach ($data as $entry)
		{
			if (isset($entry['type'], $entry['name']) && $entry['type'] === 'dir')
			{
				$folders[] = (string) $entry['name'];
			}
		}
		sort($folders, SORT_STRING);

		// set_sys(tag, data, ForceCache=true) — write even if cache pref off.
		e107::getCache()->set_sys(self::CACHE_TAG, json_encode($folders), true);

		return $folders;
	}

	/**
	 * Clear the cached plugin list (used by the manual refresh before a
	 * fresh fetch, and available for a plain "clear" action).
	 */
	public static function clearCache()
	{
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
	 * Is a plugin folder already present on disk (eplugins/{folder}/)?
	 * Local filesystem check only — no network.
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
}
