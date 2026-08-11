<?php

/**
 * githubSyncLite — admin/admin_debug.php  (mode: debug)
 *
 * Read-only diagnostics for the sync connection. Nothing here changes the
 * sync itself — this page only inspects the environment and runs isolated
 * test requests, so a broken sync can be analysed without touching it.
 *
 * What it shows / tests:
 *   (A) Environment — PHP, cURL, and the CA-bundle settings
 *       (curl.cainfo / openssl.cafile) with existence checks.
 *   (B) DNS + SSRF-guard check — dns_get_record() for the GitHub hosts and
 *       the core handler's isUrlSafe() verdict for the exact ZIP URL. The
 *       guard resolves hosts via dns_get_record(), which fails on some
 *       Windows/WAMP stacks and then produces the "Refused to fetch URL
 *       with non-HTTP(S) scheme or private/reserved IP" refusal even for
 *       public hosts.
 *   (C) Connection tests (buttons) — small direct cURL requests with SSL
 *       verification ON and OFF (isolates a missing CA bundle from network
 *       problems), plus the same download path the sync really uses
 *       (e107::getFile()->getRemoteFile()) on a tiny file. A separate heavy
 *       button repeats it with the full repo ZIP.
 *
 * No token is ever used or displayed on this page — all tests go to public
 * GitHub endpoints only.
 */

require_once('../../../class2.php');

if (!getperms('P'))
{
	e107::redirect('admin');
	exit;
}

e107_require_once('admin_menu.php'); // shared dispatcher
e107_require_once(e_PLUGIN . 'githubSyncLite/includes/github_sync_engine.php'); // isValidSegment() reuse


class githubSyncLite_debug_ui extends e_admin_ui
{
	protected $pluginTitle = 'Github Sync Lite';
	protected $pluginName  = 'githubSyncLite';
	protected $table       = ''; // prefs only — no table
	protected $pid         = '';

	protected $defaultAction = 'main';

	/**
	 * Minimal source config (org/repo/branch only — no token: this page
	 * never authenticates).
	 */
	protected function sourceConfig()
	{
		$cfg = e107::getPlugConfig('githubSyncLite');

		return array(
			'organization' => (string) $cfg->get('organization', ''),
			'repo'         => (string) $cfg->get('repo', ''),
			'branch'       => (string) $cfg->get('branch', ''),
		);
	}

	/**
	 * The codeload ZIP URL the public-repo sync downloads — the exact URL
	 * that fails. Returns '' when the source is not configured or a segment
	 * is invalid.
	 */
	protected function zipUrl()
	{
		$c = $this->sourceConfig();

		foreach (array('organization', 'repo', 'branch') as $key)
		{
			if (!github_sync_engine::isValidSegment($c[$key]))
			{
				return '';
			}
		}

		return "https://codeload.github.com/{$c['organization']}/{$c['repo']}/zip/{$c['branch']}";
	}

