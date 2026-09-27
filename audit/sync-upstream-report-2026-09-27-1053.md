# Sync report — e107-2.4.x-Lite ← e107inc/e107 master

## Baseline

| | |
|---|---|
| Lite `main` HEAD | `b80ad923e544432418937475c403a4ae59b4b5b3` (2026-09-17T11:41:54+02:00) |
| Upstream `master` (baseline) | `3cd96ea259bd90b64e775bf6918c1174efbca941` (2026-09-25) |
| Branch | `sync-upstream-2026-09-26` (name as requested, created from `main`) |
| Tag note | Tag `2.4.0.4` points to `b80ad923e` — it was created on `main` before this sync. |

Scope: `elanguages/`, `ehandlers/`, `eadmin/`, `eweb/`, repo root, and (scope extension by instruction) `ecore/`.

Directory mapping (§2) applied only to code; comments keep the upstream `e107_*` form (instruction). Where Lite
still carries an `e107_*` literal in code (e.g. `ehandlers/file_class.php:3448–3458`, `ehandlers/secure_img_handler.php:166`,
`ehandlers/language_class.php:326`, LAN texts, `eadmin/lancheck.php:397/410/471`) the Lite form was kept unchanged.

### Commits

| Commit | Scope |
|---|---|
| `64c680894` | sync(elanguages) |
| `204d73a68` | sync(ehandlers) |
| `763d7d026` | sync(ehandlers): online_class.php (separate commit — amend/force-push was blocked by the environment; instruction: separate commit) |
| `0e8e037ed` | sync(eadmin) |
| `22411b351` | sync(eadmin): auth.php / ver.php markers |
| `5e27bcc18` | sync(eweb) |
| `8e5a09a3c` | sync(root) |
| `0be55d475` | sync(root): keep Lite install.php, add markers |
| `edae44bd0` | sync(ecore) |
| `dbc2e3534` | sync(root): upstream login.php guard |
| `ff57e4234` | sync(ecore): 00d730581 in sc_admin_logo, markers |

---

## 1. elanguages/ (← e107_languages/)

- **Replaced (unmarked):** `English/admin/lan_banlist.php`, `English/admin/lan_links.php`, `English/lan_fpw.php`,
  `English/lan_search.php`, `English/lan_submitnews.php` (new LANs, `LAN_FPW6` wording).
- **Merged around markers:** none (no marked files).
- **One-sided files (STOP → instruction):**
  - `English/admin/lan_history.php` (upstream-only, added in `3dd8f673b`) — **added**.
  - `English/admin/help/cron.php` (Lite-only; deleted upstream in `6e7d5ff51`) — **deleted**.
- Skipped no-target changes: none.

## 2. ehandlers/ (← e107_handlers/)

- **One-sided files (STOP → instruction "1:1 with upstream"):**
  - Added (31): `.htaccess` (force-added; `.gitignore` ignores `.htaccess`), `Admin/DismissRequest.php`, `Admin/Incident.php`,
    `Admin/IncidentNotice.php`, `Admin/NoticeSuppression.php`, `Admin/Notices.php`, `Cache/Stamp.php`,
    `Database/TreeOrder.php`, `Flood/SourceGate.php`, and 22 vendor files (guzzlehttp/psr7 2.13.1, minify 1.3.75,
    phpmailer 6.12.0, new `symfony/deprecation-contracts`, `symfony/polyfill-php80`).
  - Deleted (18): `cli_class.php`, `e_file_inspector_sqlphar.php`, `e_jwt_class.php`, `jwt_handler.php`,
    `pcltar.lib.php`, `pcltrace.lib.php`, `search/search_event.php`, and 11 stale vendor files
    (guzzle `functions*.php`, hybridauth Mailru/Odnoklassniki/Vkontakte/Yandex, minify CONTRIBUTING/Dockerfile/
    docker-compose/ruleset, phpmailer `lang-ch`).
- **Replaced (unmarked), 108 files:** 84 under `vendor/` plus `Database/ConnectionInterface.php`,
  `Database/ConnectionTrait.php`, `Database/Platform/MysqlPlatform.php`, `Database/Platform/PlatformInterface.php`,
  `Database/QueryBuilder.php`, `Ip/Address.php`, `admin_ui.php`, `cache_handler.php`, `core_functions.php`,
  `cron_class.php`, `db_debug_class.php`, `e_db_pdo_class.php`, `e_parse_class.php`, `iphandler_class.php`,
  `mysql_class.php`, `pref_class.php`, `search/comments_{download,news,page,user}.php`, `search_class.php`,
  `sitelinks_class.php`, `theme_handler.php`, and `online_class.php` (see STOP).
