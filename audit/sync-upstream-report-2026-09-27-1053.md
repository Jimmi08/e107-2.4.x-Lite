# Sync report — e107-2.4.x-Lite ← e107inc/e107 master

## Baseline

| | |
|---|---|
| Lite `main` HEAD | `b80ad923e544432418937475c403a4ae59b4b5b3` (2026-09-17T11:41:54+02:00) |
| Upstream `master` (baseline) | `3cd96ea259bd90b64e775bf6918c1174efbca941` (2026-09-25) |
| Branch | `sync-upstream-2026-09-26` (name as requested, created from `main`) |
| Tag note | Tag `2.4.0.4` points to `b80ad923e` — it was created on `main` before this sync. |

Scope: `elanguages/`, `ehandlers/`, `eadmin/`, `eweb/`, repo root, and (scope extensions by instruction) `ecore/`, `ethemes/`, `eplugins/`.

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
| `d8738ecbf` | audit: this report (first version) |
| `bafac1c07` | sync(ethemes): bootstrap3/bootstrap5 from upstream |
| `22483dbcb` | fix(ethemes/backend): logout link carries e-token |
| `bb196796d` | audit: ethemes section |
| `4094b346a` | sync(eplugins/download) |
| `0e51e84b0` | sync(eplugins/navigation) |
| `aefe503ae` | sync(eplugins/news) |
| `ce4f6adef` | sync(eplugins/page) |
| `29d352f18` | sync(eplugins/siteinfo) |
| `9ea8d6966` | sync(eplugins/login_menu) |
| `9546b7962` | feat(eplugins/featurebox): add from upstream |
| `1785df0a6` | feat(eplugins/forum): add from upstream |
| `036241118` | sync(eplugins/login_menu): f1e3452ce registry guards |
| `f27d531ab` | audit: eplugins section |
| `1c1c6f593` | sync(eplugins/rss) |
| `3d21975b7` | audit: rss section |
| `a83f07454` | sync(eplugins/rss): keep Lite plugin.xml metadata, marker |
| `567b8f9d8` | audit: close plugin phase |
| `f45c11c30` | feat(eplugins/pm): add from upstream (correction) |
| `2b3810658` | fix(ecore): e107 external links in the admin user menu for main admin only |
| `de3f33c84` | audit: section 9 |
| `c74261262` | fix(ecore): e107 external links hidden for all admins (change of section 9) |

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

## 7. ethemes/ (← e107_themes/) — scope extension

- `_blank`, `voux` (upstream-only themes): **skipped by instruction**.
- `ethemes/index.html`: kept.
- `bootstrap3`, `bootstrap5`: **taken fully from upstream by instruction** (no marker check, no STOP; neither
  theme contained a marker before). Directory mapping applied to code and data; comments keep the upstream
  `e107_*` form. After the replace the only differences to upstream are the two mapped lines
  `href=&quot;eadmin/admin.php&quot;` in `bootstrap3/install/install.xml` and `bootstrap5/install/install.xml`
  (the six `/* … e107_admin/phpinfo.php */` CSS comments keep the upstream form).
  - **Lite-only files deleted (4):** `bootstrap3/css/admin-exas-core.css`, `bootstrap3/images/admin-exas-core.webp`,
    `bootstrap3/templates/admin_template.php`, `bootstrap3/templates/dashboard_template.php`.
  - **Upstream-only files added (3):** `bootstrap3/admin_style.css`, `bootstrap3/admin_theme.php`,
    `bootstrap5/templates/forum/forum_stats_template.php`.
  - **Lite version differed before the replace (19):**
    - bootstrap3: `css/bootstrap-dark.min.css`, `css/corporate.css`, `css/kadmin.css`, `css/modern-dark-2.css`,
      `css/modern-dark.css`, `css/modern-light.css` (upstream added the phpinfo palette block), `install/install.xml`,
      `theme.php`, `theme.xml`, `theme_config.php`, `theme_shortcodes.php`.
    - bootstrap5: `install/install.xml`, `layouts/splash_layout.html`, `style.css`, `theme.html`, `theme.php`,
      `theme.xml`, `theme_config.php`, `theme_shortcodes.php`.
  - Notes (not acted on):
    - The Lite `bootstrap3/install/install.xml` set `admincss=css/admin-exas-core.css`, `adminpref=1`,
      `adminstyle=dashboard`, `admintheme=backend`, `url_main_module=page`; the upstream file does not, so installing
      with bootstrap3 no longer applies those prefs (core `ecore/xml/default_install.xml` still sets the admin theme).
    - `ethemes/backend/theme.xml:21` declares its own `css/admin-exas-core.css` + `images/admin-exas-core.webp`;
      nothing in the tree referenced the deleted bootstrap3 copies (grep).