	public function mainPage()
	{
		$this->addTitle('Diagnostics');

		$mes = e107::getMessage();
		$frm = e107::getForm();
		$req = $this->getRequest();

		if (!getperms('0'))
		{
			$mes->addError('Only the main admin can use the diagnostics page.');
			return $mes->render();
		}

		$zipUrl = $this->zipUrl();

		$testResults = '';

		// --- POST: run the connection tests ---------------------------------
		if ($req->getPosted('run_tests') || $req->getPosted('run_zip_test'))
		{
			if (!e107::getSession()->checkFormToken($req->getPosted('e-token', '')))
			{
				$mes->addError('Invalid security token.');
			}
			elseif ($zipUrl === '')
			{
				$mes->addError('Source repository is not configured (or contains invalid characters). Set it on the Source screen first.');
			}
			elseif ($req->getPosted('run_zip_test'))
			{
				$testResults = $this->runZipTest($zipUrl);
			}
			else
			{
				$testResults = $this->runConnectionTests($zipUrl);
			}
		}

		$out  = $mes->render();
		$out .= e107::getRender()->tablerender('Environment', $this->renderEnvironment(), 'gsl-diag-env', true);
		$out .= e107::getRender()->tablerender('DNS + SSRF guard', $this->renderGuardCheck($zipUrl), 'gsl-diag-guard', true);

		// --- Test buttons ---------------------------------------------------
		$buttons  = '<p>Small, isolated test requests to public GitHub endpoints. '
			. 'Nothing is installed and the sync configuration is not touched. '
			. 'The heavy button additionally downloads the full repo ZIP to the temp folder '
			. '(and deletes it afterwards) — the exact call the sync makes.</p>';
		$buttons .= $frm->open('gsl_diag', 'post', e_SELF . '?mode=debug&action=main');
		$buttons .= $frm->token();
		$buttons .= $frm->admin_button('run_tests', 1, 'other', 'Run connection tests');
		$buttons .= ' ' . $frm->admin_button('run_zip_test', 1, 'delete', 'Run full ZIP download test (heavy)');
		$buttons .= $frm->close();

		$out .= e107::getRender()->tablerender('Connection tests', $buttons, 'gsl-diag-buttons', true);

		if ($testResults !== '')
		{
			$out .= e107::getRender()->tablerender('Test results', $testResults, 'gsl-diag-results', true);
		}

		return $out;
	}

	// =====================================================================
	// (A) Environment
	// =====================================================================

	protected function renderEnvironment()
	{
		$rows = array();

		$rows[] = array('PHP version', PHP_VERSION . ' (' . PHP_OS . ', ' . PHP_SAPI . ')');

		if (function_exists('curl_version'))
		{
			$cv = curl_version();
			$rows[] = array('cURL', 'installed — version ' . ($cv['version'] ?? '?')
				. ', SSL: ' . ($cv['ssl_version'] ?? '?'));
		}
		else
		{
			$rows[] = array('cURL', '<span class="label label-danger">NOT installed</span> — the sync cannot work without it');
		}

		$rows[] = $this->caBundleRow('curl.cainfo');
		$rows[] = $this->caBundleRow('openssl.cafile');

		$openBasedir = (string) ini_get('open_basedir');
		$rows[] = array('open_basedir', $openBasedir !== '' ? htmlspecialchars($openBasedir, ENT_QUOTES, 'utf-8') : '(not set)');

		$rows[] = array('allow_url_fopen', ini_get('allow_url_fopen') ? 'on' : 'off (cURL is used anyway)');

		$rows[] = array('e_DEBUG', (defined('e_DEBUG') && e_DEBUG) ? 'defined (SSL verification may be relaxed in some code paths)' : 'not defined');

		$rows[] = array('e_REMOTE_FILE_ALLOW_PRIVATE',
			(defined('e_REMOTE_FILE_ALLOW_PRIVATE') && e_REMOTE_FILE_ALLOW_PRIVATE === true)
				? '<span class="label label-warning">defined</span> — the SSRF guard is bypassed'
				: 'not defined (SSRF guard active)');

		$pluginVersion = '?';
		$xml = @simplexml_load_file(e_PLUGIN . 'githubSyncLite/plugin.xml');
		if ($xml !== false && isset($xml['version']))
		{
			$pluginVersion = (string) $xml['version'];
		}
		$rows[] = array('e107 / plugin', 'e107 ' . e_VERSION . ' &middot; githubSyncLite ' . $pluginVersion);

		return $this->renderRows($rows);
	}

