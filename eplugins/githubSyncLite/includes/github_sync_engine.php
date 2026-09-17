<?php
/**
 * github_sync_engine — self-contained GitHub sync engine for the githubSync plugin.
 *
 * Downloads a GitHub repository as a ZIP and extracts it into the e107 tree
 * (no shell, no `git` — works on shared hosting). Public repos use codeload;
 * private repos use the authenticated GitHub API (zipball) with a token.
 *
 * Design:
 *   - Params-driven: sync() takes everything it needs as an explicit array.
 *     The data source (currently the `github_sync` DB table) lives in the
 *     controller, not here — so this class is reusable and testable.
 *   - Self-contained: does NOT call Lite core methods (e.g.
 *     file_class::unzipGithubArchive). It keeps its own copy so it survives a
 *     re-unification of Lite with upstream e107.
 *   - Does NOT touch the DB. The controller is responsible for updating
 *     `lastsynced` after a successful run.
 *
 * Ported from the tested Lite `unzipGithubArchive` logic; see project.md.
 *
 * LITE MODIFICATION (githubSyncLite bundled copy): a 'core' sync never
 * writes the whole plugins directory. It takes an optional 'plugins' array
 * (validated folder names) and extracts ONLY those folders from the repo's
 * configured plugins directory ({plugins_folder}/) — out of the SAME archive
 * the core comes from, no separate download. Everything else under either
 * plugins-directory spelling (eplugins/ and e107_plugins/) is skipped. With
 * an empty 'plugins' array nothing under the plugins directory is ever
 * written, exactly as before 0.4.0.
 *
 * @package githubSync
 */

if (!defined('e107_INIT'))
{
	exit;
}

class github_sync_engine
{
	/** Supported sync types. */
	const SUPPORTED_TYPES = array('core', 'plugin', 'theme', 'themepack', 'language', 'other');

	/** The two whitelisted spellings of the source repo's plugins directory. */
	const PLUGINS_FOLDERS = array('eplugins', 'e107_plugins');

	/** The two whitelisted prefixes of the source repo's core directories. */
	const FOLDER_PREFIXES = array('e', 'e107_');

	/**
	 * Core directory names WITHOUT their prefix. Combined with a folder prefix
	 * ('e' or 'e107_') they give the top-level core folders a repo may carry
	 * (eadmin / e107_admin, ehandlers / e107_handlers, …). Used by
	 * detectLayout() only; buildFolderMap() keeps its explicit list.
	 */
	const CORE_DIRS = array('admin', 'core', 'docs', 'files', 'handlers', 'images', 'languages', 'media', 'system', 'themes', 'web');

	/** Files at the repo root that should never be copied into the e107 tree. */
	private $excludeFiles = array(
		'.codeclimate.yml',
		'.editorconfig',
		'.gitignore',
		'.gitmodules',
		'CONTRIBUTING.md',
		'LICENSE',
		'composer.json',
		'composer.lock',
		'install.php',
		'favicon.ico',
		'e107_config.php',
		'e107.htaccess',
		'e107.robots.txt',
	);

	/** Path fragments that, if matched anywhere in an entry, skip it. */
	private $excludeMatch = array('/.github/', '/e107_tests/');

	/**
	 * Synchronise a GitHub repository into the e107 tree.
	 *
	 * @param array $params {
	 *     @type string $organization GitHub org / username.
	 *     @type string $repo         Repository name.
	 *     @type string $branch       Branch name.
	 *     @type string $folder       Target folder (plugin/theme); defaults to $repo.
	 *     @type string $type         One of SUPPORTED_TYPES.
	 *     @type string $token        GitHub PAT (required for private repos).
	 *     @type int    $public_repo  1 = public (no token), 0 = private (token required).
	 *     @type string $plugins_folder  Source repo's plugins dir: 'eplugins' (default) or 'e107_plugins'.
	 *     @type string $folder_prefix   Source repo's core-dir prefix: 'e' (default, Lite) or 'e107_'.
	 *     @type array  $plugins         'core' only (LITE): plugin folder names to extract from
	 *                                   {plugins_folder}/ alongside the core. Default: none.
	 * }
	 * @return array|false FALSE on hard failure (validation / download / extraction);
	 *                     otherwise ['success' => [...], 'error' => [...], 'skipped' => [...]].
	 */
	public function sync(array $params)
	{
		$p = $this->validateParams($params);
		if ($p === false)
		{
			return false; // validateParams() already reported the reason
		}

		$localfile = $this->localFileName($p);
		$this->deleteTemp($localfile);

		if (!$this->download($p, $localfile))
		{
			return false; // download() already reported the reason
		}

		$unarc = $this->extractArchive($localfile);
		if ($unarc === false)
		{
			$this->deleteTemp($localfile);
			return false;
		}

		$zipBase = $this->resolveZipBase($unarc);
		if ($zipBase === null)
		{
			e107::getMessage()->addError('Could not determine the archive root folder.');
			$this->deleteTemp($localfile);
			return false;
		}

		// Layout guard: the row's folder_prefix / plugins_folder describe the
		// SOURCE repo. When the extracted archive clearly uses the other
		// spelling, every mapped prefix would match nothing and the entries
		// would end up wherever the map's fallback sends them — so stop here,
		// before anything is written, and say which value the archive needs.
		// The detected value is only reported, never applied to the row.
		$mismatches = $this->layoutMismatches($this->detectLayout($unarc, $zipBase), $p);
		if (!empty($mismatches))
		{
			$this->reportLayoutMismatch($mismatches, $p);
			$this->cleanup($localfile, $zipBase);
			return false;
		}

		$folderMap = $this->buildFolderMap($p, $zipBase);
		if (empty($folderMap))
		{
			e107::getMessage()->addError('Unsupported sync type: ' . $p['type']);
			$this->cleanup($localfile, $zipBase);
			return false;
		}

		$keepPrefix = $this->buildKeepPrefix($p, $zipBase);
		$result     = $this->relocate($unarc, $folderMap, $zipBase, $p, $keepPrefix);

		$this->cleanup($localfile, $zipBase);

		return $result;
	}

