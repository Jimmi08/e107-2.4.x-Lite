# Sync report (round 4) — e107-2.4.x-Lite ← e107inc/e107 master

## Baseline

| | |
|---|---|
| Lite `main` HEAD | `f09c7400f8ea0540c1e3012de26d8313b09e8b1f` (2026-10-23T19:36:44+02:00, "Merge pull request #117 from Jimmi08/sync-upstream-2026-10-23") |
| Upstream `master` (new baseline) | `5f9c8f8d481c36d7b3c4cac3a6e4ca85707a5224` (2026-10-24T19:50:19+02:00) |
| Previous baseline | `4367279f17d4654e3f455f86e677ef1ad12f048e` (2026-10-22T23:57:54+02:00) — report `audit/sync-upstream-report-2026-10-23-1217.md` |
| Branch | `sync-upstream-2026-10-26` (created from `main`) |
| Version note | Tag `2.4.0.6` exists. `eadmin/ver.php` set to `2.4.0.7 (git)` (§7.1). |

Rules: the same as in the previous three rounds; every earlier decision stays valid. Files were processed with a 3-way
merge (base = previous upstream `4367279f1`, ours = Lite, theirs = new upstream `5f9c8f8d4`). The previous baseline is
an ancestor of the new one.

Outside `e107_tests/` upstream changed only four files in this range (`git diff --name-status 4367279f1 5f9c8f8d4`):
`class2.php`, `e107_handlers/e107_class.php`, `e107_handlers/session_handler.php`, `userposts.php` — all modified, no file
added or deleted. Every other file in scope is unchanged upstream, so its Lite state from the previous round stays as it
is. No STOP in this round. No added line carries an `e107_*` directory literal.

Directories without a change get no commit (no empty commits).

### Upstream commit range `4367279f1..5f9c8f8d4` (10 commits)

| Commit | Date | Subject |
|---|---|---|
| `5f9c8f8d4` | 2026-10-24 | Merge pull request #6822 from e107inc/e107help/bbcode-timing-steady |
| `f2179f494` | 2026-10-24 | Merge pull request #6824 from e107inc/e107help/acceptance-failed-step-page |
| `8ee1fe7b1` | 2026-10-24 | Merge pull request #6829 from e107inc/e107help/5900 |
| `bf1b0ce1a` | 2026-10-24 | fix(userposts): set the empty forum result rather than append to nothing |
| `aab3765c5` | 2026-10-23 | test(acceptance): list the cookies with the page a failing step kept |
| `03fce4934` | 2026-10-23 | test(unit): time the parser's speed tests in processor time, in turns |
| `a7867c254` | 2026-10-23 | test(acceptance): keep the page a failing step left the browser on |
| `b71e2136f` | 2026-10-23 | Merge pull request #6818 from e107inc/e107help/6817 |
| `de38664a8` | 2026-10-22 | fix(session): send the session cookie in the one form PHP sends it |
| `7357c6ab0` | 2026-10-22 | refactor(e107): move cookie()'s domain rule into e107::cookieDomain() |

### Commits

| Commit | Scope |
|---|---|
| `431867616` | sync(ehandlers): align with upstream master 5f9c8f8d4 |
| `3dd1657c7` | sync(eadmin): align with upstream master 5f9c8f8d4 |
| `3e08ca20c` | sync(root): align with upstream master 5f9c8f8d4 |
| `90c4411ac` | audit: full report |
| `936fad741` | install.php: take upstream PHP/MySQL minimums, remove marker (instruction after the run) |
| `5afaadfd8` | audit: install.php minimums, PHP minimum search |
| `5b5b9e225` | ecore: clean up comments in Lite news URL override (instruction after the run) |
| `5a52d6dc8` | audit: Lite news URL override comments |
| `61478bece` | ecore: remove unused news override controller copy (instruction after the run) |
| (this commit) | audit: news override controller copy removed |

### STOPs

None.

---

## 1. elanguages/ (← e107_languages/)