- **Marked files, no upstream change outside protected code:** `e107_class.php` (no phone-home),
  `media_class.php` (ORDER BY), `message_handler.php`.
- **STOP — `online_class.php`:** upstream `4c624c191` moved the init of `$member_list`, `$members_online`,
  `$listuserson` to the top of `goOnline()`; this is the subject of the Lite marker "defensive init of
  $members_online". Instruction B: file taken from upstream, marker **removed by instruction** (the upstream fix
  supersedes it and also covers the empty-table and early-return cases).

## 3. eadmin/ (← e107_admin/)

- **One-sided files (STOP → instruction):** upstream-only `includes/{categories,classis,combo,compact,tabbed}.php`
  **not added** (listed in Lite `.gitignore`); Lite-only `includes/dashboard.php` **kept** (Lite admin style,
  protected by the `admin.php` marker).
- **Replaced (unmarked):** `cron.php`, `history.php`, `lancheck.php`, `links.php`, `users.php`.
- **Merged around markers:**
  - `admin.php` — upstream notices (cron refusals, admin reset attempts, dismissals) taken; `dashboard` whitelist kept.
  - `banlist.php` — upstream CSV import (`importBanlistCsv`) taken; both `perPage = 100` kept.
  - `includes/infopanel.php` — `&e-token=` added to 5 admin AJAX URLs (upstream `084711dc8`); unmarked lines, the
    LITE-SKIP subject (removed feed panel) untouched. Three of the URLs target the feed panel Lite removed.
- **Marked files, no upstream change outside protected code:** `header.php`, `newspost.php`, `theme.php`,
  `update_routines.php`, `plugin.php`, `includes/flexpanel.php`.
- **STOP — unmarked Lite divergences (instruction):**
  - `auth.php` — Lite version kept; marker **added by instruction** above `if($adminTheme !== 'backend')`.
    No other upstream change in the file.
  - `ver.php` — upstream not taken; version set to `2.4.0.5 (git)`; marker **added by instruction**.

## 4. eweb/ (← e107_web/)

- One-sided files: none. Marked files: none.
- **Replaced (unmarked):** `css/backcompat.css`, `js/core/admin.jquery.js`, `js/core/all.jquery.js`,
  `js/plupload/upload.php` (uploads refused without e-token; `LANUPLOAD_REFUSED_TOKEN_MISSING` exists in Lite).

## 5. Root

- **Replaced (unmarked):** `class2.php`, `fpw.php`, `rate.php`.
- **Merged around markers:** `page.php` (upstream chapter meta + `hash_equals` taken; 404 redirect block kept).
- **Marked, no upstream change outside protected code:** `thumb.php`.
- **Unchanged:** `e107.htaccess` (only difference is the directory mapping).
- **STOP 1 — `login.php` (instruction A1):** upstream guard taken as is; the Refs #78 marker **removed by instruction**.
  - The loop case from #78 is handled upstream by `redirection::getMembersOnlyRedirectUrl()` (e107inc/e107#5698):
    with `user_reg=0`, no social login and the stock `login.php`, a members-only redirect goes to `membersonly.php`.
  - Accepted behaviour changes: a guest opening `login.php` directly with `user_reg=0` goes to the previous URL /
    SITEURL instead of `membersonly.php`; a logged-in user opening `login.php` directly goes to the previous URL /
    SITEURL instead of profile edit.
  - Known upstream risk, not introduced by this change: `redirection::getCookie()` returns the raw client cookie
    `<e_COOKIE>__previousUrl` when the session has no value, so a client-set cookie pointing to `login.php` causes a
    self-loop for that client only. The previous Lite guard had the same path. Candidate for an upstream issue.
  - Upstream `ef10f6182` (template handling) was taken earlier in `8e5a09a3c`.
- **STOP 2 — `install.php` (instruction):** Lite version kept, no upstream change taken (the two hunks taken in
  `8e5a09a3c` were reverted in `0be55d475`). Markers **added by instruction**: above `MIN_PHP_VERSION`, above the
  default `admincss`, and "installer uses Lite admin theme look" placed directly above `return '<!DOCTYPE html>`
  in `template_data()` (the stylesheet/background lines are inside that HTML string; a `//` there would be output
  as HTML). `$ret` directory array: directory mapping per §2, no marker.
  Upstream changes **not taken**:
  - directory literals `"e107_handlers/"` / `"e107_admin/ver.php"` instead of `$ret` (directory mapping);
  - `MIN_PHP_VERSION '8.0'` / `MIN_MYSQL_VERSION '4.1.2'` (`cfebf11d0`);
  - admin skin selector (modern-light / modern-dark) in the admin step (`ff2b79145`);
  - default `admincss` `css/bootstrap-dark.min.css` (`744a713dd`);
  - password compare `varset(...) !== varset(...)` (`17bb47d65`);
  - `varset($_SERVER['SCRIPT_URL'])` in `stats()` (`7b9ebe109`);
  - installer look: bootstrap3 `bootstrap-dark.min.css` / `admin_style.css`, background `#181818` (`e0f56d364` and later).
  - (`logDir` and the two `stats()` calls are protected by existing markers.)