	/**
	 * Normalise + validate input. Returns a clean param array, or FALSE.
	 *
	 * @param array $params
	 * @return array|false
	 */
	private function validateParams(array $params)
	{
		$mes = e107::getMessage();

		$p = array(
			'organization' => trim((string) ($params['organization'] ?? '')),
			'repo'         => trim((string) ($params['repo'] ?? '')),
			'branch'       => trim((string) ($params['branch'] ?? '')),
			'type'         => trim((string) ($params['type'] ?? '')),
			'token'        => trim((string) ($params['token'] ?? '')),
			'public_repo'  => (int) ($params['public_repo'] ?? 1),
			'plugins_folder' => trim((string) ($params['plugins_folder'] ?? '')),
			'folder_prefix'  => trim((string) ($params['folder_prefix'] ?? '')),
		);

		// Source-repo layout. Whitelist strictly — these values become archive
		// path prefixes in the folder map, so nothing outside the two known
		// layouts is ever accepted; anything else falls back to the Lite
		// defaults. The two settings are independent (a repo may combine
		// e107_ core folders with an eplugins folder, or the other way round).
		if (!in_array($p['plugins_folder'], self::PLUGINS_FOLDERS, true))
		{
			$p['plugins_folder'] = 'eplugins';
		}
		if (!in_array($p['folder_prefix'], self::FOLDER_PREFIXES, true))
		{
			$p['folder_prefix'] = 'e';
		}

		$folder = trim((string) ($params['folder'] ?? ''), '/ ');
		$p['folder'] = ($folder !== '') ? $folder : $p['repo'];

		// LITE MODIFICATION (githubSyncLite): optional list of plugin folders
		// a 'core' sync may extract from {plugins_folder}/. Each name becomes
		// a path-segment comparison in relocate(), so it is re-validated here
		// regardless of what the caller already checked: only plain segments
		// (letters, digits, dot, underscore, hyphen; no '..', no '/') survive.
		// Everything else is dropped silently. Default: empty array (= no
		// plugin folder is written at all).
		$p['plugins'] = array();
		if (isset($params['plugins']) && is_array($params['plugins']))
		{
			foreach ($params['plugins'] as $name)
			{
				if (is_string($name) && $name !== '.' && self::isValidSegment($name))
				{
					$p['plugins'][$name] = $name;
				}
			}
			$p['plugins'] = array_values($p['plugins']);
		}

		foreach (array('organization', 'repo', 'branch') as $key)
		{
			if ($p[$key] === '')
			{
				$mes->addError('Missing required value: ' . $key);
				return false;
			}
		}

		if (!in_array($p['type'], self::SUPPORTED_TYPES, true))
		{
			$mes->addError('Unsupported sync type: ' . $p['type']);
			return false;
		}

		// Security: reject path-traversal / odd characters before any value
		// reaches a remote URL or the extract path (zip-slip defence).
		foreach (array('organization', 'repo', 'branch', 'folder') as $key)
		{
			if (!self::isValidSegment($p[$key]))
			{
				$mes->addError('Invalid characters in "' . $key . '".');
				return false;
			}
		}

		// Private repo requires a token (this guard is now actually reachable
		// because public_repo is passed in explicitly).
		if ($p['public_repo'] === 0 && $p['token'] === '')
		{
			$mes->addError("Private repository '{$p['organization']}/{$p['repo']}' requires a GitHub token.");
			return false;
		}

		return $p;
	}

