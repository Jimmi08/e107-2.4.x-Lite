# Sync report (round 2) — e107-2.4.x-Lite ← e107inc/e107 master

## Baseline

| | |
|---|---|
| Lite `main` HEAD | `912fb5957bf84d8513fae8fce6085b54f746bc74` (2026-10-09T07:25:24+02:00, "upstream update 2026-09-26") |
| Upstream `master` (new baseline) | `7ce7a121c7a72e5abf0cf02802ca39163649248d` (2026-10-05T22:18:45-05:00) |
| Previous baseline | `3cd96ea259bd90b64e775bf6918c1174efbca941` (2026-09-25) — report `audit/sync-upstream-report-2026-09-27-1053.md` |
| Branch | `sync-upstream-2026-10-09` (created from `main`) |
| Tag note | `2.4.0.5` is the current release tag. `eadmin/ver.php` now says `2.4.0.6 (git)` (§7 decision). |

Rules: the same as in the previous round; every decision of the previous report stays valid (§7 of the task).
Directory mapping (§2) applied to code only; comments keep the upstream `e107_*` form; LAN texts keep the upstream
form as before (no mapped form existed in `elanguages/`). Files were processed with a 3-way merge
(base = previous upstream, ours = Lite, theirs = new upstream); every unmarked file in scope was byte-identical to
the previous upstream version (or differed only by the directory mapping), so a clean merge equals "replace".

### Upstream commit range `3cd96ea25..7ce7a121c` (145 commits)