- **STOP 3:** `e107.robots.txt`, `.gitignore`, `README.md` — kept, non-code file, no marker possible.

### Root: upstream-only files

1. **Added from upstream:** `LICENSE` — reason: GPL license text.

2. **Intentionally not ported:** `download.php`, `request.php` — reason: handled by the download plugin
   (`eplugins/download`). References in the Lite tree (not changed; to be fixed later as LITE MODIFICATION):

   `download.php`:
   | file:line | form |
   |---|---|
   | `eadmin/update_routines.php:1759` | `link_url LIKE 'download.php%'` (update check) |
   | `eadmin/links.php:367` | `download.php?list.#` |
   | `ehandlers/comment_class.php:1778` | `e_HTTP."download.php?view.N"` |
   | `ehandlers/comment_class.php:1780` | `e_HTTP."download.php?list.N"` |
   | `ehandlers/search/comments_download.php:31` | `download.php?view.N` |
   | `ehandlers/sitelinks_class.php:1047` | `e_ADMIN_ABS.'download.php'` (commented out) |
   | `ehandlers/e_file_inspector.php:37` | `e_ADMIN . "download.php"` (inspector list) |
   | `comment.php:256` | `e_HTTP."download.php?view.N"` (JS redirect) |
   | `eplugins/download/e_tagwords.php:25` | `e_BASE."download.php?view.N"` |
   | `eplugins/download/e_list.php:62` | `e_BASE."download.php?view.N"` |
   | `eplugins/download/e_list.php:64` | `e_BASE."download.php?list.N"` |
   | `eplugins/download/handlers/download_class.php:993` | `SITEURL."download.php?view.N"` (report mail) |
   | `eplugins/download/request.php:589` | `e_HTTP."download.php"` (inactive-download message) |
   | `eplugins/download/request.php:172` | `e_BASE."download.php?error.N.1"` (commented out) |

   `request.php`:
   | file:line | form |
   |---|---|
   | `ecore/bbcodes/file.bb:11` | `e_HTTP."request.php?file=N"` |
   | `ehandlers/e_parse_class.php:4759` | `e_HTTP . 'request.php?file=' …` |
   | `ehandlers/bbcode_handler.php:919` | regex `request.php?file=N` → `[file=N]` (HTML→bbcode) |
   | `ehandlers/e_parse_class.php:3851` | `{e_DOWNLOAD}` → `e_HTTP . 'request.php?'` |
   | `ehandlers/e_parse_class.php:3876` | `{e_DOWNLOAD}` → `SITEURLBASE . e_HTTP . 'request.php?'` |
   | `ehandlers/e_parse_class.php:2524–2525` | `href='request.php` → `SITEURL.'request.php` |
   | `ehandlers/ren_help.php:215` | `[file={e_BASE}request.php?ID]` |
   | `ehandlers/ren_help.php:219` | `[file={e_BASE}request.php?URL]` |
   | `ehandlers/e107_class.php:5011` | comment: legacy `request.php?download.43` |
   | `ehandlers/e_thumbnail_class.php:465, 675` | comments mentioning request.php |

   **`request.php?file=`:** `eplugins/download/request.php` (read in full) never reads `$_GET['file']`. For
   `request.php?file=N`, `e_QUERY` is `file=N`: `findByName()` finds no row, the query splits to `$id = intval('file=N') = 0`,
   and the file branch calls `lite_refuse_not_found()` (404). Independently, every file request must carry
   `id` + matching `sef` (`lite_sef_matches()`), so the `file` form is never served. In addition the root
   `request.php` those links point to does not exist in Lite.