- `backend` (Lite-only theme): not synced. Separate commit `22483dbcb`: `theme_shortcodes.php` logout link changed from
  `e_HTTP.'index.php?logout'` to `e_HTTP.'index.php?logout&amp;e-token='.defset('e_TOKEN')` (form used by upstream
  bootstrap3). Marker **added by instruction**; it is placed on its own line directly above the `$text .= '`
  statement that holds the link, because the link line is inside a PHP string (a `//` there would be output as HTML).
  - Finding at the time (out of scope then): `eplugins/login_menu/login_menu.php:52` and
    `eplugins/login_menu/login_menu_shortcodes.php:319, 324` built `index.php?logout` without an e-token — fixed in
    the plugin phase by taking upstream (see section 8, `9ea8d6966`).
- `php -l` on changed PHP files: no errors.

## 8. eplugins/ (← e107_plugins/) — scope extension

Marker count at the start of the plugin phase (whole tree): `LITE MODIFICATION` 67 (php/js/css) + 1 (xml),
`LITE FEATURE` 3, `LITE-SKIP` 57 (xml 0 for both).

### A. Plugins in both Lite and upstream

| Plugin | One-sided files | Result |
|---|---|---|
| download | none | Replaced (unmarked): `e_list.php`, `plugin.xml` (version 1.2 → 1.3). Marked, no upstream change outside protected code: `e_url.php`, `request.php` (Lite id+SEF gate). |
| login_menu | none | Replaced (unmarked): `config.php`, `login_menu.php` (e-token on logout). Merged around markers: `login_menu_shortcodes.php` (Remember-Me shortcode removed, `allowEmailLogin` via `varset`, e-token on `sc_lm_logout` / `sc_lm_logout_href`, empty-stats trim; the four "label: count" markers kept), `login_menu_template.php` (`LM_REMEMBERME` removed, CHAP condition without `user_tracking`, `$LM_STATITEM_SEPARATOR` theme-overridable per `15354bb17`; `LM_STATS` and `LOGIN_MENU_STATITEM` markers kept), `login_menu_class.php` (`f1e3452ce` fixes outside markers: `$list_ord` init, `isInstalled()` gate, `call_user_func($this, …)`, `$lbox_stats[0]` keys, `defset('USERLV', 0)`, `$get_stats` parameter). |
| navigation | none | Replaced (unmarked): `navigation_menu.php`. |
| news | none | Replaced (unmarked): `e_search.php` (upstream `633bfb032` dropped `$res['image']`, `6abf15197` label spacing), `news.php`. |
| page | none | Replaced (unmarked): `e_search.php`. |
| siteinfo | none | Replaced (unmarked): `e_shortcode.php` (`@getimagesize`). |
| tinymce4 | none | Identical to upstream — no change. |
| user | none | Identical to upstream — no change. |
| rss ↔ rss_menu | `languages/English_admin_rss_menu.php` (upstream) ↔ `languages/English_admin.php` (Lite); `README.md` (Lite-only) | See below (`1c1c6f593`). |

**rss ↔ rss_menu mapping (instruction):** the plugin name difference is treated like the §2 mapping, including path
literals (`rss_menu/` → `rss/`, `e107::url('rss_menu', …)` → `e107::url('rss', …)`, `isInstalled('rss_menu')` →
`isInstalled('rss')`, class `rss_menu_url` → `rss_url`); the menu file name `rss_menu.php` is the same on both sides.
- `languages/English_admin.php` (Lite) ↔ `languages/English_admin_rss_menu.php` (upstream): treated as the same file,
  content updated from upstream (only difference was the final newline), Lite file name kept; the upstream file was not added.
  Consequently the upstream `e107::includeLan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE."_admin_rss_menu.php")` calls stay
  in the Lite form `e107::lan("rss", true)` (`rss_menu.php`, `admin_prefs.php`, and unchanged `rss.php`, `rss_setup.php`,
  `rss_addons.php`, which carries a `// LITE:` note — not one of the three marker variants).
- `README.md`: kept (Lite document).
- Replaced from upstream (with the mapping): `e_meta.php` (single query refactor), `e_url.php` (final newline),
  `rss_menu.php` (upstream formatting; only functional difference was the LAN load).