- One-sided files: none.
- No upstream change in this range — nothing replaced, nothing merged, no commit.

## 2. ehandlers/ (← e107_handlers/)

- One-sided files: none (the six `search/advanced_*.php` / `search/search_*.php` files stay deleted, §7.3; they are not
  in upstream either).
- **Replaced (unmarked), 1 file:** `session_handler.php` (`de38664a8`) — the session cookie is now sent with
  `session_get_cookie_params()` through `eShims::setcookie()` (new private `sendSessionCookie()`); the domain-wide copy
  written by earlier releases is expired (new private `expireDomainWideSessionCookie()`, also after
  `session_regenerate_id()`); `validateSessionCookie()` renews the cookie only on a request that ran language detection
  (`!e107::getE107('no_lan')`). Byte-identical to upstream after the merge. Dependencies present in Lite:
  `eShims::setcookie()` with the options-array form (`ehandlers/Shims/Internal/SetcookieTrait.php:46`),
  `e107::getE107()` (`ehandlers/e107_class.php:1034`), `e107::cookieDomain()` (this commit).
- **Merged around marker:** `e107_class.php` — one upstream hunk (`7357c6ab0`): new `public static function
  cookieDomain($domain)` after `cookiePath()` (`:6002–6016`). The marker ("no phone-home", `coreUpdateAvailable()`, now
  `:7188`) is untouched; clean 3-way merge. The file differs from upstream only by the mapped directory arrays in
  `overridableDirs()` / `defaultDirs()`, the two marker lines and the indentation of the two lines after them (as before).
- Skipped no-target changes: none.

## 3. eadmin/ (← e107_admin/)

- **One-sided files (decided in §7):** upstream-only `includes/{categories,classis,combo,compact,tabbed}.php` not added;
  Lite-only `includes/dashboard.php` kept (not synced).
- No upstream change in this range.
- **§7.1:** `ver.php` — `$e107info['e107_version'] = "2.4.0.7 (git)";` (was `2.4.0.6 (git)`), marker kept, nothing else
  taken (upstream did not change the file). Commit `3dd1657c7`.

## 4. eweb/ (← e107_web/)

- One-sided files: none (`js/chap_script.js` stays deleted, §7.3; not in upstream either).
- No upstream change in this range — no commit.

## 5. Root

- **One-sided files:** all decided in §7 — upstream-only `.editorconfig`, `banner.php`, `composer.json`, `composer.lock`,
  `contact.php`, `download.php`, `email.php`, `gsitemap.php`, `online.php`, `print.php`, `request.php`, `search.php`,
  `submitnews.php`, `top.php`, `unsubscribe.php`, `upload.php` not added; Lite-only `README.sk.md`, `README_links.md`,
  `REGISTRY-RULES.md` kept. `README.md`, `.gitignore`, `e107.robots.txt` kept as in Lite (unchanged upstream).
  `LICENSE`: unchanged upstream. `.gitmodules`: in neither tree.
- **Merged (unmarked, directory mapping only):** `class2.php` (`7357c6ab0`) — `cookie()` now takes the domain from
  `e107::cookieDomain($domain)`; the commented old rule and the inline rule are removed (same logic, moved). The two
  mapped defaults (`ehandlers/`, `eplugins/`, `:163`, `:168`) kept.
- **Replaced (unmarked):** `userposts.php` (`bf1b0ce1a`) — `$ftext .=` → `$ftext =` in the `forums` action when the
  query fails. Byte-identical to upstream. This removes the PHP 8 warning "Undefined variable $ftext" described in the
  previous report (forum plugin not installed).
- **§7.2 — `install.php`:** Lite version kept, nothing taken. Upstream changes in `install.php` since the previous
  baseline: **none** (the file is unchanged in `4367279f1..5f9c8f8d4`).