3. **Pending (decision later):**
   | file | what | Lite references |
   |---|---|---|
   | `userposts.php` | user's comments/forum posts list | `ecore/shortcodes/batch/user_shortcodes.php:90,147,460,469`; `ecore/shortcodes/batch/comment_shortcodes.php:478` |
   | `search.php` | site search page | `ehandlers/e_parse_class.php:1507` (e_SELF check); `ecore/url/search/url.php:34`; `eplugins/rss/rss.php:711`; `eplugins/download/download_shortcodes.php:1204` |
   | `email.php` | "email this item" page | `ehandlers/emailprint_class.php:91`; `elanguages/English/lan_online.php:35` |
   | `print.php` | printer-friendly page | `ehandlers/emailprint_class.php:97` |
   | `unsubscribe.php` | mailing unsubscribe | `eadmin/mailout.php:917–918`; `ehandlers/notify_class.php:221` |
   | `contact.php` | contact form | only a comment: `ecore/shortcodes/batch/navigation_shortcodes.php:145` |
   | `submitnews.php` | front-end news submission | `ecore/xml/default_install.xml:860` (default link); comment `elanguages/English/English.php:89` |
   | `upload.php` | public upload page | none for root `upload.php` (matches found are `eweb/js/plupload/upload.php` and admin `upload.php`) |
   | `banner.php` | banner plugin redirector | none |
   | `gsitemap.php` | gsitemap plugin front page | none |
   | `online.php` | who-is-online page | comments only (`ehandlers/online_class.php:46–49`, `lan_users.php:91`) |
   | `top.php` | top posters (forum) | none |
   | `.editorconfig` | editor settings | `ehandlers/file_class.php:3396` (file-inspector exclude list) |
   | `.gitmodules` | git submodules | `ehandlers/file_class.php:3398` |
   | `composer.json` | composer manifest for `ehandlers/vendor` | `ehandlers/file_class.php:3402`; comment `ehandlers/Security/Cipher/PhpseclibCipher.php:24` |
   | `composer.lock` | composer lock | `ehandlers/file_class.php:3403` |

   Lite-only root files kept: `README.sk.md`, `README_links.md`, `REGISTRY-RULES.md`.

## 6. ecore/ (← e107_core/) — scope extension

- **Replaced (unmarked), 27 files:** `bbcodes/{bb_alert,bb_block,bb_img,bb_youtube}.php`,
  `bbcodes/{email,flash,link,quote,stream,table,textarea,url}.bb`, `controllers/system/xup.php`,
  `shortcodes/batch/{bbcode,contact,navigation,signup,usersettings}_shortcodes.php`,
  `shortcodes/single/{search,sitelinks_alt}.php`, `sql/core_sql.php`, `templates/{header_default,login,
  membersonly,search}_template.php` (`header_default.php` included), `url/search/rewrite_url.php`,
  `url/user/rewrite_url.php`.
- **Upstream taken, Lite values kept (then marked by instruction):**
  - `xml/default_install.xml` — upstream `ban_durations`, removal of `lan_global_list` / `user_tracking` taken;
    Lite `admintheme=backend`, `adminstyle=dashboard`, `admincss=css/admin-exas-core.css`,
    `admin_navbar_labels=1` kept. Marker **added by instruction** (XML comment on its own line above `admintheme`).
  - `url/user/url.php` — upstream member-list paging taken; Lite `usersettings.php?ID` edit link kept. Marker
    **added by instruction**.
- **Merged around markers:**
  - `shortcodes/batch/login_shortcodes.php` — LITE FEATURE labels kept; upstream removal of the Remember-Me
    shortcodes (`1d68580a6`) taken.
  - `shortcodes/single/custom.php` — `isInstalled('login_menu')` guard kept; inside it the e-token logout link and
    the autologin removal taken.
  - `shortcodes/batch/user_shortcodes.php` — usersettings edit link kept; upstream `legacyTemplate` changes taken.
  - `shortcodes/batch/admin_shortcodes.php` — nav template override kept; e-token on 3 logout links and on
    `e107_update.php`, `multilanguage_subdomain` check taken. `sc_admin_logo()`: **upstream hunk applied by
    instruction inside a whole-method marker — does not touch $parm parsing** (`00d730581`, `@getimagesize` +
    no style when dimensions are unreadable).
- **Marked, no upstream change outside protected code:** `templates/admin_icons_template.php`.

### ecore: one-sided files

1. **Kept, Lite-only:**
   - `override/controllers/news/sef_url.php` — Lite news SEF controller override (`9611500d8`).
   - `override/url/news/sef_url.php` — Lite custom news URL system (`6f0e801dd`).
   - `override/url/usersettings/index.html` — directory guard for the usersettings URL module (`9611500d8`).
   - `override/url/usersettings/sef_url.php` — usersettings SEF URL config (`9611500d8`).
   - `override/url/usersettings/url.php` — usersettings URL config (`9611500d8`).
   - `templates/bootstrap5/login_template.php` — Lite Bootstrap 5 login template (last Lite commit `7a269ac65`).
   - `templates/bootstrap5/membersonly_template.php` — Lite Bootstrap 5 members-only template (`7a269ac65`).
   - `templates/bootstrap5/signup_template.php` — Lite Bootstrap 5 signup template (`7a269ac65`).
   - `templates/bootstrap5/user_template.php` — Lite Bootstrap 5 user template (`7a269ac65`).
