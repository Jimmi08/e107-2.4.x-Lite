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
| (this commit) | audit: full report |

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

## 6. ecore/ (← e107_core/)

- **One-sided files decided in §7:** Lite-only `override/controllers/news/sef_url.php`, `override/url/news/sef_url.php`,
  `override/url/usersettings/{index.html,sef_url.php,url.php}`, `templates/bootstrap5/{login,membersonly,signup,user}_template.php`
  kept; upstream-only `templates/bootstrap4/user_template.php` not ported; `shortcodes/batch/submitnews_shortcodes.php`,
  `templates/submitnews_template.php`, `shortcodes/single/{news_categories,news_category,newsfile,newsimage}.sc`,
  `templates/legacy/*` (5 files) pending, not added. No other one-sided file.
- No upstream change in this range (`dispatch.php`, `routes.php`, `templates/logout_template.php` included) — no commit.

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
| `LITE MODIFICATION` | 55 | 1 | 55 | 1 |
| `LITE FEATURE` | 3 | 0 | 3 | 0 |
| `LITE-SKIP` | 86 | 0 | 86 | 0 |

- Removed: none. Added: none. (The `e107_class.php` marker moved from line 7172 to 7188 because of the 16 lines added
  above it.)

## Checks

- `php -l` (PHP 8.3.6) on all changed PHP files (`class2.php`, `eadmin/ver.php`, `ehandlers/e107_class.php`,
  `ehandlers/session_handler.php`, `userposts.php`): no errors.
- `git diff --name-only main..HEAD`: `class2.php`, `eadmin/ver.php`, `ehandlers/e107_class.php`,
  `ehandlers/session_handler.php`, `userposts.php` — plus `audit/` (this report).
- `git status` clean, no `.rej` / `.orig`.
- Branch `sync-upstream-2026-10-26` pushed; no merge, no PR, nothing pushed to `main`.