- Merged around marker: `admin_prefs.php` (upstream formatting taken; `target='_blank'` marker kept).
- Marked, no upstream change outside protected code: `rss.php` (6 markers), `rss_setup.php` (news-guard marker).
- Identical: images, `English_global.php`, `rss_resolver.php`, `rss_shortcodes.php`, `rss_sql.php`, `templates/rss_template.php`.
- `plugin.xml` (STOP → instruction): Lite version kept — upstream differs only in the metadata (`version="1.3"`,
  `date="2012-08-01"`, `compatibility="2.0"`, author `e107 Inc.`) and the final newline, so nothing else was taken.
  Lite keeps `version="2.1"`, `date="2026-08-01"`, `compatibility="2.4"`, author `Jimmi` / `https://www.e107sk.com`.
  Marker **added by instruction** as an XML comment on its own line between the `<?xml …?>` declaration and the root
  `<e107Plugin>` element (`a83f07454`).
  **XML check:** core reads `plugin.xml` through `xmlClass::loadXMLfile(…, 'advanced')` → `parseXml()` → `xml2array()`
  (`ehandlers/plugin_class.php:963` `parse_plugin_xml()`, also `:3737`, `:5622`). Loaded the same way before and after
  the edit: 7 top-level keys, no `comment` key anywhere, `md5(serialize())` identical
  (`2f2c7de1aed97995464bc41d1502e628`).

**STOP — `login_menu_class.php` (instruction):** both markers **removed by instruction — fixed upstream in `f1e3452ce`**:
- `parse_external_list()`: upstream cache guard taken (per-`$active` key `loginbox_elist_<0|1>`, `getRegistry(..., FALSE)`).
- `get_plugin_data()`: upstream body taken (`e107::getPlug()->load()`).
The `get_coreplugs()` marker (empty core list) is unchanged.

### B. Taken fully from upstream (added)

- `featurebox` (22 files) — identical to upstream.
- `pm` (35 files, added by correction instruction, `f45c11c30`) — identical to upstream (no mapped content).
- `forum` (225 files) — identical to upstream apart from the directory mapping in two user-facing strings:
  `forum_update.php:899` (`efiles/public`) and `languages/English/English_global.php:9` (`eplugins/forum/…` example URLs).

#### pm: dependencies (nothing changed)

Root files missing in Lite: **none** referenced. The only non-plugin paths pm uses are core handlers present in Lite:
`ehandlers/user_select_class.php` (`pm.php:81`, `pm_shortcodes.php:166`), `ehandlers/mail_manager_class.php`
(`e_cron.php:206`), `ehandlers/mail.php` (`pm_class.php:747`, `:829` — both inside comments), and `eadmin/auth.php`,
`eadmin/footer.php` (`admin_config.php:1076`, `:1079`).

Plugins not in Lite: **none** actually used.
| file:line | reference | status |
|---|---|---|
| `eplugins/pm/e_url.php:27–44` | `forum/rules`, `forum/stats`, `forum/track` routes | inside a `/* */` comment — not active (and `forum` is now in Lite) |
| `eplugins/pm/e_shortcode.php:113–115` | `THEME.'forum/pm.png'` | theme image, guarded by `file_exists()`, falls back to `pm/images/pm.png` |

Other things pm refers to that do not exist (all identical in upstream):
| file:line | reference | status |
|---|---|---|
| `eplugins/pm/pm_class.php:177` (`legacyAttachmentDir()`) | `e_PLUGIN.'pm/attachments/'` | legacy directory, not shipped upstream either; current attachments go to `e_MEDIA.'plugins/pm/attachments/'` (`pm_class.php:150`) |
| `eplugins/pm/admin_config.php:727` | `get_files(e_PLUGIN.'pm/attachments')` | same legacy directory, upstream marks it `//FIXME wrong path` |
| `eplugins/pm/pm.php:40` | `pm/shortcodes/batch/pm_shortcodes.php` | inside a comment |
| `eplugins/pm/pm.php:194–199`, `:237–241`, `:313–317`, `:384–389`, `:476–480`; `pm_class.php:749–759` | `THEME.'pm_template.php'` / `pm/pm_template.php` | the `pm/pm_template.php` fallbacks are in comments; the live `include_once(THEME.'pm_template.php')` runs only when `THEME_LEGACY === true` (v1 theme); the plugin's own template is `templates/pm_template.php` |

Other notes: pm uses the core cron (`e_cron.php`, bulk-send queue) and mail manager, both present in Lite. With pm now in
the tree, the forum's `e107::isInstalled('pm')` branches (`forum/shortcodes/batch/view_shortcodes.php:524`, `:897`;
`forum/forum_viewtopic.php:148`) become active once pm is installed.

#### forum: dependencies (nothing changed)