	/**
	 * Validate a single path segment (organization/repo/branch/folder).
	 * Mirrors Lite e_marketplace::isValidSegment, with an explicit '..' reject.
	 * Note: branch names containing '/' are not supported (same constraint as Lite).
	 * Public+static so controllers (e.g. the add-repo form) can reuse it.
	 *
	 * @param string $segment
	 * @return bool
	 */
	public static function isValidSegment($segment)
	{
		if (!is_string($segment) || $segment === '')
		{
			return false;
		}
		if (strpos($segment, '..') !== false)
		{
			return false;
		}
		return (bool) preg_match('/^[A-Za-z0-9._-]+$/', $segment);
	}

	/**
	 * Temp ZIP filename for a given sync.
	 *
	 * @param array $p
	 * @return string
	 */
	private function localFileName(array $p)
	{
		if ($p['type'] === 'plugin' || $p['type'] === 'theme')
		{
			return $p['repo'] . '.zip';
		}
		return $p['folder'] . '-' . $p['branch'] . '.zip';
	}

	/**
	 * Download the ZIP into e_TEMP. Public repos via codeload + the native
	 * e107 fetcher; private repos via the authenticated GitHub API.
	 *
	 * @param array  $p
	 * @param string $localfile
	 * @return bool
	 */
	private function download(array $p, $localfile)
	{
		$mes = e107::getMessage();

		if ($p['public_repo'] === 0)
		{
			return $this->downloadPrivate($p, $localfile);
		}

		$remotefile = "https://codeload.github.com/{$p['organization']}/{$p['repo']}/zip/{$p['branch']}";

		$fl = e107::getFile();
		if ($fl->getRemoteFile($remotefile, $localfile) === false)
		{
			$mes->addError('Failed to download ZIP from ' . $remotefile);

			// Surface the reason instead of failing silently. The core handler
			// reports SSRF-guard refusals via getErrorMessage(); plain cURL
			// errors it writes only to the PHP error log — point there, and to
			// the plugin's Diagnostics screen, when the message is empty.
			$reason = method_exists($fl, 'getErrorMessage') ? (string) $fl->getErrorMessage() : '';
			if ($reason !== '')
			{
				$mes->addError('Core handler: ' . htmlspecialchars($reason, ENT_QUOTES, 'utf-8'));
			}
			else
			{
				$mes->addInfo('No reason reported by the core handler — check the PHP error log for a '
					. '"cURL error [" line, or use the plugin\'s Diagnostics screen.');
			}

			return false;
		}

		return true;
	}

	/**
	 * Authenticated download for a private repo via the GitHub API zipball.
	 * The token is never printed or logged.
	 *
	 * @param array  $p
	 * @param string $localfile
	 * @return bool
	 */
	private function downloadPrivate(array $p, $localfile)
	{
		$mes        = e107::getMessage();
		$remotefile = "https://api.github.com/repos/{$p['organization']}/{$p['repo']}/zipball/{$p['branch']}";

		// SSL verification stays ON in production; only relaxed under e_DEBUG
		// for local development.
		$verifySsl = !(defined('e_DEBUG') && e_DEBUG);
		if (!$verifySsl)
		{
			$mes->addWarning('SSL certificate verification disabled (e_DEBUG) — local development only.');
		}

		$ch = curl_init($remotefile);
		curl_setopt_array($ch, array(
			CURLOPT_HTTPHEADER     => array(
				'Authorization: token ' . $p['token'],
				'Accept: application/vnd.github+json',
				'User-Agent: e107-githubSync',
			),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => $verifySsl,
			CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
			CURLOPT_TIMEOUT        => 300, // large ZIPs
		));
		$body     = curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlErr  = curl_error($ch);
		curl_close($ch);

		if ($curlErr !== '' || $httpCode !== 200)
		{
			// Deliberately does not echo the response body or the token.
			$mes->addError("GitHub API download failed (HTTP {$httpCode}). Check the token and repository access.");
			return false;
		}

		if (file_put_contents(e_TEMP . $localfile, $body) === false)
		{
			$mes->addError('Could not write the downloaded archive to the temp folder.');
			return false;
		}

		return true;
	}

	/**
	 * Extract the downloaded ZIP into e_TEMP.
	 *
	 * @param string $localfile
	 * @return array|false  PclZip file list on success, FALSE on failure.
	 */
	private function extractArchive($localfile)
	{
		$mes = e107::getMessage();

		@chmod(e_TEMP . $localfile, 0755);
		require_once e_HANDLER . 'pclzip.lib.php';

		$archive = new PclZip(e_TEMP . $localfile);
		$unarc   = $archive->extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755);

		if ($archive->errorCode() != PCLZIP_ERR_NO_ERROR)
		{
			$mes->addError('Extraction failed: ' . $archive->errorInfo(true));
			return false;
		}

		if (empty($unarc))
		{
			$mes->addError('Extracted archive is empty — no files found.');
			return false;
		}