	/**
	 * One row for a CA-bundle ini directive: value + does the file exist /
	 * is it readable. This answers "is the CA bundle actually installed".
	 */
	protected function caBundleRow($directive)
	{
		$value = (string) ini_get($directive);

		if ($value === '')
		{
			return array($directive,
				'<span class="label label-warning">not set</span> — cURL falls back to its compiled-in CA path; '
				. 'on Windows/WAMP there usually is none, which produces cURL error 60 when SSL verification is on');
		}

		$safe = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

		if (!@file_exists($value))
		{
			return array($directive, $safe . ' — <span class="label label-danger">file does NOT exist</span>');
		}
		if (!@is_readable($value))
		{
			return array($directive, $safe . ' — <span class="label label-danger">file is not readable</span>');
		}

		return array($directive, $safe . ' — <span class="label label-success">exists, readable</span> ('
			. number_format((int) @filesize($value)) . ' bytes)');
	}

	// =====================================================================
	// (B) DNS + SSRF guard
	// =====================================================================

	protected function renderGuardCheck($zipUrl)
	{
		$rows = array();

		// The hosts the public sync path actually contacts.
		foreach (array('codeload.github.com', 'api.github.com', 'raw.githubusercontent.com') as $host)
		{
			$rows[] = array('dns_get_record: ' . $host, $this->dnsReport($host));
		}

		if ($zipUrl === '')
		{
			$rows[] = array('isUrlSafe()', 'Source repository not configured — set it on the Source screen to test the exact ZIP URL.');
			return $this->renderRows($rows);
		}

		$safeUrl = htmlspecialchars($zipUrl, ENT_QUOTES, 'utf-8');
		$rows[]  = array('ZIP URL', $safeUrl);

		$fl = e107::getFile();
		if (!method_exists($fl, 'isUrlSafe'))
		{
			$rows[] = array('isUrlSafe()', 'method not present in this file_class version — SSRF guard check skipped');
			return $this->renderRows($rows);
		}

		$verdict = $fl->isUrlSafe($zipUrl);
		if ($verdict)
		{
			$rows[] = array('isUrlSafe()', '<span class="label label-success">PASS</span> — the SSRF guard is not the blocker');
		}
		else
		{
			$rows[] = array('isUrlSafe()',
				'<span class="label label-danger">REFUSED</span> — this is the exact condition that produces '
				. '"Refused to fetch URL with non-HTTP(S) scheme or private/reserved IP". For a public GitHub host '
				. 'the usual cause is dns_get_record() failing on this stack (see the DNS rows above): the guard '
				. 'treats an unresolvable host as unsafe.');
		}

		return $this->renderRows($rows);
	}

	protected function dnsReport($host)
	{
		$records = @dns_get_record($host, DNS_A | DNS_AAAA);

		if ($records === false)
		{
			return '<span class="label label-danger">FAILED</span> — dns_get_record() returned false. '
				. 'The core SSRF guard uses this exact call, so it will refuse every URL on this host.';
		}
		if (!is_array($records) || count($records) === 0)
		{
			return '<span class="label label-danger">EMPTY</span> — no A/AAAA records returned. '
				. 'The SSRF guard treats this as unresolvable and refuses the URL.';
		}

		$ips = array();
		foreach ($records as $r)
		{
			if (!empty($r['ip']))   { $ips[] = $r['ip']; }
			if (!empty($r['ipv6'])) { $ips[] = $r['ipv6']; }
		}

		$gh = @gethostbyname($host); // independent resolver path, for comparison
		$note = ($gh !== false && $gh !== $host) ? ' &middot; gethostbyname: ' . htmlspecialchars($gh, ENT_QUOTES, 'utf-8') : '';

		return '<span class="label label-success">OK</span> — '
			. htmlspecialchars(implode(', ', $ips), ENT_QUOTES, 'utf-8') . $note;
	}

	// =====================================================================
	// (C) Connection tests
	// =====================================================================