| Commit | Date | Subject |
|---|---|---|
| `7ce7a121c` | 2026-10-05 | Merge pull request #6687 from e107inc/e107help/harness-submodule-pin |
| `e009cc8df` | 2026-10-05 | Merge pull request #6686 from e107inc/e107help/harness-sql-stdin |
| `85a0a2847` | 2026-10-05 | Merge pull request #6685 from e107inc/e107help/webdriver-datepicker-flake |
| `3dfdcd35e` | 2026-10-06 | test(webdriver): wait for the date picker before judging its calendar |
| `57b81358a` | 2026-10-05 | Merge pull request #6680 from e107inc/e107help/cheap-test-hashes |
| `e2fca1add` | 2026-10-06 | fix(tests): leave the cPanel client submodule out of harness and CI runs |
| `62207d57c` | 2026-10-06 | fix(harness): stop execs that read no input swallowing the caller's stdin |
| `d17de6ce5` | 2026-10-06 | test: seed the suites' accounts with the cheapest bcrypt hash |
| `4fa13c102` | 2026-10-04 | Merge pull request #6673 from e107inc/e107help/6665 |
| `a2c8cf764` | 2026-10-04 | Merge pull request #6675 from e107inc/e107help/6672 |
| `a3cc5df70` | 2026-10-04 | ci(sqli): Remove `OPTIMIZE TABLE` from static gate |
| `de4ec89c7` | 2026-10-04 | Merge pull request #6668 from e107inc/e107help/6664 |
| `cf351a661` | 2026-10-04 | Merge pull request #6666 from e107inc/e107help/6663 |
| `287117322` | 2026-10-04 | fix(database): list only the tables under the connection's own prefix |
| `200a1b95e` | 2026-10-04 | fix(admin): make Optimize SQL database optimise the site's own tables |
| `c878f148a` | 2026-10-04 | test: render an entry script in a booted child through one shared helper |
| `842831112` | 2026-10-04 | fix(forum): stop counter decrements at zero without error 1690 |
| `240317b59` | 2026-10-04 | feat(database): add a decrement that stops at zero |
| `96fa07cdb` | 2026-10-04 | fix(database): connect on the port e107_config.php names |
| `97d542126` | 2026-10-04 | fix(database): optimise the table optimizeTable() is given, not its lan_ copy |
| `814e4be17` | 2026-10-04 | Merge pull request #6661 from e107inc/e107help/6656 |
| `631cc7356` | 2026-10-04 | fix(bootstrap5): load Font Awesome as CSS, not as the SVG script |
| `9551f88c6` | 2026-10-03 | Merge pull request #6654 from e107inc/e107help/6653 |
| `08e306e28` | 2026-10-03 | Merge pull request #6659 from e107inc/e107help/6658 |
| `7c87f366d` | 2026-10-03 | fix(shortcodes): create a batch's value store on first use |
| `4b59c0708` | 2026-10-02 | fix(login): discontinue CHAP login |
| `86fca93c1` | 2026-09-30 | Merge pull request #6499 from e107inc/e107help/6498 |
| `a4887165d` | 2026-09-30 | Merge pull request #6458 from e107inc/e107help/5880 |
| `4fff75317` | 2026-09-30 | Merge pull request #6588 from e107inc/e107help/6586 |
| `b4e5d3745` | 2026-09-30 | Merge pull request #6620 from e107inc/e107help/6527 |
| `57f0fa6fc` | 2026-09-30 | Merge pull request #6613 from e107inc/e107help/6488 |
| `739ab8f99` | 2026-09-30 | Merge pull request #6562 from e107inc/e107help/6414 |
| `aa1b5ef8a` | 2026-09-30 | Merge pull request #6515 from e107inc/e107help/6502 |
| `c58e8e6aa` | 2026-09-30 | Merge pull request #6650 from e107inc/e107help/6124-coppa-wait |
| `f70107574` | 2026-09-28 | feat(admin): merge a stray site folder into the site's own from the Multi-Site page |
| `0f224955c` | 2026-09-28 | fix(bootstrap): derive the site hash through the MySQL config accessor |
| `ff0fa4936` | 2026-09-28 | fix(rss): escape an item's author before it lands in the feed |
| `51508f5a4` | 2026-09-28 | fix(rss): name the author of a comment in the comments feed |
| `4b10df83f` | 2026-09-28 | refactor(rss): declare the comments feed from rss_menu's own e_rss.php |
| `ba3c6d392` | 2026-09-28 | fix(download): hand the batch its query state before the breadcrumb |
| `293b4cc3b` | 2026-09-28 | refactor(tests): boot a plugin's own page in a child the one way |
| `4615d57a7` | 2026-09-28 | fix(users): let a user rank delete report its result |
| `3eb7555ae` | 2026-09-28 | fix(users): delete a user rank from the button on its row |
| `a74bde83f` | 2026-09-28 | fix(social): keep LinkedInOpenID's credentials apart from LinkedIn's |
| `90ca9414b` | 2026-09-28 | fix(rector): hoist a literal passed to a by-reference parameter |
| `b17902de0` | 2026-09-28 | chore(search): delete six legacy search handlers nothing can load |
| `a57c6a8ae` | 2026-09-28 | fix(faqs): guard the admin page's unseeded preference reads |
| `cf9e6221f` | 2026-09-28 | fix(faqs): drop the abandoned controller folder that hijacks its own URLs |
| `0ebdbd5f6` | 2026-09-28 | test(webdriver): wait for the signup form after the COPPA gate |
| `c72721cae` | 2026-09-27 | Merge pull request #6492 from e107inc/e107help/5744 |
| `1024c8bc6` | 2026-09-27 | Merge pull request #6544 from e107inc/e107help/6391 |
| `3b2939658` | 2026-09-27 | Merge pull request #6564 from e107inc/e107help/6478 |
| `c235ca6f0` | 2026-09-27 | Merge pull request #6553 from e107inc/e107help/6489 |
| `83c598940` | 2026-09-27 | Merge pull request #6429 from e107inc/e107help/6336 |
| `fff027ba4` | 2026-09-27 | Merge pull request #6510 from e107inc/e107help/6497 |
| `4c974fc7d` | 2026-09-27 | Merge pull request #6610 from e107inc/e107help/6491 |
| `fe0a7ae5f` | 2026-09-27 | Merge pull request #6608 from e107inc/e107help/6338 |
| `2467b8ec7` | 2026-09-27 | Merge pull request #6572 from e107inc/e107help/6396 |
| `24ad7b2bd` | 2026-09-27 | Merge pull request #6551 from e107inc/e107help/6408 |
| `e38f81bae` | 2026-09-27 | Merge pull request #6532 from e107inc/e107help/6403 |
| `84057a1f9` | 2026-09-27 | Merge pull request #6369 from e107inc/e107help/6358 |
| `5610c39f8` | 2026-09-27 | Merge pull request #6380 from e107inc/e107help/6370 |
| `d73268be1` | 2026-09-27 | Merge pull request #6576 from e107inc/e107help/6522 |
| `a158adf4e` | 2026-09-27 | Merge pull request #6457 from e107inc/e107help/6331 |
| `02e47b0ae` | 2026-09-27 | Merge pull request #6349 from e107inc/e107help/6144 |
| `528d899cb` | 2026-09-27 | Merge pull request #6351 from e107inc/e107help/6143 |
| `e60303507` | 2026-09-27 | Merge pull request #6528 from e107inc/e107help/6127 |
| `4a959b953` | 2026-09-27 | Merge pull request #6529 from e107inc/e107help/6117 |
| `d9281afbd` | 2026-09-27 | Merge pull request #6411 from e107inc/e107help/6244 |
| `8f87a1aa7` | 2026-09-27 | Merge pull request #5559 from rica-carv/pm_nextprev |
| `c27d489d7` | 2026-09-27 | Merge pull request #6554 from e107inc/e107help/6523 |
| `97fbe0cad` | 2026-09-27 | Merge pull request #6636 from e107inc/e107help/6420 |
| `17e92caa2` | 2026-09-27 | Merge pull request #6616 from e107inc/e107help/6490 |
| `0d08a2816` | 2026-09-27 | Merge pull request #6568 from e107inc/e107help/6177 |
| `c7c65464e` | 2026-09-27 | Merge pull request #6536 from e107inc/e107help/6526 |
| `914833b59` | 2026-09-27 | Merge pull request #6426 from e107inc/e107help/6274 |
| `32df463ea` | 2026-09-27 | Merge pull request #6432 from e107inc/e107help/6283 |
| `78ca2ad2b` | 2026-09-27 | Merge pull request #6533 from e107inc/e107help/6124 |
| `a0c193f24` | 2026-09-27 | Merge pull request #6626 from e107inc/e107help/6524 |
| `b01cb57df` | 2026-09-27 | Merge pull request #6624 from e107inc/e107help/6445 |
| `6a98bc872` | 2026-09-27 | Merge pull request #6627 |
| `b6dc581b7` | 2026-09-27 | Merge pull request #6603 from tgtje/workingv5 |
| `63e5d7e3f` | 2026-09-27 | fix(pm): index private messages by sender |
| `b6b0a640c` | 2026-09-27 | fix(prefs): head the Email & Contact Info tab with its own name |
| `b952b9390` | 2026-09-27 | test(file): accept a resolver timeout in the pinned-address control |
| `cf17c2620` | 2026-09-27 | fix: pass an empty numeric prefix to http_build_query() |
| `8248e2bec` | 2026-09-27 | fix(comment_menu): stop the save warning when the news-title box is unticked |
| `e54642838` | 2026-09-27 | fix(comment_menu): keep the other languages' captions when one is saved |
| `b4ebea751` | 2026-09-27 | fix(parser): read a missing user_name quietly in toAvatar() |
| `7c6902af9` | 2026-09-27 | fix(comments): stop the new-comment icon fataling for guests on PHP 8 |
| `58fb53032` | 2026-09-27 | fix(core): keep multi-row config objects apart in getPlugConfig() and getThemeConfig() |
| `53e5c4e55` | 2026-09-27 | fix(prefs): key a multi-row plugin or theme preference's cache on its own row |
| `164a64a06` | 2026-09-27 | fix(comments): indent every nested reply one level below its parent |
| `ba6a5e27e` | 2026-09-25 | text to LAN |
| `34b1351ae` | 2026-09-25 | New LAN's |
| `3e451388d` | 2026-09-23 | fix(admin): let the Site URL be a bare path again, and say what that costs |
| `cc71df77e` | 2026-09-21 | fix(banlist): serialise replace-imports so one cannot delete another's rows |
| `abc219ffa` | 2026-09-20 | fix(online): supply the online state where goOnline() is skipped |
| `9526da184` | 2026-09-20 | fix(online): give the Who's Online tokens the figures their names say |
| `6bdc15f80` | 2026-09-20 | fix(newsletter): define $action and guard the archive's query segments |
| `5bf4ab926` | 2026-09-20 | fix(download): Use `varset()` for optional category template keys |
| `9d3b7695f` | 2026-09-20 | test(scripts): load every bundled plugin entry script in a process of its own |
| `58c9fd62f` | 2026-09-20 | fix(theme): refuse a style submission naming another theme |
| `047147ee4` | 2026-09-20 | chore(db): drop the premise that a counted select must stay unbound |
| `536176117` | 2026-09-20 | fix(search): bind the keyword the MySQL sort method matches against |
| `20e449375` | 2026-09-20 | fix(db): read FOUND_ROWS after a bound SQL_CALC_FOUND_ROWS select too |
| `70b4f4851` | 2026-09-20 | fix(admin): draw the field that cancelled an admin Save |
| `60da14186` | 2026-09-20 | test(rector): run the shipping config over the shapes the hand patches fixed |
| `a08d056cc` | 2026-09-20 | feat(rector): alias an import whose short name its own namespace has taken |
| `bf67c599a` | 2026-09-20 | feat(rector): teach the downgrade what the PHP 5.6 floor has, not just what it parses |
| `b03ff56b3` | 2026-09-20 | fix(page): answer a book or chapter that does not resolve with a real 404 |
| `fc2e36fa7` | 2026-09-20 | fix(css): draw the marker core emits for a required field |
| `6c90792a2` | 2026-09-20 | fix(admin): read the admin navigation's phrases through defset() |
| `c00fb7755` | 2026-09-20 | fix(admin-ui): drop a field whose writeParms offer no option list |
| `a550af39e` | 2026-09-20 | fix(xml): name the reason a feed fetch or parse failed |
| `1662b2490` | 2026-09-20 | fix(user): label TMP as Theme Manager |
| `81fa05026` | 2026-09-19 | fix(admin): space the remaining admin labels from their values |
| `efb320b3e` | 2026-09-19 | fix(mailout): space the mail labels from their values |
| `af7732ef7` | 2026-09-19 | fix(update): space the update log messages from their values |
| `f988811ae` | 2026-09-19 | fix(plugin): space the install and dependency messages from their values |
| `93ad76200` | 2026-09-19 | fix(mail): space the email subjects from the site name |
| `a4a13aae6` | 2026-09-19 | fix(comment): space the reply page title from its subject |
| `fbbeea9a2` | 2026-09-19 | fix(search): space the flood message around its interval |
| `99ff61408` | 2026-09-19 | fix(online): space the Who's Online labels from their counts |
| `370668686` | 2026-09-18 | test(acceptance): stop counting a linked stylesheet as an inline one |
| `ea5e1f2ce` | 2026-09-18 | test(unit): put back the parser settings a test found, not a guess |
| `13cc97123` | 2026-09-16 | fix(download): keep a resolved file name out of the mirror branch |
| `078fd26af` | 2026-09-16 | fix(download): read the author fields one way in every shortcode |
| `8ef915854` | 2026-09-16 | fix(rss): build the download feed's links without an undefined variable |
| `ef2671329` | 2026-09-14 | ci(plugins): require a bundled plugin's version to rise once per release cycle |
| `04543cd3f` | 2026-09-14 | fix(chatbox): count only the posts the chat page will show |
| `811e04bc6` | 2026-09-14 | fix(admin): render the navbar update notice and bell closed |
| `b41eb0885` | 2026-09-14 | fix(theme): drop the menu perm markers dispatch never reads |
| `c204d7d11` | 2026-09-13 | fix(admin): restrict Admin History to main administrators |
| `34d568d25` | 2026-09-13 | fix(chatbox): read the page offset the chat page links carry |
| `b66a939c3` | 2026-09-13 | fix(tests): restore the site logo the siteinfo shortcode test changes |
| `8113abd99` | 2026-09-11 | fix(core): percent-encode request URLs before they reach a shortcode |
| `9b53537d0` | 2026-09-06 | Merge branch 'e107inc:master' into pm_nextprev |
| `a6707c4a2` | 2026-08-10 | Merge branch 'e107inc:master' into pm_nextprev |
| `5014e856b` | 2026-08-05 | fix(pm): resolve the parser and the tmpl_prefix key in sc_pm_nextprev |
| `34c81abbe` | 2026-08-04 | Merge branch 'e107inc:master' into pm_nextprev |
| `53d1fc8cc` | 2026-06-10 | Merge branch 'e107inc:master' into pm_nextprev |
| `a118d8d54` | 2026-05-19 | Merge branch 'e107inc:master' into pm_nextprev |
| `824b5fbd0` | 2026-03-07 | Merge branch 'e107inc:master' into pm_nextprev |
| `968044b1a` | 2025-11-08 | Enhance sc_pm_nextprev with new parameter handlingBring PM_NEXTPREV shortcode to v2.3.3 |