- Commit `3e08ca20c`.
- **Instruction after the run — `install.php` minimums (`936fad741`):** the marker
  `// LITE MODIFICATION: Lite minimum PHP 7.4 / MySQL 5.1.2` (was `:31`) removed; `MIN_PHP_VERSION` `'7.4'` → `'5.6'`,
  `MIN_MYSQL_VERSION` `'5.1.2'` → `'4.1.2'` (`:31–32`), the upstream values. The other five markers in `install.php`
  (`:67`, `:1677`, `:2019`, `:2054`, `:3108`) kept. Nothing else in the file changed. `php -l` clean.

### Search: other Lite-specific PHP minimum checks / PHP 7.4 workarounds

Instruction after the run: report only, nothing changed. Searched all tracked files outside `audit/` and `*.md`, without
the third-party `ehandlers/vendor/` (Composer packages, not Lite or e107 code) and minified assets, for `PHP_VERSION`,
`PHP_VERSION_ID`, `PHP_MAJOR_VERSION` / `PHP_MINOR_VERSION`, `phpversion()`, `version_compare` against a PHP version,
`70400`, `7.4`, `'7.x'` / `"7.x"`, `min_php`, `minimum PHP`, `requires PHP`, `PHP 7` / `PHP7` in comments, and the XML
files for a PHP minimum. Each hit below was read in context. "Upstream" = the same line in the mapped upstream file at
`5f9c8f8d4`.

**Lite-only (not in upstream) — for decision:**

| file:line | what it does | upstream |
|---|---|---|
| `eplugins/githubSyncLite/admin/admin_sync.php:248–252` | `runCoreSync()`: `if (version_compare(PHP_VERSION, '7.4', '<'))` → error "GitHub Sync Lite requires PHP 7.4 or newer" and return. A plugin-level guard, not a site minimum. The plugin code uses `??` (PHP 7.0+) in `admin_config.php`, `admin_debug.php`, `includes/github_sync_engine.php`, `includes/plugin_list.php`; no typed properties, arrow functions, `??=`, scalar type hints or return types found, so nothing found needs 7.4 specifically (not verified by running on PHP 7.0–7.3). | plugin is Lite-only |
| `eplugins/githubSyncLite/admin/admin_sync.php:504` | Help text: "Main admin only. Requires PHP 7.4 or newer." | plugin is Lite-only |
| `ecore/override/controllers/news/sef_url.php:16` | Doc-comment "PHP 7.4 safe." No version check. Removed later by instruction (§6). | file is Lite-only |
| `ecore/override/url/news/sef_url.php:18` | Doc-comment "PHP 7.4 safe." No version check. Note: the same doc-comment started with `DRAFT - NOT TO BE COMMITTED` (`:3`). Both comments removed later by instruction (§6). | file is Lite-only |

**Same code in upstream (not Lite-specific):**