Root files missing in Lite (calling code read; none of these links is guarded):
| file:line | target | context |
|---|---|---|
| `eplugins/forum/shortcodes/batch/view_shortcodes.php:948` | `email.php?plugin:forum.N` | post options dropdown, always rendered |
| `eplugins/forum/shortcodes/batch/view_shortcodes.php:949` | `print.php?plugin:forum.N` | post options dropdown, always rendered (upstream `// FIXME`) |
| `eplugins/forum/shortcodes/batch/forum_shortcodes.php:210` | `search.php` (form action) | `{SEARCH}` on the forum index |
| `eplugins/forum/shortcodes/batch/viewforum_shortcodes.php:315` | `search.php` (form action) | `{SEARCH}` in a forum view |
| `eplugins/forum/shortcodes/batch/forum_shortcodes.php:126` | `top.php?0.top.forum.10`, `top.php?0.active` | `{USERINFO}`, always rendered |
| `eplugins/forum/shortcodes/batch/forum_shortcodes.php:129` | `userposts.php?0.forums.USERID` | `{USERINFO}`, logged-in users |
| `eplugins/forum/e_user.php:35` | `userposts.php?0.forums.N` | profile statistics link when the user has posts |
| `eplugins/forum/templates/forum_template.php:39` | `online.php` | `$SC_WRAPPER['USERLIST']` unless the theme sets its own |

Plugins not in Lite:
| plugin | file:line | guarded? |
|---|---|---|
| pm | `shortcodes/batch/view_shortcodes.php:524`, `:897`; `forum_viewtopic.php:148` | yes — `e107::isInstalled('pm')` (pm is now in Lite, added later in this phase) |
| poll | `shortcodes/batch/post_shortcodes.php:424`; `forum_viewtopic.php:197`; `forum_admin.php:241`; `e_meta.php:6`, `:17` | yes — `e107::isInstalled('poll')` |
| poll | `forum_post.php:239` → `submitPoll()` (`:302` `require_once(e_PLUGIN.'poll/poll_class.php')`) | **no** — runs on any POST with `submitpoll` |
| poll | `forum_post.php:1135–1137` (preview with `poll_title`) | **no** — only `check_class($prefs->get('poll'))`; pref default `255` (nobody) in `plugin.xml:19` / `forum_class.php:1098` |
| poll | `forum_post.php:1349–1351` (new thread with `poll_title` + two options) | **no** |
| rss_menu | `templates/forum_viewtopic_template.php:185`; `templates/forum_viewforum_template.php:183–185` | inside comments — not rendered |

The poll form itself is only rendered when `poll` is installed, so the unguarded `require_once` calls are reached only by a
crafted POST; without `eplugins/poll/poll_class.php` that request ends in a fatal error.

Other: the forum's `online` table queries (`forum_viewforum.php:490`, `forum_shortcodes.php:368/374`) use the core
`online` table, which Lite has. Lite `login_menu` keeps an empty core-plugin list (marker), so the forum count in the
login menu stays off.

### C. Skipped (report only)

- `githubSyncLite` (Lite-only) — skipped by instruction.
- Upstream-only plugins skipped by instruction: `_blank`, `admin_menu`, `alt_auth`, `banner`, `blogcalendar_menu`,
  `chatbox_menu`, `comment_menu`, `contact`, `faqs`, `gallery`, `gsitemap`, `hero`, `import`, `linkwords`, `list_new`,
  `newforumposts_main`, `newsfeed`, `newsletter`, `online`, `poll`, `search_menu`, `signin`, `social`, `tagcloud`.
  (`pm` was moved from this list to B by a later instruction.)

`php -l` on all 86 added/modified PHP files of the plugin phase, plus the 19 PHP files of pm: no errors.

#### Plugin phase — marker count per variant

| Marker | `*.php` before → after | `*.js` | `*.css` | `*.xml` before → after |
|---|---|---|---|---|
| `LITE MODIFICATION` | 67 → 65 | 0 → 0 | 0 → 0 | 1 → 2 |
| `LITE FEATURE` | 3 → 3 | 0 → 0 | 0 → 0 | 0 → 0 |
| `LITE-SKIP` | 57 → 57 | 0 → 0 | 0 → 0 | 0 → 0 |

- `*.php` −2: **removed by instruction — fixed upstream in `f1e3452ce`** (`login_menu_class.php` `parse_external_list()`,
  `get_plugin_data()`).
- `*.xml` +1: **added by instruction** (`eplugins/rss/plugin.xml`).

## 9. Additional Lite fix — admin user menu external links

