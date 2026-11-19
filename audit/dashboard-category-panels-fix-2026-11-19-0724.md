# Dashboard: category panels for all admins — fix report 2026-11-19 07:24 UTC

Refs #120. Branch `refs-120-dashboard-category-panels`, created from `main`.

Legend: **read** = verified by reading the whole function and at least one caller.
**assumed** = not verified against code, data or a browser.

## 0. Setup

| Item | Value |
|------|-------|
| Base | `main` at `9a4ee2eceb377fcdfc30f3a5e8e7e9b705885da3` (`git rev-parse HEAD`) |
| Commit date | 2026-10-26T19:41:11+01:00 (merge of PR #119) |
| Version (`eadmin/ver.php`) | `2.4.0.7 (git)` |
| Background audit | `audit/dashboard-restricted-admin-panels-audit-2026-11-19-0630.md`, read from branch `origin/audit-dashboard-restricted-admin-panels` (not on `main`). Its line numbers were re-checked against HEAD before use; they matched for every location used here. |
| Upstream reference | `e107inc/e107` `master` = `7391ca0efa8d17436aaa38a8d73bf0f136dbf49e` (`git ls-remote`, shallow blobless clone) |

### Lite-only check

Upstream tree at `7391ca0` (`git ls-tree -r`) contains no `e107_admin/includes/dashboard.php`,
no `e107_themes/backend/` directory at all, and no `dashboard_template.php` anywhere. The
three changed files therefore have no upstream counterpart (`eadmin/` = `e107_admin/`,
`ethemes/` = `e107_themes/`). No `LITE MODIFICATION` markers were added.

## 1. Commits

| # | Group | Message |
|---|-------|---------|
| 1 | 4.1 + 4.2 + 4.4 | `Category panels for all admins, empty perm means any admin` |
| 2 | 4.3 | `No empty dashboard panels` |
| 3 | 4.5 | `Close unclosed rule in backend admin_style.css` |
| 4 | report | this file |

`php -l` passes on `eadmin/includes/dashboard.php` and `ethemes/backend/templates/dashboard_template.php`.

## 2. Changed lines per file

### `ethemes/backend/templates/dashboard_template.php`

Old `:166-178` → new `:166-170`. The `if(getperms('1')) { ... } else { ... }` block is
gone. `core-multi-icons-category` is defined unconditionally with `'perm' => ''`
(was `'1'`); all other keys unchanged (`method_name`, `links`, `style`, `caption`,
`multi`, `personalize`). The `core-infopanel-mye107` entry is removed. `$caption`
(`:164`) is kept: `core-infopanel-icons-plugins` still uses it. `core-infopanel-news`,
`plug-infopanel-comments`, `addons-single-info-panel`, `addons-single-icons-modules`
are unchanged.

### `eadmin/includes/dashboard.php`

| Old line | New line | Change |
|----------|----------|--------|
| `:316-319` | removed | `core-infopanel-mye107` entry removed from `getDefaultPositions()`; `core-infopanel-news` kept |
| — | `:376` | `core_infopanel_icons()`: `$tiles = 0;` |
| — | `:382`, `:389` | `$tiles++;` after each rendered tile (both branches) |
| — | `:396-398` | `if ($tiles === 0) { return ''; }` before `tablerender()` |
| `:761` | `:764` | `buildPanels()`: `if ($perm !== '' && !getperms($perm)) continue;` |

`core_infopanel_icons()` was not otherwise changed (decision 4.1 was about not touching
it for the template; 4.3 explicitly asks for the zero-tile return).

### `ethemes/backend/admin_style.css`

`:127`: a whitespace-only line replaced with `}`, closing `.core-mainpanel-block small {` (`:125`).

## 3. `getperms()` calls in `dashboard.php` (4.2)

All calls in the file after the change (**read**):

| Line | Call | Argument source | Template perm? | Action |
|------|------|-----------------|----------------|--------|
| `:21` | `getperms('1')` | literal; computes `FLEXPANEL_ENABLED` | no | unchanged |
| `:163` | `getperms('1')` | literal; `canEditLayout()` | no | unchanged (decision) |
| `:233` | `getperms('1')` | literal; `panelInfo()` decides whether to emit the HTML debug comment | no | unchanged |
| `:626`, `:638` | `getperms($icon['perms'])` | icon perms from `adminLinks()` in `render_infopanel_icons()` (customize form) | no | unchanged |
| `:764` | `getperms($perm)` | `$panel['perm']` from the template, `buildPanels()` | **yes** | `''` skipped |

The template perm reaches only two other places, neither calls `getperms()`:
`panelInfo($id, $perm)` (label only, `(none) = all admins` for `''`) via
`varset($options['perm'], '')` in `core_infopanel_icons()`, `core_categories_icons()`,
`addon_group_icons()`, `addon_single_panel()`.

Semantics of `:764`: `$perm = varset($params['perm'], false)`. Exactly `''` → no check.
`'0'`, `'1'`, `'4'` etc. → `getperms()` as before. A missing `perm` key gives `false`,
which is not `''`, so it is still passed to `getperms(false)` exactly as before.

Tiles inside panels are still filtered per icon: `renderAdminButton()`
(`ehandlers/sitelinks_class.php:1669-1745`, **read**) returns `''` unless
`getperms($perms)` passes for the icon's own perm. That is independent of `:764`.

## 4. Empty panels (4.3)

### Category panels — no fix needed (**read**)

`core_categories_icons()` (`dashboard.php:408-451`), called only from `buildPanels()`
through the template entry `core-multi-icons-category`:

- For each category key except 6, `$text_rend` is concatenated only from
  `renderAdminButton(..., 'div')` for icons that are in `$testarray`
  (`personalize => true` → `self::$userAdminPanelArray`) and whose category
  (`$funcinfo[4]`) matches.
- `renderAdminButton()` returns `''` when `getperms()` fails and a non-empty
  `<div class='core-mainpanel-block ...'>` string in mode `div` otherwise.
- `$dashboards[]` gets an entry only `if ($text_rend)`; only those entries are passed
  to `addToPosition()`. The method returns `false`, so `buildPanels()` never places the
  `core-multi-icons-category` key itself.

So a category panel exists only when at least one icon passes both the personalisation
list and the icon perm check.

### `core-infopanel-icons-plugins` — fixed

`core_infopanel_icons()` with `links => 'plugin'`: `adminLinks('plugin')` →
`pluginLinks(..., 'array')` (perms `P<plugin id>`). `$dashboardLinks` is `'plugin'`,
never `'plugins'`, so the `else` branch runs (`if (true)`). Before the fix it always
returned a `tablerender()` result, so an admin with no `P*` perm got a panel with an empty
`<div>`, placed in `menu-area-02`. Now it returns `''` with zero tiles and `buildPanels()`
skips `addToPosition()` (`if ($text)`).

## 5. Default position (4.4)

`getDefaultPositions()['default']` now holds only `core-infopanel-news`
(`menu-area-08`, weight 0). Category panels (`setMenu`, `userMenu`, `contMenu`,
`toolMenu`, `managMenu`) without a stored entry fall through to the unchanged fallback in
`getMenuPosition()`: `menu-area-02`, weight 1000 (**read**).

Stored data was not touched. A `core-flexpanel-order['core-infopanel-mye107']` entry,
if one exists in the core pref (or in a user pref with `adminpref = 0`), is now unused:
no template entry or runtime panel has that id, so `getMenuPosition()` is never asked for
it, and `saveOrder()` keeps it because it merges and never deletes. Not deleted, as
instructed. Whether the site has such an entry: **assumed** unknown (not inspected).

Do not confuse it with the pref key `core-infopanel-mye107` at the top level of the pref
array: that one is the "Personalize Icons" list (section 7) and is still used.

## 6. CSS (4.5)

### Before

`admin_style.css:125` opened `.core-mainpanel-block small {` followed by
`font-size: 14px;` and never closed it. Brace count was 856 `{` / 855 `}`. With comments
stripped the nesting depth is 0 after line 124 and 1 at end of file, so this was the only
imbalance. The rule came in with commit `0988345` ("synchronization attemp", 2026-08-01)
(`git log -S`).

The intended end is immediately after `font-size: 14px;`: the next non-blank line is the
`/* TODO - use col-xx-x markup ... */` comment followed by top-level `@media` blocks
that only make sense at top level. The brace was put on line 127 (previously a single
space).

### After

856 `{` / 856 `}`; depth 0 at end of file.

### Upstream

Upstream `e107_themes/bootstrap3/admin_style.css` (at `7391ca0`) is balanced (847/847)
and has **no** `.core-mainpanel-block small` rule. Its `.core-mainpanel-block` base rule
is `width:20%` (Lite: `15%`) and it has the same media query block.

### Affected rules and visual change — **assumed**, not verified in a browser

Everything from line 129 to the end of the file (line 2214) was inside the unclosed
block. Expected parsing:

- Browsers with CSS nesting (current Chrome, Firefox, Safari): the following rules become
  nested rules of `.core-mainpanel-block small`, i.e. selectors like
  `.core-mainpanel-block small table input.form-control`, which match nothing. Nested
  `@media` blocks likewise only contain rules scoped under that selector.
- Browsers without nesting: the content is parsed as invalid declarations and dropped.

`font-size: 14px` itself was valid before and stays valid.

Either way the rules after line 125 were effectively not applied, and they apply now. The
groups (by section comments in the file) are:

- `:130-154` responsive tile widths of `.core-mainpanel-block` (25% ≤1700px, 33.33%
  ≤1500px, 30% ≤1200px, 20% ≤992px, 25% ≤768px, 33.33% ≤668px, 50% ≤500px/380px). Until
  now tiles stayed at the base `15%` at every viewport width; after the fix they become
  wider below 1700px, so fewer tiles per row and less label truncation.
- `:157-186` `table .form-control { width: 206px }` and Bootstrap 2 input sizes
  (`.input-mini` … `.input-xxlarge`).
- Media manager / media picker / dropzone / image picker / icon picker (`:229-425`).
- Bootstrap bug fixes, datepicker, core icons and image buttons (`:426-597`).
- Admin list table, system messages, backwards-compatibility CSS (`:598-838`).
- ICONS section (`:839-1149`).
- Dark drop-down menus, color box, tabs styled like jQuery UI (`:1154-1329`).
- Bootstrap tags input, selectize overrides, theme manager (find online), iOS nav fix,
  selectize, uiAlert, theme selection, legacy styling, BC for old plugins
  (`:1350-1723`).
- Flex classes, parent/child indicator, table clean-up for light themes, Bootstrap switch,
  remaining rules (`:1768-2214`).

Admin pages using those classes may change appearance after this commit (the rules were
written to be active, but nobody has seen them active in this theme since 2026-08-01).
This is the largest risk of the branch and needs a visual check of a few admin pages
(dashboard, a list page, media manager, theme manager).

## 7. "Personalize Icons" list and restricted admins under `adminpref = 1` (read only)

**Read:**

- `adminstyle_dashboard::__construct()` (`dashboard.php:73-86`): with `adminpref = 1`,
  `self::$user_pref = e107::getPref()` (core prefs). `self::$userAdminPanelArray` =
  core pref `core-infopanel-mye107`, or `getNav()->getDefaultAdminPanelArray()` when that
  pref is empty.
- `getDefaultAdminPanelArray()` (`sitelinks_class.php:1190-1229`): every key of
  `adminLinks()` except `e-administrator`, `e-updateadmin`, `e-banlist`, `e-cache`,
  `e-comment`, `e-credits`, `e-db`, `e-docs`, `e-emoticon`, `e-users_extended`,
  `e-fileinspector`, `e-language`, `e-ugflag`, `e-notify`, `e-phpinfo`, `e-upload`,
  `e-cron`, `e-search`, `e-admin_log`, `e-eurl`.
- `core_categories_icons()` with `personalize => true`: a tile is shown iff the icon key
  is in that one shared list **and** the current admin passes the icon's own perm in
  `renderAdminButton()`.

Consequences:

- Under `adminpref = 1` every admin uses the same list. A restricted admin sees the
  intersection of the shared list and her own perms; she cannot widen it.
- Compared with the removed `core-infopanel-mye107` panel, which ignored the list
  (`if (true)` in `core_infopanel_icons()`) and showed every core icon she had perms for,
  a restricted admin now loses tiles for icons that are not in the list, even if she has
  the perm (with the default list e.g. `e-comment`, `e-banlist`, `e-upload`,
  `e-admin_log`). The pages remain reachable through the admin navigation menu; only the
  dashboard tile is gone.
- The "Personalize" link (`admin_shortcodes.php:2249-2259`) and `?mode=customize`
  (`admin.php:22-33`) are offered only when `adminpref = 0` or the admin passes
  `getperms('1')`.
- Saving the list in the `dashboard` style: `render()` shows the form at
  `?mode=customize` with a `submit-mye107` button, but no code in `dashboard.php` handles
  `$_POST['submit-mye107']` (the only handler is `adminstyle_infopanel::__construct()`,
  `infopanel.php:142-146`, and `infopanel.php` is not loaded in the dashboard style:
  `admin.php:68-81` requires only `includes/<adminstyle>.php`). So in the dashboard style
  the list cannot be saved through the UI; the stored core pref (e.g. saved earlier with
  the `infopanel` style) or the default list applies. Not changed here; reported as a
  separate finding.

## 8. Before / after behaviour

`adminpref = 1`, style `dashboard`, theme `backend`.

| Admin | Before | After |
|-------|--------|-------|
| Main admin (perm `0`) | Category panels (perm `'1'` passes), plugins panel, chart panels | Same panels. `core-infopanel-icons-plugins` disappears only if no plugin admin link exists. Drag/save unchanged. |
| Perm `1` without `0` | Category panels with permitted icons, plugins panel only if `getperms('')` passed (trailing dot) | Category panels with permitted icons, plugins panel whenever she has ≥1 `P*` perm, independent of the perms string format. Drag/save unchanged. |
| No `1`, perms with trailing dot (`1.3.` style, saved by `updatePerms()`) | `core-infopanel-mye107` (all permitted core icons, `menu-area-07`, 1/3 width) + plugins panel (possibly empty) | Category panels (shared list ∩ own perms) at positions from the shared `core-flexpanel-order`; plugins panel only with ≥1 tile. No drag. |
| No `1`, perms without trailing dot (saved from Users → Edit) | `getperms('')` failed: neither `core-infopanel-mye107` nor the plugins panel; only perm-`4` chart panels if she had `4` | Same as the row above. |

## 9. Manual test checklist

Preconditions: style `dashboard`, admin theme `backend`, `adminpref = 1`, at least one
plugin with an admin page installed. Check each admin in a separate browser session.
"No empty panel" means: no panel whose body has zero tiles; use the page source, each panel
body is preceded by `<!-- dashboard-panel: ... -->` for admins with `1`.

### A. Main admin (perm `0`)

- [ ] Panels: Settings, Users, Content, Tools, Manage (each only if the shared list has an icon in it), "… Control Panel - Plugins", user chart if enabled.
- [ ] No `<username>'s Control Panel` panel without "- Plugins".
- [ ] No empty panel.
- [ ] Drag available: dashed drop zones with area labels, panel headings show the move cursor.
- [ ] Move e.g. Users to `menu-area-07` and Tools to `menu-area-03`, reload: positions kept.
- [ ] Page source: category panels show `perm: (none) = all admins`.

### B. Admin with `1`, without `0`

- [ ] Panels: only categories where she has at least one permitted icon from the shared list; plugins panel only if she has ≥1 plugin perm.
- [ ] No empty panel.
- [ ] Drag available; a save is shared (check with A after reload).

### C. Restricted admin without `1`, `user_perms` with trailing dot

Set up via Admin → Administrators (uses `updatePerms()`); check `user_perms` in the DB ends with `.`.

- [ ] Panels: category panels limited to shared list ∩ own perms; plugins panel only if she has a `P*` perm.
- [ ] No narrow one-third "Control Panel" panel in `menu-area-07`.
- [ ] No empty panel (also test an admin with no `P*` perm: no plugins panel at all).
- [ ] No drag: no dashed zones, no move cursor, `admin.flexpanel.js` not loaded.
- [ ] Positions set in A (Users in area 07, Tools in area 03) apply here as well.

### D. Same admin without trailing dot

Re-save her from Users → Edit (uses `implode('.')`); check `user_perms` has no trailing `.`.

- [ ] Same panels as C (previously she got no Control Panel and no plugins panel).
- [ ] No empty panel.
- [ ] No drag.
- [ ] Positions from A apply.

### E. CSS

- [ ] Dashboard at viewport widths ~1800px, ~1600px, ~1400px, ~1000px, ~900px, ~700px: tile widths follow the media queries (15%, 25%, 33.3%, 30%, 20%, 25%).
- [ ] Spot-check a list page, media manager, theme manager, a preferences form with `input-*` classes for unexpected changes.

## 10. Not changed

`getperms()`, `simulateHasAdminPerms()`, `users.php` and all other core files;
`canEditLayout()`; `core_infopanel_icons()` beyond the zero-tile return; template
entries `core-infopanel-news`, `plug-infopanel-comments`, `addons-single-info-panel`,
`addons-single-icons-modules`; stored `core-flexpanel-order` data.