| file:line | what it does |
|---|---|
| `class2.php:62–67` | Exits with "PHP 5.6 or higher is required" below PHP 5.6. |
| `class2.php:2056` | `version_compare(PHP_VERSION, '8.4', '<')` — adds the `E_STRICT` label only below PHP 8.4. |
| `class2.php:2164`, `:2191` | Comments on PHP 7 / 8 error levels and the deprecated `$context`. |
| `eadmin/admin.php:758` | `PHP_MAJOR_VERSION < 8` → "outdated version of PHP" notice on the dashboard. |
| `eadmin/db.php:421–426` | Hard-coded `$min_php_version = '5.6'` → warning before the GitHub sync. |
| `eadmin/theme.php:595–601` | `PHP_MAJOR_VERSION < 6` → skip the PHP 7 compatibility check of a theme's `theme.php`. |
| `ehandlers/core_functions.php:590` | `PHP_MAJOR_VERSION > 5` → `unserialize()` with `allowed_classes => false`. |
| `ehandlers/magpie_rss.php:491` | `substr(phpversion(), 0, 1) == 5` → PHP 5 XML parser path. |
| `ehandlers/plugin_class.php:3855` | Plugin dependency check: `min_version` of a `php` dependency compared with `phpversion()`. |
| `ehandlers/Reflection/ReflectionMethod.php:36`, `ReflectionProperty.php:28` | `PHP_VERSION_ID < 80100` → `setAccessible(true)` only below 8.1. |
| `ehandlers/Shims/Internal/SetcookieTrait.php:53`, `ehandlers/session_handler.php:1114` | `PHP_VERSION_ID >= 70300` → options-array form of `setcookie()` / `session_set_cookie_params()`. |
| `ehandlers/Shims/Internal/StrtolowerTrait.php:28`, `StrtoupperTrait.php:28` | `PHP_VERSION_ID >= 80200` → native ASCII-only case folding. |
| `ehandlers/Shims/Internal/UsortTrait.php:35` | `PHP_VERSION_ID >= 80000` → native stable sort. |
| `ehandlers/Security/SealedToken.php:51–56`, `Security/Random.php:28`, `:42`, `Security/Hkdf.php:39` | Comments on PHP 7.x behaviour (the `SealedToken` comment says "Current e107 requires PHP 8.0"). |
| `ehandlers/e_db_pdo_class.php:1261` | Comment on PHP 7.3.22 / 7.4.10 PDO error codes (bug #64705); not a minimum. |
| `ehandlers/php_compatibility_handler.php:9` | Comment "e107 requires PHP >= 5". |
| `ehandlers/e_parse_class.php:1197`, `model_class.php:1145`, `shortcode_handler.php:1064`, `:1374`, `:1383`, `user_model.php:3444`, `xml_class.php:810`, `event_class.php:260`, `e_search_fulltext_indexer_class.php:158` | "PHP7 fix" / "PHP 7+" comments. |
| `elanguages/English/lan_installer.php:35` | `LANINS_019` text uses `MIN_PHP_VERSION`. |
| `install.php:228–230`, `:1442` | The installer's checks against `MIN_PHP_VERSION` (now `5.6`). |

Not version checks (matched the text only): `ehandlers/bounce_handler.php:1093–1115` (SMTP status codes `7.0`–`7.7`),
`ehandlers/file_class.php:2721–2722`, `:3955` (`.php7` extension), `ehandlers/cron_class.php`, `eadmin/cron.php:535`,
`ecore/shortcodes/batch/admin_shortcodes.php:1425`, `eplugins/githubSyncLite/admin/admin_debug.php:140`, `eadmin/update_routines.php:2439`,
`install.php:2088` (print or send the running PHP version).

No other Lite-specific PHP minimum check was found.

## 6. ecore/ (← e107_core/)

- **One-sided files decided in §7:** Lite-only `override/url/news/sef_url.php`,
  `override/url/usersettings/{index.html,sef_url.php,url.php}`, `templates/bootstrap5/{login,membersonly,signup,user}_template.php`
  kept; upstream-only `templates/bootstrap4/user_template.php` not ported; `shortcodes/batch/submitnews_shortcodes.php`,
  `templates/submitnews_template.php`, `shortcodes/single/{news_categories,news_category,newsfile,newsimage}.sc`,
  `templates/legacy/*` (5 files) pending, not added. No other one-sided file.
- No upstream change in this range (`dispatch.php`, `routes.php`, `templates/logout_template.php` included) — no commit.
- **Lite-only files — comment clean-up (instruction after the run, `5b5b9e225`).** `override/url/news/sef_url.php` and
  `override/controllers/news/sef_url.php` are a deliberate Lite feature (custom news URL system via override, upstream
  code untouched). Only doc-comment lines removed; no code changed (`php -w` output before and after identical for both
  files); `php -l` clean on both. Removed (line numbers before the change):

  | file:line | removed text |
  |---|---|
  | `ecore/override/url/news/sef_url.php:3–4` | ` * DRAFT - NOT TO BE COMMITTED` and the empty ` *` line after it |
  | `ecore/override/url/news/sef_url.php:17–18` | the empty ` *` line and ` * PHP 7.4 safe.` |
  | `ecore/override/controllers/news/sef_url.php:15–16` | the empty ` *` line and ` * PHP 7.4 safe.` |

  The rest of each header (target path, description, stored pref value, class name) is kept; neither file had a
  license or author line. The empty ` *` separators went with the removed text so the comments end without a blank line.
  After the change the two files are byte-identical (same blob `d1ffbbde0`).
- **Lite-only files — `override/controllers/news/sef_url.php` deleted (instruction after the run, `61478bece`).**
  Reason: the file is a copy of the `eUrlConfig` class `override_news_sef_url`; that class is loaded only from
  `ecore/override/url/news/`, and the controllers path expects a class `override_<module>_<controller>_controller`, so
  the copy is never used. `ecore/override/url/news/sef_url.php` unchanged.

  Checked before deleting (code read in `ehandlers/application.php`, not only grep):
  - URL configs: `eDispatcher::getConfigObject()` (`:937–962`) builds the class name with `getConfigClassName()`
    (`:913–928`, `override_news_sef_url` for `override/sef`) and includes `getConfigPath()` (`:652–688`), which for
    `override` is always `e_CORE.'override/url/<module>/<custom>_url.php'`. The admin URL screen (`eadmin/eurl.php` →
    `eRouter::adminBuildLocations()`, `:1501…`) also tests only `getConfigPath()` (`:1524`, `:1554`, `:1588`).
  - Controllers: `isDispatchableModule()` (`:834–867`, called from `isDispatchable()` / `getController()`) includes
    `getControllerPath()` = `e_CORE.'override/controllers/<module>/<controller>.php'` (`:788–793`, `:757–775`) and
    accepts only the class `getControllerClass()` = `override_<module>_<controller>_controller` (`:803–808`). The copy
    defines `override_news_sef_url`, so it can never be returned as a controller.
  - Edge case (read, not run): if a request resolved to module `news`, controller `sef_url` with the override location,
    `isDispatchableModule()` would `include_once` this copy (it is readable and `override_news_sef_url_controller` does
    not exist). With the URL config already loaded from `override/url/news/sef_url.php`, that include would declare
    `override_news_sef_url` a second time — a fatal error. Whether any URL resolves to that controller was not
    verified. The deletion removes this path.
  - `eRouter::adminReadModules()` (`:1310…`) lists the folders of `override/controllers/` (`get_dirs()`) as override
    modules; `news` stays listed through `override/url/news/` (same method, "merge with configs"), so the module list
    does not change.
  - No other reference: `git grep` for `override/controllers`, `controllers/news`, `override_news_sef_url` outside
    `audit/` finds only the methods above and the class in `override/url/news/sef_url.php`.

  `ecore/override/controllers/news/` is now empty and gone from the tree; `ecore/override/controllers/` stays (it still
  holds `index.html`, as upstream).

## 7. ethemes/ (← e107_themes/)

- `_blank`, `voux`: skipped (§7.5). `backend` (Lite-only): not synced. `ethemes/index.html`: kept.
- `bootstrap3`, `bootstrap5`: no upstream change in this range. File lists identical to upstream; the only content
  differences are the two mapped `href=&quot;eadmin/admin.php&quot;` lines in `bootstrap3/install/install.xml` and
  `bootstrap5/install/install.xml` (as before). **Changed:** none. **Deleted:** none. **Added:** none. No commit.
- §7.7 Lite-only fixes checked in `main` before the run, all as described: `eadmin/auth.php:30` forces the Lite admin
  theme (marker); `ethemes/backend/theme_shortcodes.php:274–276` logout link carries `e-token` (marker);
  `ecore/shortcodes/batch/admin_shortcodes.php:2285–2286` external links behind `if(false /* getperms('0') */)`;
  `eadmin/includes/infopanel.php` feeds (`:103–122`) and `renderFeedTabs()` (`:205–218`) under `//LITE-SKIP`;
  `eplugins/rss_menu/rss.php:532`, `:574`, `:722` markers; `eadmin/includes/dashboard.php` and
  `ethemes/backend/templates/dashboard_template.php` present. None of these files was touched in this round.

## 8. eplugins/ (← e107_plugins/)

### Plugins in both Lite and upstream

| Plugin | One-sided files | Result |
|---|---|---|
| chatbox_menu | none | No upstream change. Identical to upstream. |
| download | none | No upstream change. Existing differences: `e_url.php`, `request.php` (marked). |
| featurebox | none | No upstream change. Identical to upstream. |
| forum | none | No upstream change. Existing differences (earlier rounds, Lite path text in user-facing strings): `forum_update.php` (`efiles/public`), `languages/English/English_global.php` (`eplugins/forum/` in `FORUM_LAN_URL_DEFAULT_DESCR`). |
| login_menu | none | No upstream change. Existing differences: `login_menu_shortcodes.php`, `login_menu_template.php` (marked). |
| navigation | none | Identical to upstream. |
| news | none | Identical to upstream. |
| page | none | Identical to upstream. |
| pm | none | Identical to upstream. |
| rss_menu | none | No upstream change. Existing difference: `rss.php` (the three markers, §7.6). |
| siteinfo | none | Identical to upstream. |
| tinymce4 | none | No upstream change. Existing differences: `wysiwyg.php`, `wysiwyg_class.php` (earlier mapping). |
| user | none | Identical to upstream. |

No commit in `eplugins/`.

### forum: dependencies (changes since the previous round only)

- No change in the plugin. Outside it: the root `userposts.php` `forums` action no longer raises the
  "Undefined variable $ftext" warning when the forum tables are missing (§5); the rest of that analysis stands.

### pm: dependencies (changes since the previous round only)

- None.

### chatbox_menu: dependencies (changes since the previous round only)

- None.

### Skipped (report only)

- `githubSyncLite` (Lite-only) — skipped by §7.
- Upstream-only plugins skipped by §7: `_blank`, `admin_menu`, `alt_auth`, `banner`, `blogcalendar_menu`, `comment_menu`,
  `contact`, `faqs`, `gallery`, `gsitemap`, `hero`, `import`, `linkwords`, `list_new`, `newforumposts_main`, `newsfeed`,
  `newsletter`, `online`, `poll`, `search_menu`, `signin`, `social`, `tagcloud`. None of them changed upstream in this
  range.

---

## Markers

Counted with `git grep` over the tracked `*.php`, `*.js`, `*.css` files (combined) and `*.xml` (separately), lines.

| Marker | before (php/js/css) | before (xml) | after (php/js/css) | after (xml) |
|---|---|---|---|---|
| `LITE MODIFICATION` | 55 | 1 | 54 | 1 |
| `LITE FEATURE` | 3 | 0 | 3 | 0 |
| `LITE-SKIP` | 86 | 0 | 86 | 0 |

- **Removed by instruction** — 1 php: `install.php:31` (`// LITE MODIFICATION: Lite minimum PHP 7.4 / MySQL 5.1.2`),
  commit `936fad741`. 55 − 1 = 54. Added: none. (The `e107_class.php` marker moved from line 7172 to 7188 because of the 16 lines added
  above it.)

## Checks

- `php -l` (PHP 8.3.6) on all changed PHP files (`class2.php`, `eadmin/ver.php`, `ehandlers/e107_class.php`,
  `ehandlers/session_handler.php`, `userposts.php`, `install.php`, `ecore/override/url/news/sef_url.php`): no errors
  (`ecore/override/controllers/news/sef_url.php` was checked before it was deleted).
- `git diff --name-only main..HEAD`: `class2.php`, `eadmin/ver.php`, `ehandlers/e107_class.php`,
  `ehandlers/session_handler.php`, `install.php`, `userposts.php`, `ecore/override/url/news/sef_url.php`,
  `ecore/override/controllers/news/sef_url.php` (deleted) — plus `audit/` (this report).
- `git status` clean, no `.rej` / `.orig`.
- Branch `sync-upstream-2026-10-26` pushed; no merge, no PR, nothing pushed to `main`.