Instruction: in the `enav_logout` submenu of `{ADMIN_NAVIGATION}` show the four external links only to the main admin
(`2b3810658`, `ecore/shortcodes/batch/admin_shortcodes.php`).

- Location: `sc_admin_navigation()` builds the `enav_logout` menu by calling `getOtherNav($parm)`
  (`admin_shortcodes.php:1990–1997`); the entries are defined in `getOtherNav()`, branch
  `$type == self::ADMIN_NAV_LOGOUT` (from line 2208). The edit is there.
- `$tmp[5]` e107 Website, `$tmp[6]` Twitter, `$tmp[7]` Facebook, `$tmp[8]` Github are wrapped in `if(getperms('0')) { … }`
  (re-indented one level, content unchanged). Marker **added by instruction** directly above the `if`.
  `$tmp[1]` settings, `$tmp[2]` personalize (already conditional), `$tmp[3]` logout and `$tmp[4]` divider are unchanged.
- **Gap check (read `getOtherNav()` in full and the renderer):** the menu is rendered by
  `e107::getNav()->admin('', '', $menu_vars, $template, FALSE, FALSE)` → `navigation::admin()`
  (`ehandlers/sitelinks_class.php:1414`). It iterates `foreach (array_keys($e107_vars) as $act)` and recurses into
  `$e107_vars[$act]['sub']` the same way (line 1634); it never indexes by position or assumes consecutive keys, and the
  only re-indexing is `array_values()` inside the optional sort, which is off for this menu (no `'sort'` key, `$sortlist`
  FALSE). `count()` on the sub-array is used only for the `oversized` class (> 15). The submenu already had a gap when
  `$tmp[2]` is skipped (`adminpref` set and no `getperms('1')`), so non-consecutive keys were already a supported case.
  Removing keys 5–8 therefore cannot break rendering. Cosmetic effect for non-main admins: the `$tmp[4]` divider becomes
  the last item of the submenu.
- The submenu is used by `ecore/templates/admin_template.php:198, 215` and `ethemes/backend/templates/admin_template.php:203`
  (`{ADMIN_NAVIGATION=enav_logout}`); both only supply the button template.
- `php -l`: no errors.
- **Change by later instruction (`c74261262`):** the links are now hidden for all admins. The `if` block is kept, its
  condition is `if(false /* getperms('0') */)` (restore `getperms('0')` to show them to the main admin again), and the
  marker text above it reads `// LITE MODIFICATION: e107 external links hidden for all admins (restore getperms('0') to
  show them to main admin)`. Nothing else in the method changed; the gap check above applies unchanged (keys 5–8 are now
  absent for everyone, so the `$tmp[4]` divider is the last item of the submenu for all admins). Marker count unchanged.

---

## Markers

Counted in `*.php`, `*.js`, `*.css` and, per instruction, also `*.xml`.

| Marker | before (php/js/css) | before (xml) | after (php/js/css) | after (xml) |
|---|---|---|---|---|
| `LITE MODIFICATION` | 62 | 0 | 66 | 2 |
| `LITE FEATURE` | 3 | 0 | 3 | 0 |
| `LITE-SKIP` | 57 | 0 | 57 | 0 |

`LITE MODIFICATION` php/js/css 62 → 66 = −4 + 8:
- **Removed by instruction:** `ehandlers/online_class.php` (defensive init of `$members_online`); `login.php` (Refs #78 guard);
  `eplugins/login_menu/login_menu_class.php` ×2 (`parse_external_list()`, `get_plugin_data()` — fixed upstream in `f1e3452ce`).
- **Added by instruction:** `eadmin/auth.php`; `eadmin/ver.php`; `install.php` ×3 (min PHP/MySQL, default admincss,
  installer look); `ecore/url/user/url.php`; `ethemes/backend/theme_shortcodes.php`;
  `ecore/shortcodes/batch/admin_shortcodes.php` (external links in the admin user menu, section 9).
- xml 0 → 2, **added by instruction:** `ecore/xml/default_install.xml`; `eplugins/rss/plugin.xml`.

## Checks

- `php -l` on all 166 added/modified PHP files: no errors (PHP 8.4.19).
- `git diff --name-only main..HEAD`: only `elanguages/`, `ehandlers/`, `eadmin/`, `eweb/`, `ecore/`, `ethemes/`, `eplugins/`, root files
  (`LICENSE`, `class2.php`, `fpw.php`, `install.php`, `login.php`, `page.php`, `rate.php`) — plus `audit/`.
- `git status` clean, no `.rej` / `.orig`.
- Branch pushed; no merge, no PR, nothing pushed to `main`.