### Commits

| Commit | Scope |
|---|---|
| `7563eed99` | sync(elanguages) |
| `c32b1d6d9` | sync(ehandlers) |
| `d8ee0e5df` | sync(eadmin) (includes `ver.php` → `2.4.0.6 (git)`) |
| `64e845282` | sync(eweb) |
| `6de195279` | sync(root) |
| `d31b57597` | sync(ecore) |
| `161a2e858` | sync(ethemes) |
| `2fad1466b` | sync(eplugins/download) |
| `61a0a4541` | sync(eplugins/forum) |
| `f48580977` | sync(eplugins/login_menu) |
| `3e5c368d9` | sync(eplugins/pm) |
| `03af147da` | sync(eplugins/rss) |
| `0ff03efc3` | sync(eplugins/user) |
| (this commit) | audit: this report |

### Open STOPs (waiting for instruction)

The run is autonomous, so each STOP below was left untouched (the one-sided file was neither added nor deleted; the
colliding hunk was not applied) and everything else in the directory was processed and committed. A resolution will be
a new commit. Details are in the directory sections.

| # | Where | What |
|---|---|---|
| 1 | `ehandlers/` | upstream-only `Storage/SiteFolder.php`, `Storage/SiteFolderMerge.php`, `Storage/SiteFolderScan.php`; Lite-only `search/advanced_{news,pages,user}.php`, `search/search_{news,pages,user}.php` |
| 2 | `eweb/` | Lite-only `js/chap_script.js` |
| 3 | root `page.php` | upstream `b03ff56b3` rewrites the `if(!$pageRow)` 404 block that the Lite marker "fix for 404 page" protects |
| 4 | `eplugins/download/request.php` | upstream `13cc97123` changes the mirror-branch condition that the Lite marker at line 110 protects |
| 5 | `eplugins/rss/` | upstream-only `e_rss.php` (comments feed moved out of `rss.php`) |