	/**
	 * The light test set: two small direct cURL requests (SSL verify ON and
	 * OFF) plus the core handler on a tiny file. Together they separate the
	 * three failure classes: DNS/guard, CA bundle, and handler-internal.
	 */
	protected function runConnectionTests($zipUrl)
	{
		$c = $this->sourceConfig();

		$out = '';

		// 1) direct cURL, small endpoint, SSL verify ON — DNS + TLS + CA in one go
		$out .= $this->renderCurlTest(
			'Direct cURL — https://api.github.com/zen (SSL verify ON)',
			'https://api.github.com/zen', false, true,
			'Fails with cURL error 60 = missing/unusable CA bundle. Fails with error 6 = DNS. HTTP 200 = network + TLS fine.'
		);

		// 2) same, SSL verify OFF — isolates the CA bundle from the network
		$out .= $this->renderCurlTest(
			'Direct cURL — https://api.github.com/zen (SSL verify OFF, diagnostic only)',
			'https://api.github.com/zen', false, false,
			'If this succeeds while the verify-ON test fails with error 60, the CA bundle is the only problem.'
		);

		// 3) the real ZIP URL, HEAD, verify ON — codeload reachable?
		$out .= $this->renderCurlTest(
			'Direct cURL — repo ZIP URL, HEAD request (SSL verify ON)',
			$zipUrl, true, true,
			'Only the connection matters here: an HTTP 403/405 on HEAD still means DNS + TLS worked. '
			. 'cURL error 60 = CA bundle, error 6 = DNS.'
		);

		// 4) the core handler on a tiny file — the sync's actual code path
		$tinyUrl = "https://raw.githubusercontent.com/{$c['organization']}/{$c['repo']}/{$c['branch']}/index.php";
		$out .= $this->renderHandlerTest(
			'Core handler — e107::getFile()->getRemoteFile() on a tiny file',
			$tinyUrl,
			'This is the exact code path the sync uses (including the SSRF guard and whatever SSL settings the '
			. 'core handler applies). If the direct tests pass but this fails, the blocker is inside the core '
			. 'handler — check the guard verdict above and the PHP error log (the handler logs cURL errors there).'
		);

		return $out;
	}

	/**
	 * The heavy test: the core handler on the full repo ZIP — the exact
	 * call that fails in the sync. The file is deleted afterwards.
	 */
	protected function runZipTest($zipUrl)
	{
		return $this->renderHandlerTest(
			'Core handler — full repo ZIP (the exact sync call)',
			$zipUrl,
			'Identical to what "Run core sync" executes for the download step. The downloaded archive is deleted '
			. 'again — nothing is extracted or installed.',
			120 // longer timeout for a full archive
		);
	}