		return $unarc;
	}

	/**
	 * Derive the archive's top-level folder name from its first entry.
	 * Works for both codeload ("{repo}-{branch}") and API zipball
	 * ("{owner}-{repo}-{sha}") layouts.
	 *
	 * @param array $unarc
	 * @return string|null
	 */
	private function resolveZipBase(array $unarc)
	{
		if (isset($unarc[0]) && $unarc[0]['folder'] == 1)
		{
			$zipBase = rtrim($unarc[0]['stored_filename'], '/');
			if ($zipBase !== '' && $zipBase !== '..' && strpos($zipBase, '/') === false)
			{
				return $zipBase;
			}
		}
		return null;
	}

	/**
	 * Detect which source layout the extracted archive actually uses, from
	 * its top-level directories under {zipBase}/ (the entry list PclZip
	 * already returned — the archive is not opened a second time).
	 *
	 * For each of the two layout settings the result lists every spelling
	 * found, keyed by the whitelisted value, with the top-level folder(s)
	 * that revealed it:
	 *
	 *   'plugins_folder' => array('e107_plugins' => array('e107_plugins/'))
	 *   'folder_prefix'  => array('e107_' => array('e107_languages/', 'e107_themes/'))
	 *
	 * A spelling that is not present is simply absent from the list, so an
	 * empty list means the archive carries no such folder at all (a
	 * single-plugin repo, a theme repo, an 'other' row) — which is NOT a
	 * mismatch, see layoutMismatches(). Only directories count: a folder
	 * entry, or any entry with a further path segment below the top level.
	 *
	 * @param array  $unarc    PclZip entry list.
	 * @param string $zipBase  Archive top-level folder.
	 * @return array  array('plugins_folder' => array, 'folder_prefix' => array)
	 */
	private function detectLayout(array $unarc, $zipBase)
	{
		$found = array('plugins_folder' => array(), 'folder_prefix' => array());
		$base  = $zipBase . '/';
		$seen  = array();

		foreach ($unarc as $v)
		{
			$stored = str_replace('\\', '/', (string) $v['stored_filename']);
			if (strpos($stored, $base) !== 0)
			{
				continue;
			}

			$rest  = substr($stored, strlen($base));
			$slash = strpos($rest, '/');

			// Top-level segment, and whether it is a directory.
			if ($slash === false)
			{
				$name  = $rest;
				$isDir = (!empty($v['folder']));
			}
			else
			{
				$name  = substr($rest, 0, $slash);
				$isDir = true;
			}

			if ($name === '' || !$isDir || isset($seen[$name]))
			{
				continue;
			}
			$seen[$name] = true;

			if (in_array($name, self::PLUGINS_FOLDERS, true))
			{
				$found['plugins_folder'][$name][] = $name . '/';
				continue;
			}

			foreach (self::FOLDER_PREFIXES as $px)
			{
				if (strpos($name, $px) === 0 && in_array(substr($name, strlen($px)), self::CORE_DIRS, true))
				{
					$found['folder_prefix'][$px][] = $name . '/';
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * Compare the detected layout with the declared one. A setting is a
	 * mismatch only on a clear contradiction: the archive uses exactly ONE
	 * spelling and it is not the one the row declares. Nothing found is not a
	 * mismatch (the repo simply has no such folder), and both spellings found
	 * is not one either (whatever the row says matches something).
	 *
	 * @param array $detected  Result of detectLayout().
	 * @param array $p         Validated sync params (folder_prefix, plugins_folder, …).
	 * @return array  One entry per mismatched setting, keyed by setting name:
	 *                array('declared' => string, 'detected' => string, 'folders' => array).
	 */
	private function layoutMismatches(array $detected, array $p)
	{
		$mismatches = array();

		foreach (array('folder_prefix', 'plugins_folder') as $setting)
		{
			$spellings = $detected[$setting] ?? array();
			if (count($spellings) !== 1)
			{
				continue; // absent, or both spellings present: no contradiction
			}

			$value = (string) key($spellings);
			if ($value === $p[$setting])
			{
				continue;
			}

			$mismatches[$setting] = array(
				'declared' => $p[$setting],
				'detected' => $value,
				'folders'  => $spellings[$value],
			);
		}

		return $mismatches;
	}

	/**
	 * Report a layout mismatch that aborted the sync: which sync it was
	 * (organization/repo, branch, type), which value is declared, which
	 * spelling the archive contains (with the folders that show it) and the
	 * value that would work — via e107::getMessage() and, identically, via
	 * e107::getLog() so it can be diagnosed from the admin log. The detected
	 * value is named for the admin to pick in the dropdown; it is never
	 * written to the row. Archive paths are escaped before rendering;
	 * nothing else from the sync params (in particular no token) is included.
	 *
	 * @param array $mismatches  Result of layoutMismatches() (non-empty).
	 * @param array $p           Validated sync params.
	 * @return void
	 */
	private function reportLayoutMismatch(array $mismatches, array $p)
	{
		$labels = array(
			'folder_prefix'  => 'folder prefix',
			'plugins_folder' => 'plugins folder',
		);

		$esc = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'utf-8'); };

		$source = $esc($p['organization']) . '/' . $esc($p['repo'])
			. ' (branch ' . $esc($p['branch']) . ', type ' . $esc($p['type']) . ')';

		$lines = array();
		foreach ($mismatches as $setting => $m)
		{
			$label   = $labels[$setting] ?? $setting;
			$folders = array_map($esc, array_slice($m['folders'], 0, 5));
			$more    = (count($m['folders']) > count($folders)) ? ' (+' . (count($m['folders']) - count($folders)) . ' more)' : '';

			$lines[] = 'The configured ' . $label . ' is <strong>' . $esc($m['declared']) . '</strong>, but the archive uses <strong>'
				. $esc($m['detected']) . '</strong> (found: ' . implode(', ', $folders) . $more . '). '
				. 'Change the ' . $label . ' setting to <strong>' . $esc($m['detected']) . '</strong> and run the sync again.';
		}

		$detail = 'Sync of ' . $source . ' aborted: the declared source layout does not match the archive. '
			. implode(' ', $lines) . ' Nothing was written.';

		e107::getMessage()->addError($detail);
		e107::getLog()->add('Sync aborted: source layout mismatch', strip_tags($detail), E_LOG_WARNING, '');
	}

	/**
	 * Build the {zip path prefix => destination path} remap for a sync type.
	 *
	 * NOTE: the folder names on the LEFT side depend on the SOURCE repo's
	 * directory layout and come from two whitelisted params (see
	 * validateParams()): $p['folder_prefix'] ('e' or 'e107_') for the
	 * standard core directories, and $p['plugins_folder'] ('eplugins' or
	 * 'e107_plugins') for the plugins directory. The former hardcoded mixed
	 * convention was replaced by these preferences (v0.2.0).
	 *
	 * @param array  $p
	 * @param string $zipBase
	 * @return array
	 */
	private function buildFolderMap(array $p, $zipBase)
	{
		$px      = $p['folder_prefix'];   // 'e' or 'e107_' (whitelisted)
		$plugDir = $p['plugins_folder'];  // 'eplugins' or 'e107_plugins' (whitelisted)

		switch ($p['type'])
		{
			case 'plugin':
				// Folder-scoped single plugin. Extract ONLY {plugins_folder}/{folder}/
				// from the repo zip into the LOCAL plugins dir. The folder stays in the
				// path after str_replace, so it is NOT appended to the destination.
				// relocate() additionally skips anything outside keepPrefix (see
				// buildKeepPrefix()). Identical layout to the marketplace reference.
				return array(
					$zipBase . '/' . $plugDir . '/' => e_BASE . e107::getFolder('PLUGINS'),
				);

			case 'theme':
				// TODO: align with 'plugin' (folder-scoped e107_themes/{folder}/) when
				// theme sync is tackled. Left as the legacy root-layout map for now.
				return array(
					$zipBase => e_BASE . e107::getFolder('THEMES') . $p['folder'],
				);

			case 'core':
				$map = array(
					$zipBase . '/' . $px . 'admin/'     => e_BASE . e107::getFolder('ADMIN'),
					$zipBase . '/' . $px . 'core/'      => e_BASE . e107::getFolder('CORE'),
					$zipBase . '/' . $px . 'docs/'      => e_BASE . e107::getFolder('DOCS'),
					$zipBase . '/' . $px . 'files/'     => e_BASE . e107::getFolder('FILES'),
					$zipBase . '/' . $px . 'handlers/'  => e_BASE . e107::getFolder('HANDLERS'),
					$zipBase . '/' . $px . 'images/'    => e_BASE . e107::getFolder('IMAGES'),
					$zipBase . '/' . $px . 'languages/' => e_BASE . e107::getFolder('LANGUAGES'),
					$zipBase . '/' . $px . 'media/'     => e_BASE . e107::getFolder('MEDIA'),
					$zipBase . '/' . $px . 'system/'    => e_BASE . e107::getFolder('SYSTEM'),
					$zipBase . '/' . $px . 'themes/'    => e_BASE . e107::getFolder('THEMES'),
					$zipBase . '/' . $px . 'web/'       => e_BASE . e107::getFolder('WEB'),
				);

				// LITE MODIFICATION (githubSyncLite): the plugins folder is mapped
				// ONLY when the caller selected plugin folders, and relocate()
				// then lets through just those folders (skipping the rest of
				// the plugins directory, and the other spelling entirely). With
				// nothing selected there is no mapping at all, and relocate()
				// skips both spellings so the catch-all cannot pull them in.
				// ORDER MATTERS: relocate() applies the map with str_replace()
				// on ordered arrays, so this entry must come before the
				// catch-all ($zipBase.'/' => e_BASE), which must stay last.
				if (!empty($p['plugins']))
				{
					$map[$zipBase . '/' . $plugDir . '/'] = e_BASE . e107::getFolder('PLUGINS');
				}

				$map[$zipBase . '/'] = e_BASE;

				return $map;

			case 'themepack':
				return array(
					$zipBase . '/' . $plugDir . '/'      => e_BASE . e107::getFolder('PLUGINS'),
					$zipBase . '/' . $px . 'themes/'     => e_BASE . e107::getFolder('THEMES'),
					$zipBase . '/'                       => e_BASE,
				);

			case 'other':
				// Root-layout grab for ad-hoc / manually-synced repos that do NOT
				// follow the {plugins_folder}/{folder}/ standard: the whole repo root
				// goes into one named LOCAL plugin folder. No plugins-folder remap
				// and no pack-style catch-all into e_BASE, so it cannot overwrite
				// core directories. hasTraversal() still guards every entry in
				// relocate().
				return array(
					$zipBase => e_BASE . e107::getFolder('PLUGINS') . $p['folder'],
				);

			case 'language':
				// A language pack has exactly three legitimate destinations:
				// the languages folder, a plugin's folder and a theme's folder.
				// There is deliberately NO catch-all ($zipBase.'/' => e_BASE):
				// an entry matching none of these prefixes (e.g. when the
				// row's folder_prefix does not match the repo layout) is
				// skipped and reported by relocate(), never written to the
				// site root.
				return array(
					$zipBase . '/' . $px . 'languages/' => e_BASE . e107::getFolder('LANGUAGES'),
					$zipBase . '/' . $plugDir . '/'     => e_BASE . e107::getFolder('PLUGINS'),
					$zipBase . '/' . $px . 'themes/'    => e_BASE . e107::getFolder('THEMES'),
				);
		}

		return array();
	}

	/**
	 * Positive extraction prefix for strict folder-scoped types. When non-empty,
	 * relocate() extracts ONLY archive entries beginning with this prefix and
	 * skips everything else (matches the marketplace reference). Empty string
	 * means "no scoping" (the map's own prefixes + catch-all decide placement).
	 *
	 * @param array  $p
	 * @param string $zipBase
	 * @return string
	 */
	private function buildKeepPrefix(array $p, $zipBase)
	{
		if ($p['type'] === 'plugin')
		{
			return $zipBase . '/' . $p['plugins_folder'] . '/' . $p['folder'] . '/';
		}

		// 'theme' will join here once it becomes folder-scoped (see buildFolderMap).
		return '';
	}

	/**
	 * Move extracted entries from e_TEMP into their destinations.
	 * Uses copy+unlink (the tested Lite pattern) and rejects any archive entry
	 * containing a '..' path segment (zip-slip defence). For 'language',
	 * skips (and reports) every entry that matches none of the mapped prefixes,
	 * and skips translations for plugins/themes not present on this site. For
	 * strict folder-scoped types a $keepPrefix skips everything outside the folder.
	 * For 'core' (LITE), entries under the plugins directory are let through
	 * only for the selected plugin folders — see skipCorePluginEntry().
	 *
	 * @param array  $unarc
	 * @param array  $folderMap
	 * @param string $zipBase
	 * @param array  $p           Validated sync params (type, plugins_folder, folder_prefix, …).
	 * @param string $keepPrefix
	 * @return array ['success' => [...], 'error' => [...], 'skipped' => [...]]
	 */
	private function relocate(array $unarc, array $folderMap, $zipBase, array $p, $keepPrefix = '')
	{
		$type = $p['type'];
		$excludes = array();
		foreach ($this->excludeFiles as $f)
		{
			$excludes[] = $zipBase . '/' . $f;
		}

		$srch = array_keys($folderMap);
		$repl = array_values($folderMap);

		$success   = array();
		$error     = array();
		$skipped   = array();
		$unmatched = array(); // 'language' only: entries outside every mapped prefix

		foreach ($unarc as $v)
		{
			$stored = $v['stored_filename'];

			// language: the map has no catch-all (see buildFolderMap()), so an
			// entry that starts with none of the mapped prefixes has nowhere
			// legitimate to go. Skip it and remember it for the report instead
			// of letting str_replace() below leave the path untouched and
			// write it relative to the current directory. The archive root
			// folder itself is skipped silently — it is not content.
			if ($type === 'language' && !$this->matchesPrefix($stored, $srch))
			{
				$skipped[] = $stored;
				if ($stored !== $zipBase . '/' && $stored !== $zipBase)
				{
					$unmatched[] = $stored;
				}
				continue;
			}

			// Folder-scoped extract: skip anything outside the requested folder.
			if ($keepPrefix !== '' && strpos($stored, $keepPrefix) !== 0)
			{
				$skipped[] = $stored;
				continue;
			}

			// LITE MODIFICATION (githubSyncLite): for a core sync, the plugins
			// directory is written SELECTIVELY. Entries under the non-configured
			// spelling are always skipped; entries under the configured
			// spelling ({plugins_folder}/) are kept only when their first path
			// segment is one of the validated $p['plugins'] names. With an
			// empty selection nothing under either spelling is ever written —
			// the catch-all ($zipBase.'/' => e_BASE) would otherwise relocate
			// plugin files, which is why the skip happens here explicitly.
			if ($type === 'core' && $this->skipCorePluginEntry($stored, $zipBase, $p))
			{
				$skipped[] = $stored;
				continue;
			}

			if ($this->matchFound($stored, $this->excludeMatch) || in_array($stored, $excludes, true))
			{
				$skipped[] = $stored;
				continue;
			}

			// language: only translate plugins/themes that exist here.
			if ($this->skipMissingTarget($stored, $zipBase, $p))
			{
				$skipped[] = $stored;
				continue;
			}

			// zip-slip guard: reject any entry with a '..' path segment.
			// e107 path constants are relative, so we validate the archive
			// entry itself rather than an absolute destination prefix.
			if ($this->hasTraversal($stored))
			{
				$error[] = $stored . ' (rejected: path traversal)';
				continue;
			}

			$oldPath = $v['filename'];
			$newPath = str_replace($srch, $repl, $stored);

			if ($v['folder'] == 1)
			{
				if (is_dir($newPath))
				{
					continue;
				}
				@mkdir($newPath, 0755, true);
				$success[] = $newPath;
				continue;
			}

			$dir = dirname($newPath);
			if (!is_dir($dir))
			{
				@mkdir($dir, 0755, true);
			}

			if (@copy($oldPath, $newPath))
			{
				@unlink($oldPath);
				$success[] = $newPath;
			}
			else
			{
				$error[] = $newPath;
			}
		}

		if (!empty($unmatched))
		{
			$this->reportUnmatched($unmatched, $p);
		}

		return array('success' => $success, 'error' => $error, 'skipped' => $skipped);
	}

	/**
	 * True if $path starts with any of the given archive path prefixes.
	 *
	 * @param string $path      Archive entry path.
	 * @param array  $prefixes  Left-hand side of the folder map.
	 * @return bool
	 */
	private function matchesPrefix($path, array $prefixes)
	{
		foreach ($prefixes as $prefix)
		{
			if (strpos($path, $prefix) === 0)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Report archive entries a 'language' sync skipped because they matched
	 * none of the mapped prefixes: a count plus the first few paths via
	 * e107::getMessage(), and the same via e107::getLog() so a misconfigured
	 * row (folder_prefix / plugins_folder not matching the repo layout) can be
	 * diagnosed from the admin log. Paths are escaped before rendering;
	 * nothing else from the sync params (in particular no token) is included.
	 *
	 * @param array $unmatched  Skipped archive entry paths.
	 * @param array $p          Validated sync params (folder_prefix, plugins_folder, …).
	 * @return void
	 */
	private function reportUnmatched(array $unmatched, array $p)
	{
		$total   = count($unmatched);
		$sample  = array_slice($unmatched, 0, 5);
		$safe    = array_map(function ($path) { return htmlspecialchars($path, ENT_QUOTES, 'utf-8'); }, $sample);
		$safePx  = htmlspecialchars($p['folder_prefix'], ENT_QUOTES, 'utf-8');
		$safePlg = htmlspecialchars($p['plugins_folder'], ENT_QUOTES, 'utf-8');
		$more    = ($total > count($sample)) ? ' (+' . ($total - count($sample)) . ' more)' : '';

		$detail = $total . ' archive entr' . ($total === 1 ? 'y' : 'ies') . ' matched none of the mapped folders '
			. '(' . $safePx . 'languages/, ' . $safePlg . '/, ' . $safePx . 'themes/) and were not written: '
			. implode(', ', $safe) . $more
			. '. If these are translations, check the row\'s folder prefix / plugins folder layout.';

		e107::getMessage()->addWarning($detail);
		e107::getLog()->add('Language sync: unmatched entries skipped', $detail, E_LOG_WARNING, '');
	}

	/**
	 * Remove the temp ZIP and the extracted temp folder.
	 *
	 * @param string      $localfile
	 * @param string|null $zipBase
	 * @return void
	 */
	private function cleanup($localfile, $zipBase)
	{
		$this->deleteTemp($localfile);

		if ($zipBase !== null && is_dir(e_TEMP . $zipBase))
		{
			$this->removeDir(e_TEMP . $zipBase);
		}
	}

	/**
	 * @param string $localfile
	 * @return void
	 */
	private function deleteTemp($localfile)
	{
		if ($localfile !== '' && file_exists(e_TEMP . $localfile))
		{
			@unlink(e_TEMP . $localfile);
		}
	}

	/**
	 * Recursively delete a directory — confined to e_TEMP for safety.
	 *
	 * @param string $dir
	 * @return void
	 */
	private function removeDir($dir)
	{
		// Safety: only ever operate inside e_TEMP (string-prefix check —
		// e107 path constants are relative, so no normalisation needed).
		if (!is_dir($dir) || strpos($dir, e_TEMP) !== 0)
		{
			return;
		}

		foreach (scandir($dir) as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}
			$path = $dir . '/' . $item;
			if (is_dir($path))
			{
				$this->removeDir($path);
			}
			else
			{
				@unlink($path);
			}
		}
		@rmdir($dir);
	}

	/**
	 * LITE MODIFICATION (githubSyncLite): decide whether a 'core' sync must
	 * skip an archive entry because of the plugins directory rules.
	 *
	 *   - entry under the NON-configured plugins spelling → always skip;
	 *   - entry under the configured spelling ({plugins_folder}/) → keep only
	 *     when the first path segment after it is in the validated
	 *     $p['plugins'] array (exact, case-sensitive match); otherwise skip;
	 *   - the plugins container folder entry itself → keep only when the
	 *     array is non-empty;
	 *   - anything else (core folders, root files) → not this method's
	 *     business, never skipped here.
	 *
	 * An empty $p['plugins'] therefore reproduces the pre-0.4.0 behaviour:
	 * nothing under either plugins directory is ever written.
	 *
	 * @param string $stored   Archive entry path.
	 * @param string $zipBase  Archive top-level folder.
	 * @param array  $p        Validated sync params (plugins_folder, plugins, …).
	 * @return bool  TRUE = skip this entry.
	 */
	private function skipCorePluginEntry($stored, $zipBase, array $p)
	{
		$configured = $p['plugins_folder'];
		$other      = ($configured === 'eplugins') ? 'e107_plugins' : 'eplugins';

		// The other spelling is never written by a core sync, whatever the
		// selection says — belt-and-braces against a wrong 'plugins_folder'
		// preference.
		if (strpos($stored, $zipBase . '/' . $other . '/') === 0)
		{
			return true;
		}

		$prefix = $zipBase . '/' . $configured . '/';
		if (strpos($stored, $prefix) !== 0)
		{
			return false; // not a plugins-directory entry
		}

		if (empty($p['plugins']))
		{
			return true; // nothing selected: the whole directory is skipped
		}

		$rest = substr($stored, strlen($prefix));
		if ($rest === '' || $rest === false)
		{
			return false; // the plugins container folder itself
		}

		$segments = explode('/', $rest, 2);
		$name     = $segments[0];

		// Files directly under the plugins directory have no folder segment
		// and can never match a selected folder name, so they are skipped too.
		return !in_array($name, $p['plugins'], true);
	}

	/**
	 * Substring match of $file against any term in $array.
	 *
	 * @param string $file
	 * @param array  $array
	 * @return bool
	 */
	private function matchFound($file, $array)
	{
		foreach ($array as $term)
		{
			if (strpos($file, $term) !== false)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * For a 'language' pack, decide whether an entry should be skipped because
	 * it carries a translation for a plugin/theme that is not present on this
	 * site. Entries under {plugins_folder}/<X>/ or {prefix}themes/<X>/ are
	 * skipped when the local <X> folder does not exist. Core languages and
	 * root entries are always kept.
	 *
	 * @param string $stored   Archive entry path.
	 * @param string $zipBase  Archive top-level folder.
	 * @param array  $p        Validated sync params (type, plugins_folder, folder_prefix, …).
	 * @return bool
	 */
	private function skipMissingTarget($stored, $zipBase, array $p)
	{
		if ($p['type'] !== 'language')
		{
			return false;
		}

		$targets = array(
			$zipBase . '/' . $p['plugins_folder'] . '/'       => e_BASE . e107::getFolder('PLUGINS'),
			$zipBase . '/' . $p['folder_prefix'] . 'themes/'  => e_BASE . e107::getFolder('THEMES'),
		);

		foreach ($targets as $prefix => $localBase)
		{
			if (strpos($stored, $prefix) !== 0)
			{
				continue;
			}

			$rest = substr($stored, strlen($prefix));
			if ($rest === '')
			{
				return false; // the plugins container folder itself
			}

			$name = strtok($rest, '/');
			if ($name === false || $name === '')
			{
				return false;
			}

			// Skip when the local plugin/theme folder is absent.
			return !is_dir($localBase . $name);
		}

		return false;
	}

	/**
	 * True if any path segment is '..' (archive path-traversal / zip-slip).
	 *
	 * @param string $path
	 * @return bool
	 */
	private function hasTraversal($path)
	{
		foreach (explode('/', str_replace('\\', '/', $path)) as $segment)
		{
			if ($segment === '..')
			{
				return true;
			}
		}
		return false;
	}
}