**Important:** until STOP 1 is resolved the admin dashboard fatals: the synced `eadmin/admin.php` calls
`\e107\Storage\SiteFolderScan::ofThisSite()` in `checkSiteFolders()` on every dashboard load, and the autoloader
(`e107::autoload_namespaced()`) resolves it to `ehandlers/Storage/SiteFolderScan.php`, which is not in Lite.
`eadmin/db.php?mode=multisite` needs all three files.

---

## 1. elanguages/ (← e107_languages/)

- One-sided files: none.
- **Replaced (unmarked), 4 files:** `English/admin/lan_admin.php` (`ADLAN_SITEURL_NO_HOST`, `ADLAN_SITE_FOLDER_NOTICE*`),
  `English/admin/lan_banlist.php` (`BANLAN_IMPORT_REPLACE_BUSY`, `…_LOCK_FAILED`), `English/admin/lan_db.php`
  (Multi-Site / site folder LANs, `DBLAN_OPTIMIZE_TABLE_FAILED`; the texts carry `e107_media/` / `e107_system/` in the
  upstream form, like every other LAN text), `English/admin/lan_theme.php` (`TPVLAN_REFUSED_NOT_SITE_THEME`).
- Merged around markers: none (no marked files). Skipped no-target changes: none.

## 2. ehandlers/ (← e107_handlers/)

- **One-sided files — STOP 1 (new, not decided in §7):**
  - Upstream-only (added in `f70107574` "merge a stray site folder into the site's own from the Multi-Site page"):
    `Storage/SiteFolder.php`, `Storage/SiteFolderMerge.php`, `Storage/SiteFolderScan.php`. Used by `eadmin/admin.php`
    (`checkSiteFolders()`, called unconditionally after the upgrade check — dashboard fatal without them) and
    `eadmin/db.php` (`mode=multisite`, merge/remove site folder). Proposal: add (last round's ehandlers instruction was
    "1:1 with upstream").
  - Lite-only (deleted upstream in `b17902de0` "delete six legacy search handlers nothing can load"):
    `search/advanced_news.php`, `search/advanced_pages.php`, `search/advanced_user.php`, `search/search_news.php`,
    `search/search_pages.php`, `search/search_user.php`. Nothing in Lite references them (grep for `search/(advanced_|search_)`
    finds only their own CVS headers; `search_class.php:599` loads only `search/comments_*.php`). Proposal: delete.