	/**
	 * Run one direct cURL request and render the result. Uses its OWN cURL
	 * handle (not the core handler), so the SSL-verify setting is exactly
	 * what the label says. Response bodies are never shown — only status
	 * data and the (sanitised) verbose log.
	 *
	 * @param string $title
	 * @param string $url        Absolute https URL (built from validated segments only).
	 * @param bool   $headOnly   TRUE = HEAD request (no body download).
	 * @param bool   $verifySsl
	 * @param string $hint       One-line interpretation help.
	 * @return string
	 */
	protected function renderCurlTest($title, $url, $headOnly, $verifySsl, $hint)
	{
		$verbose = fopen('php://temp', 'w+');

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_NOBODY         => $headOnly,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => $verifySsl,
			CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT        => 20,
			CURLOPT_USERAGENT      => 'e107-githubSyncLite-diagnostics',
			CURLOPT_VERBOSE        => true,
			CURLOPT_STDERR         => $verbose,
		));
		if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS'))
		{
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
			curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
		}

		curl_exec($ch);
		$errno    = curl_errno($ch);
		$error    = curl_error($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$effUrl   = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		rewind($verbose);
		$log = (string) stream_get_contents($verbose, 4000); // first ~4 KB is plenty
		fclose($verbose);

		$rows   = array();
		$rows[] = array('URL', htmlspecialchars($url, ENT_QUOTES, 'utf-8'));
		$rows[] = array('Result', $errno === 0
			? '<span class="label label-success">cURL OK</span> — HTTP ' . $httpCode
			: '<span class="label label-danger">cURL error ' . $errno . '</span> — ' . htmlspecialchars($error, ENT_QUOTES, 'utf-8'));
		if ($effUrl !== '' && $effUrl !== $url)
		{
			$rows[] = array('Effective URL', htmlspecialchars($effUrl, ENT_QUOTES, 'utf-8'));
		}
		$rows[] = array('How to read it', $hint);
		if ($log !== '')
		{
			$rows[] = array('Verbose log (first 4 KB)',
				'<pre style="max-height:200px;overflow:auto;white-space:pre-wrap">'
				. htmlspecialchars($log, ENT_QUOTES, 'utf-8') . '</pre>');
		}

		return '<h4>' . htmlspecialchars($title, ENT_QUOTES, 'utf-8') . '</h4>' . $this->renderRows($rows);
	}

	/**
	 * Run e107::getFile()->getRemoteFile() — the sync's real download path —
	 * against a URL, report the outcome incl. getErrorMessage(), and delete
	 * the temp file again.
	 *
	 * @param string $title
	 * @param string $url
	 * @param string $hint
	 * @param int    $timeout
	 * @return string
	 */
	protected function renderHandlerTest($title, $url, $hint, $timeout = 30)
	{
		$fl        = e107::getFile();
		$localName = 'gsl_diag_' . time() . '.tmp';

		$ok = $fl->getRemoteFile($url, $localName, 'temp', $timeout);

		$size = @file_exists(e_TEMP . $localName) ? (int) @filesize(e_TEMP . $localName) : 0;
		@unlink(e_TEMP . $localName); // always clean up

		$handlerError = method_exists($fl, 'getErrorMessage') ? (string) $fl->getErrorMessage() : '';

		$rows   = array();
		$rows[] = array('URL', htmlspecialchars($url, ENT_QUOTES, 'utf-8'));
		$rows[] = array('Result', $ok
			? '<span class="label label-success">SUCCESS</span> — ' . number_format($size) . ' bytes downloaded (temp file deleted again)'
			: '<span class="label label-danger">FAILED</span> — getRemoteFile() returned false');
		if ($handlerError !== '')
		{
			$rows[] = array('Handler error', htmlspecialchars($handlerError, ENT_QUOTES, 'utf-8'));
		}
		elseif (!$ok)
		{
			$rows[] = array('Handler error',
				'(empty) — the handler reports SSRF-guard refusals via getErrorMessage(), but logs plain cURL '
				. 'errors only to the PHP error log. Check the error log for a line starting with "cURL error [".');
		}
		$rows[] = array('How to read it', $hint);

		return '<h4>' . htmlspecialchars($title, ENT_QUOTES, 'utf-8') . '</h4>' . $this->renderRows($rows);
	}

	// =====================================================================
	// helpers
	// =====================================================================

	/**
	 * @param array $rows  list of [label, html] pairs (label is escaped here,
	 *                     the value side is pre-escaped by the callers)
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
		$text  = '<strong>Diagnostics</strong> inspects why a sync download fails, without touching the sync itself.';
		$text .= '<br><br><strong>Environment</strong> answers "is a CA bundle installed" (curl.cainfo / openssl.cafile).';
		$text .= '<br><strong>DNS + SSRF guard</strong> tests the core handler\'s isUrlSafe() — on some Windows/WAMP '
			. 'stacks dns_get_record() fails and the guard then refuses even public GitHub hosts with the '
			. '"Refused to fetch URL…" message.';
		$text .= '<br><strong>Connection tests</strong> separate DNS problems (cURL error 6), a missing CA bundle '
			. '(cURL error 60) and handler-internal blocks. The heavy button repeats the sync\'s exact ZIP download.';

		return array(
			'caption' => LAN_HELP,
			'text'    => $text,
		);
	}
}


class githubSyncLite_debug_form_ui extends e_admin_form_ui
{
}


new githubSyncLite_adminArea();

require_once(e_ADMIN . 'auth.php');
e107::getAdminUI()->runPage();

require_once(e_ADMIN . 'footer.php');
exit;