2. **Intentionally not ported:** `templates/bootstrap4/user_template.php` — reason: `e107::coreTemplatePath()`
   prefers `templates/bootstrap5/` when BOOTSTRAP > 3, and Lite has `bootstrap5/user_template.php`.
3. **Pending:**
   - `shortcodes/batch/submitnews_shortcodes.php`, `templates/submitnews_template.php` — belong to root
     `submitnews.php` (pending).
   - `shortcodes/single/news_categories.sc` — **used**: `eplugins/news/news.php:922` (`render_newscats()`, called at
     160/167/184 when pref `news_cats == 1`) parses `{NEWS_CATEGORIES}` with no batch; `shortcode_handler.php:1320–1325`
     then loads `ecore/shortcodes/single/news_categories.sc`, which is missing, so the block renders empty today.
   - `shortcodes/single/news_category.sc` — no caller found (upstream file returns `""` anyway).
   - `shortcodes/single/newsfile.sc` — no caller found.
   - `shortcodes/single/newsimage.sc` — not reached: `{NEWSIMAGE}` in `eplugins/news/templates/*` is parsed with the
     `news_shortcodes` batch (`sc_newsimage()` in `news_shortcodes_legacy.php:116`); `ethemes/backend/admin_theme.php:317`
     is inside a `/* */` comment.
   - `templates/legacy/` (5 files) — `THEME_LEGACY` is defined only in `ehandlers/theme_handler.php:1253`, `:1344`
     (`$legacy`, computed from the theme) and `:1371` (`false`). Nothing in Lite hard-codes `true`; it becomes true
     only for a v1-style theme. No such theme in `ethemes/` was verified.

### Requested confirmations

- `ecore/shortcodes/batch/login_shortcodes.php:200` `sc_login_table_dest()` emits
  `<input type="hidden" name="{redirection::LOGIN_DEST_FIELD}" value="{token}" />` (empty when no token);
  used by `ecore/templates/login_template.php:43`.
- `ehandlers/redirection_class.php` in this branch contains `verifyDestination()` (line 633),
  `getLoginDestination()` (847), `clearLoginDestination()` (907).
- **Finding (not changed):** Lite-only `ecore/templates/bootstrap5/login_template.php` (used when BOOTSTRAP > 3) has
  no `{LOGIN_TABLE_DEST}` and still uses `{LOGIN_TABLE_REMEMBERME}`, which upstream removed.

### XML check

`ecore/xml/default_install.xml` loaded with `xmlClass::loadXMLfile(..., 'advanced')` before and after the marker:
3 top-level keys, no `comment` key anywhere, `md5(serialize())` identical (`be3aa50bbc94fa922004e11c09a8ae53`).

---

## Markers

Counted in `*.php`, `*.js`, `*.css` and, per instruction, also `*.xml`.

| Marker | before (php/js/css) | before (xml) | after (php/js/css) | after (xml) |
|---|---|---|---|---|
| `LITE MODIFICATION` | 62 | 0 | 66 | 1 |
| `LITE FEATURE` | 3 | 0 | 3 | 0 |
| `LITE-SKIP` | 57 | 0 | 57 | 0 |

`LITE MODIFICATION` php/js/css 62 → 66 = −2 + 6:
- **Removed by instruction:** `ehandlers/online_class.php` (defensive init of `$members_online`); `login.php` (Refs #78 guard).
- **Added by instruction:** `eadmin/auth.php`; `eadmin/ver.php`; `install.php` ×3 (min PHP/MySQL, default admincss,
  installer look); `ecore/url/user/url.php`.
- xml 0 → 1, **added by instruction:** `ecore/xml/default_install.xml`.

## Checks

- `php -l` on all 166 added/modified PHP files: no errors (PHP 8.4.19).
- `git diff --name-only main..HEAD`: only `elanguages/`, `ehandlers/`, `eadmin/`, `eweb/`, `ecore/`, root files
  (`LICENSE`, `class2.php`, `fpw.php`, `install.php`, `login.php`, `page.php`, `rate.php`) — plus this report in `audit/`.
- `git status` clean, no `.rej` / `.orig`.
- Branch pushed; no merge, no PR, nothing pushed to `main`.