- **Replaced (unmarked), 30 files:** `Database/ConnectionTrait.php`, `Database/QueryBuilder.php` (new
  `decrementNotBelowZero()`), `Database/Schema/SchemaBuilder.php`, `admin_ui.php`, `application.php`, `comment_class.php`,
  `core_functions.php`, `e_db_pdo_class.php`, `e_marketplace.php`, `e_parse_class.php`, `file_class.php`,
  `iphandler_class.php`, `login.php` (CHAP removed), `mail_manager_class.php`, `mailout_admin_class.php`,
  `mailout_class.php`, `mysql_class.php`, `notify_class.php`, `online_class.php` (`defineUnsampledState()`),
  `plugin_class.php`, `pref_class.php`, `search_class.php`, `session_handler.php` (challenge removed),
  `shortcode_handler.php`, `sitelinks_class.php`, `theme_handler.php`, `user_handler.php`, `user_model.php`,
  `vendor/hybridauth/hybridauth/src/Provider/Apple.php`, `xml_class.php`.
- **Merged around markers:** `e107_class.php` — marker "no phone-home" in `coreUpdateAvailable()` kept; upstream hunks
  (`getMySQLConfig()` site hash, `configKey()` for multi-row plugin/theme prefs, `encodeRequestUrl()` in `set_urls()`,
  `setMySQLConfig()` prefixing, removal of the ajax `online` unset) are outside the marked method. The directory arrays
  in `overridableDirs()` / `defaultDirs()` keep the Lite names (§2).
- **Marked files, no upstream change:** `media_class.php`, `message_handler.php`.
- Skipped no-target changes: none.

## 3. eadmin/ (← e107_admin/)

- **One-sided files (decided in §7):** upstream-only `includes/{categories,classis,combo,compact,tabbed}.php` not added;
  Lite-only `includes/dashboard.php` kept (not synced, dashboard fixes from `main`).
- **Replaced (unmarked), 11 files:** `admin_log.php`, `comment.php`, `cpage.php`, `db.php` (Multi-Site page; the new
  `DBLAN_MULTISITE_HELP` fallback text carries `e107_media/` / `e107_system/` as LAN text), `emoticon.php`, `history.php`,
  `image.php`, `lancheck.php`, `language.php`, `prefs.php`, `users.php`.
- **Merged around markers:**
  - `admin.php` — `checkSiteUrl()` and `checkSiteFolders()` taken, CHAP condition removed from the password warning;
    the `adminstyle` whitelist marker (`dashboard`) kept. The marker's revert condition ("upstream adds 'dashboard'") is
    not met.
  - `auth.php` — CHAP removed from the admin login (`login()` call, `onsubmit`, `hashchallenge` field); the Lite admin
    theme marker (`backend` / `dashboard` / `admin-exas-core.css`) kept.
  - `header.php` — CHAP script block removed (`chap_script.js` is no longer loaded here); admin template override marker kept.
  - `update_routines.php` — label spacing (`LAN_UPDATE_*.' '.…`), `password_CHAP` reset routine, `http_build_query(…, '', '&')`
    taken; the three `admin-exas-core.css` markers kept.
  - `theme.php` — upstream `b41eb0885` drops `'perm'` from the six `$adminMenu` entries. Applied on the three live entries
    (`main/main`, `main/admin`, `main/choose`). **Skipped, no target:** the same change on `main/online`, `main/upload`,
    `convert/main`, which the Lite marker keeps commented out (left as they are, not uncommented).
- **§7 decision:** `ver.php` → `$e107info['e107_version'] = "2.4.0.6 (git)";`, marker kept, nothing else taken
  (upstream did not change the file in this range).
- **Marked files, no upstream change:** `banlist.php`, `newspost.php`, `plugin.php`, `includes/flexpanel.php`,
  `includes/infopanel.php`.

## 4. eweb/ (← e107_web/)

- **One-sided file — STOP 2 (new):** Lite-only `js/chap_script.js`, deleted upstream in `4b59c0708` "discontinue CHAP
  login". After this round nothing in Lite references it any more (the two loaders, `eadmin/header.php:349–361` and
  `ecore/templates/header_default.php:431–443`, were removed by the same upstream commit and are synced). Proposal: delete.
- **Replaced (unmarked):** `css/e107.css` (required-field marker), `js/core/admin.jquery.css`.
- Marked files: none.

## 5. Root

- **One-sided files:** all decided in §7 — upstream-only `.editorconfig`, `.gitmodules`, `banner.php`, `composer.json`,
  `composer.lock`, `contact.php`, `download.php`, `email.php`, `gsitemap.php`, `online.php`, `print.php`, `request.php`,
  `search.php`, `submitnews.php`, `top.php`, `unsubscribe.php`, `upload.php`, `userposts.php` not added (`download.php`,
  `request.php` intentionally not ported; the rest pending); Lite-only `README.sk.md`, `README_links.md`, `REGISTRY-RULES.md`
  kept. `e107.robots.txt`, `.gitignore`, `README.md`: unchanged upstream, kept. `LICENSE`: unchanged upstream.
- **Replaced (unmarked):** `comment.php`, `fpw.php`, `login.php`, `signup.php` (all: CHAP / `hashchallenge` removal).
- **Merged (unmarked, directory mapping only):** `class2.php` — CHAP challenge removed from the session check, login call
  without `hashchallenge`, `defineUnsampledState()` when `goOnline()` is skipped; the two mapped defaults
  (`ehandlers/`, `eplugins/`) kept.
- **Merged around marker — STOP 3: `page.php`** (upstream `b03ff56b3` "answer a book or chapter that does not resolve
  with a real 404"). Taken (outside the marker): `http_response_code() !== 404` guards around `e107::canonical()`,
  `isRequested()`, `notFound()`, `emptyChapter()`, the `listChapters()` / `listPages()` not-found handling, the
  `chapter_visibility` filter in `listPages()`, `$text` instead of early return for an empty page list.
  **Not taken (collision):** the rewrite of the `if(!$pageRow)` block in `showPage()`, which is the subject of the Lite
  marker `/* LITE MODIFICATION  fix for 404 page */`. Both sides:

  Lite (kept, `page.php:734–786`):
  ```php
  if(!$pageRow)
  {
      /* LITE MODIFICATION  fix for 404 page */
      $r = eFront::instance()->getRouter();
      if (e107::getPref('url_error_redirect', false) && $r->notFoundUrl)
      {
          $redirect = $r->assemble($r->notFoundUrl, '', 'encode=0&full=1');
          e107::getRedirect()->redirect($redirect, true, 404);
      }
      header("HTTP/1.0 404 Not Found");
      /* … commented $ret block … */
      $this->page['page_title'] = LAN_PAGE_12;
      $this->page['sub_title'] = '';
      $this->page['page_text'] = LAN_PAGE_3;
      … (comments, rating, np, err, cachecontrol, authorized = 'nf', template, batch, breadcrumb)
      e107::title($this->page['page_title']);
      return;
  }
  ```
  Upstream (`b03ff56b3`):
  ```php
  if(!$pageRow)
  {
      $notFound = $this->notFound();          // http_response_code(404); e107::title(LAN_PAGE_12); pageOutput
      $this->page['page_title'] = $notFound['caption'];
      $this->page['sub_title'] = '';
      $this->page['page_text'] = $notFound['text'];
      … (same fields, authorized = 'nf', template, batch, breadcrumb)
      return;                                 // the trailing e107::title() call is gone
  }
  ```
  Proposal: keep the Lite redirect block and, below it, use `$this->notFound()` for the title/text (it also sets the
  404 status and the document title), i.e. replace `header("HTTP/1.0 404 Not Found")`, the commented `$ret` block,
  `LAN_PAGE_12` / `LAN_PAGE_3` and the trailing `e107::title()` with the upstream lines, keeping the marker and the
  redirect `if` above them.
- **§7 — `install.php`:** Lite version kept, nothing taken. Upstream changes in this range not taken:
  - `81fa05026` (2026-09-19): `LANINS_037 . ' ' . LANINS_038` — a space between the stage title and "(create database)".
- **Marked, no upstream change:** `thumb.php`. Unchanged: `e107.htaccess`.

## 6. ecore/ (← e107_core/)

- **One-sided files:** all decided in §7 — Lite-only `override/controllers/news/sef_url.php`, `override/url/news/sef_url.php`,
  `override/url/usersettings/{index.html,sef_url.php,url.php}`, `templates/bootstrap5/{login,membersonly,signup,user}_template.php`
  kept; upstream-only `templates/bootstrap4/user_template.php` not ported; `shortcodes/batch/submitnews_shortcodes.php`,
  `templates/submitnews_template.php`, `shortcodes/single/{news_categories,news_category,newsfile,newsimage}.sc`,
  `templates/legacy/*` pending, not added.
- **Replaced (unmarked), 4 files:** `shortcodes/batch/news_shortcodes.php`, `templates/header_default.php` (CHAP block
  and its `chap_script.js` loader removed), `templates/login_template.php`, `templates/online_template.php`.
- **Merged around markers:**
  - `shortcodes/batch/admin_shortcodes.php` — `sc_admin_notifications()` / `sc_admin_update()` rendered closed
    (`aria-expanded="false"`, no `open` class, notification only with version+url) taken. The three markers
    (`sc_admin_logo()` whole method, admin template override, external links hidden) untouched; the Lite divergence
    hunks are identical to before, shifted by −12 lines.
  - `shortcodes/batch/login_shortcodes.php` — `hashchallenge` field removed from `sc_login_table_password()`; the two
    LITE FEATURE label markers kept.
  - `xml/default_install.xml` — upstream `'rss_menu' => 'rss_menu'` added to `e_rss_list`, `password_CHAP` removed,
    `plug_installed['rss_menu']` `1.3 → 1.4` taken; the Lite admin prefs (`admintheme=backend`, `adminstyle=dashboard`,
    `admincss=css/admin-exas-core.css`, `admin_navbar_labels=1`) and the XML marker kept. **Check:** both files parse
    with `simplexml`; 253 `core` prefs on both sides; the only differing keys are the four Lite admin prefs.
    **Note (not changed, question):** this file carries the upstream plugin folder `rss_menu` in `e_url_list`,
    `e_sql_list`, `e_rss_list` and `plug_installed` (lines 120, 127–130, 147, 151, 254) although the Lite folder is
    `rss`; the previous round left the existing entries in the upstream form, and the new `e_rss_list` entry was applied
    the same way. With `rss_menu` in `e_rss_list`, `rss_addons::folders()` finds no `eplugins/rss_menu/e_rss.php` and
    skips it; it adds its own folder `rss` anyway (see section 8).
- **Marked, no upstream change:** `shortcodes/batch/user_shortcodes.php`, `shortcodes/single/custom.php`,
  `templates/admin_icons_template.php`, `url/user/url.php`.

## 7. ethemes/ (← e107_themes/)

- `_blank`, `voux` (upstream-only; `voux/theme_shortcodes.php` changed upstream): **skipped by §7**.
  `backend` (Lite-only): not synced. `ethemes/index.html`: kept.
- `bootstrap3`, `bootstrap5`: **taken fully from upstream (§7)**. Before the replace only two files differed:
  - **Changed (2):** `bootstrap3/theme_shortcodes.php` (login form without `onsubmit="hashLoginPassword(this)"`,
    `4b59c0708`), `bootstrap5/theme.xml` (Font Awesome front library `files="js"` → `files="css"`, `631cc7356`).
  - **Deleted Lite-only files:** none. **Added upstream-only files:** none.
  - After the replace the only differences to upstream are the two mapped `href=&quot;eadmin/admin.php&quot;` lines in
    `bootstrap3/install/install.xml` and `bootstrap5/install/install.xml` (as last round).
- Lite-only fixes checked in `main` before the run (§7): `eadmin/auth.php` forces the Lite admin theme (marker kept, see
  section 3); `ethemes/backend/theme_shortcodes.php:274–276` logout link carries `e-token`;
  `ecore/shortcodes/batch/admin_shortcodes.php:2257–2258` external links behind `if(false /* getperms('0') */)`;
  `eadmin/includes/dashboard.php` and `ethemes/backend/templates/dashboard_template.php` carry the dashboard fixes
  (`98b36b7ea`, `9bbee95a4`, `f47f66521`, `67bbf7869`) — none of them synced.

## 8. eplugins/ (← e107_plugins/)

### A. Plugins in both Lite and upstream

| Plugin | One-sided files | Result |
|---|---|---|
| download | none | Replaced (unmarked): `download_shortcodes.php` (`!empty()` author reads), `e_rss.php` (links relative to `e_PLUGIN`), `handlers/download_class.php` (`varset()` category template keys, breadcrumb after `qry`/`setVars`), `plugin.xml` (date). **STOP 4:** `request.php`, see below. |
| featurebox | none | Identical to upstream — no change. |
| forum | none | Replaced (unmarked): `forum_admin.php` (help texts as LANs), `forum_class.php` (`decrementNotBelowZero()`, `842831112`), `languages/English/English_admin.php` (`FORLAN_HELP_IMAGE_DISPLAY`, `FORLAN_HELP_ICON_DISPLAY`), `plugin.xml` (2.2 → 2.3). |
| login_menu | none | Replaced (unmarked): `login_menu.php` (form without CHAP `onsubmit`). Merged around markers: `login_menu_shortcodes.php` (`hashchallenge` field removed from `sc_lm_password_input()`; the four zero-count markers kept), `login_menu_template.php` (`password_CHAP == 2` branch removed; the two markers kept). |
| navigation | none | Identical to upstream — no change. |
| news | none | Identical to upstream — no change. |
| page | none | Identical to upstream — no change. |
| pm | none | Replaced (unmarked): `plugin.xml` (3.1 → 3.2), `pm_class.php` (two comments), `pm_shortcodes.php` (`sc_pm_nextprev()` with `type=` / `tmpl_prefix=` parms, `968044b1a`/`5014e856b`), `pm_sql.php` (`KEY pm_from`, `63e5d7e3f`). |
| siteinfo | none | Identical to upstream — no change. |
| tinymce4 | none | No upstream change. Lite differs only by the directory mapping in `wysiwyg.php:27` (code) and `wysiwyg_class.php:16,19` (comments, mapped in an earlier round) — left as is. |
| user | none | Replaced (unmarked): `e_mailout.php` (label spacing). |
| rss ↔ rss_menu | upstream-only `e_rss.php` (**STOP 5**), `languages/English_admin_rss_menu.php` (decided: same file as Lite `languages/English_admin.php`, unchanged upstream); Lite-only `README.md` (kept), `languages/English_admin.php` (kept) | See below (`03af147da`). |

**STOP 4 — `eplugins/download/request.php`** (upstream `13cc97123` "keep a resolved file name out of the mirror
branch"). Taken: `array_pad(explode(".", e_QUERY), 3, '')` inside the mirror branch (unmarked line). **Not taken
(collision):** the branch condition, which is the subject of the Lite marker at line 110.

Lite (kept, `request.php:110–112`):
```php
// LITE MODIFICATION: an id+sef request never enters the mirror branch - the SEF slug may contain "mirror".
if(empty($_GET['id']) && strpos(e_QUERY, "mirror") !== false)
```
Upstream (`13cc97123`):
```php
if(!$resolved && strpos(e_QUERY, "mirror") !== false)
```
`$resolved` exists in Lite too (`request.php:82`, set at `:103` when a by-name request resolved to an id) and Lite's
file-name test at `:204` already combines both guards (`if(!$resolved && empty($_GET['id']) && preg_match(…))`).
Proposal: `if(!$resolved && empty($_GET['id']) && strpos(e_QUERY, "mirror") !== false)`, marker kept.

**rss ↔ rss_menu (mapping as last round):** path literals `rss_menu/` → `rss/`, `e107::url('rss_menu', …)` →
`e107::url('rss', …)`, `isInstalled('rss_menu')` → `isInstalled('rss')`, class names `rss_menu_*` → `rss_*`,
LAN load `e107::lan('rss', true)`.
- Merged around markers (all markers kept): `admin_prefs.php` (hardcoded comments feed entry removed from the feed list,
  feed lookup without `rss_path`; `target='_blank'` marker kept), `rss.php` (`rssCreate::ITEM` defaults, `varset()`
  item reads, `commentItems()` / `commentParents()` / `visibleComments()` / `byNewestFirst()` / `whereClassPermits()`
  removed, `case 'comments'` removed, `$tp->toRss()` on authors, `rss_shownewsimage` block removed from RSS 0.92; the six
  `SITEURL` / sitebutton / category markers kept), `rss_setup.php` (`upgrade_post()` now moves the comments feed row —
  the value written to `rss_path` is mapped to `'rss'`, the Lite folder, because `rss_resolver` compares it with the
  addon's folder; the news-guard marker kept).
- Replaced with the mapping (unmarked): `rss_addons.php` (`folders()` / `includeAddon()`; the plugin's own folder mapped
  to `'rss'` twice), `rss_resolver.php` (legacy keys from the addons only, row ownership by `rss_path` empty / folder / url).
- `plugin.xml`: kept per §7 (upstream changed only the metadata: `version="1.4"`, `date="2026-09-16"`); marker kept.
- **STOP 5 — `e_rss.php`** (upstream `4b10df83f` "declare the comments feed from rss_menu's own e_rss.php", 240 lines,
  class `rss_menu_rss` with `legacy()` → `5 => 'comments'`, `config()` and `data()`; the code moved from `rss.php`).
  With the synced `rss.php`, `admin_prefs.php` and `rss_resolver.php`, the comments feed has no provider until this
  file exists: `rss_addons::folders()` adds `'rss'` and `includeAddon('rss')` returns `false` (file not readable), so
  nothing fatals, but the comments feed is not listed in the admin and `rss.php?comments` / legacy key `5` serve nothing.
  Proposal: add it as `eplugins/rss/e_rss.php` with the class renamed to `rss_rss` (it is loaded by
  `e107::callMethod($plugin.'_rss', …)` with `$plugin = 'rss'`); the `@see rss_menu_rss::commentParents()` doc comment
  would be mapped too, as the class name is code.
- Identical / unchanged: `e_meta.php`, `e_url.php`, images, `languages/English_admin.php`, `languages/English_global.php`,
  `rss_menu.php`, `rss_shortcodes.php`, `rss_sql.php`, `templates/rss_template.php`.
- Remaining `rss_menu` literal in the plugin: only the Lite `// LITE:` note in `rss_addons.php:206` (unchanged).

### B. Dependencies updates (changes since the previous round only)

#### forum: dependencies

Nothing new. `forum_class.php` now calls `QueryBuilder::decrementNotBelowZero()` (upstream `240317b59`), present in
`ehandlers/Database/QueryBuilder.php:2519` after this round. `forum_admin.php` uses `FORLAN_214`,
`FORLAN_HELP_IMAGE_DISPLAY`, `FORLAN_HELP_ICON_DISPLAY`, all in the synced `languages/English/English_admin.php`. The root
files and plugins listed last time are unchanged.

#### pm: dependencies

Nothing new. `sc_pm_nextprev()` now builds a core `{NEXTPREV=…}` shortcode with `total/amount/current/url` and an optional
`tmpl_prefix` (core `nextprev` shortcode, present). `pm_sql.php` adds `KEY pm_from (pm_from)` — applied on install;
an existing Lite install does not get the index unless the plugin upgrade runs (upstream behaviour).

### C. Skipped (report only)

- `githubSyncLite` (Lite-only) — skipped by §7.
- Upstream-only plugins skipped by §7: `_blank`, `admin_menu`, `alt_auth`, `banner`, `blogcalendar_menu`, `chatbox_menu`,
  `comment_menu`, `contact`, `faqs`, `gallery`, `gsitemap`, `hero`, `import`, `linkwords`, `list_new`, `newforumposts_main`,
  `newsfeed`, `newsletter`, `online`, `poll`, `search_menu`, `signin`, `social`, `tagcloud`. Upstream changed
  `chatbox_menu`, `comment_menu`, `faqs`, `newsletter`, `signin`, `social` in this range — not taken.

---

## Markers

Counted with `grep -c` over the tracked `*.php`, `*.js`, `*.css` files (combined) and `*.xml` (separately).
Note: the previous report's "after" value for `LITE MODIFICATION` (66) does not match a recount of its final tree
(`1ce8d5029^` and `912fb5957` both give 61 with this method); 61 is the value at `main` HEAD before this round.

| Marker | before (php/js/css) | before (xml) | after (php/js/css) | after (xml) |
|---|---|---|---|---|
| `LITE MODIFICATION` | 61 | 2 | 61 | 2 |
| `LITE FEATURE` | 3 | 0 | 3 | 0 |
| `LITE-SKIP` | 57 | 0 | 57 | 0 |

- Removed: none. Added: none. (No marker instruction was given in this round.)

## Checks

- `php -l` on all 84 changed PHP files (`git diff --name-only main..HEAD`, `*.php`): no errors (PHP 8.3.6).
- `git diff --name-only main..HEAD`: `elanguages/` (4), `ehandlers/` (31), `eadmin/` (17), `eweb/` (2), root
  (`class2.php`, `comment.php`, `fpw.php`, `login.php`, `page.php`, `signup.php`), `ecore/` (7), `ethemes/` (2),
  `eplugins/` (22) — plus `audit/` (this report).
- `git status` clean, no `.rej` / `.orig`.
- Branch `sync-upstream-2026-10-09` pushed; no merge, no PR, nothing pushed to `main`.
