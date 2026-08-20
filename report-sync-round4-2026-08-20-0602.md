# Sync round 4 — archive report

- **Repo:** `Jimmi08/e107-2.4.x-Lite` · **upstream:** `https://github.com/e107inc/e107`
- **Branch:** `sync-round4` (no prefix, no slash)
- **Branch point:** `main` = `c73b26aa0d5ee24084297aa628f42963cec8c6b4` ("Sync lancheck (#105)")
- **Branch HEAD after run:** `8e25082cc`
- **Baseline:** upstream `9eaf62a82`
- **Enumeration:** `git rev-list --topo-order --reverse 9eaf62a82..upstream/master` → 19 commits (6 merges, 13 real)
- **Run outcome:** completed the full enumeration. No stop, no failed hunks.

## Marker count at start

The task states an expected count of 189. Measured at branch point:

| grep | count |
|---|---|
| `grep -rn "LITE MODIFICATION\|LITE FEATURE" .` (the task's grep) | **122** |
| `grep -rn "LITE" .` excluding `.git/` | **189** |

The 189 figure corresponds to the plain `LITE` grep, not to the
`LITE MODIFICATION\|LITE FEATURE` grep in the task text. Both numbers are
**identical before and after this run** — see verification below. Reported here
so the maintainer can decide which grep the standing 189 baseline refers to; no
markers were touched either way.

## Commit-by-commit

Processed oldest first. Upstream SHAs below are all from `git rev-parse` /
`git log`, never hand-typed.

| # | upstream | subject | outcome | Lite commit |
|---|---|---|---|---|
| 1 | `a21f29278` | feat(siteinfo): let {SITELOGO} take a class parm | **APPLIED** | `c13b3c475` |
| 2 | `6065d3f8c` | Merge pull request #5960 | SKIP (merge commit) | — |
| 3 | `b59c97699` | feat(featurebox): let item templates print their own category | SKIP | — |
| 4 | `67232b803` | Merge pull request #5961 | SKIP (merge commit) | — |
| 5 | `db21fadff` | fix(news): make the News Grid menu's Featured setting take effect | **APPLIED** | `cde0633e6` |
| 6 | `1fe177f56` | refactor(news): drop the dead news-grid placeholder substitution | **APPLIED** | `af81542e3` |
| 7 | `39c6c2263` | Merge pull request #5962 | SKIP (merge commit) | — |
| 8 | `143f76e3d` | fix(menus): stop {FEATUREBOX} killing the Menu Manager | **APPLIED** | `d14d08bde` |
| 9 | `65d87b4d6` | fix(admin-ui): close the featurebox batch link's opening tag | **APPLIED** | `29d51f556` |
| 10 | `a81acde03` | fix(core): guard the other plugin language constants core prints | **APPLIED** | `c2b551ad5` |
| 11 | `7898dca07` | Merge pull request #5964 | SKIP (merge commit) | — |
| 12 | `86f96a396` | docs(featurebox): correct the {FEATUREBOX} example in sc_featurebox() | SKIP | — |
| 13 | `2d57e116e` | fix(featurebox): resolve an empty category preference from the site's data | SKIP | — |
| 14 | `d345ea85b` | fix(featurebox): stop loading an admin language file on the front end | SKIP | — |
| 15 | `816790015` | feat(featurebox): add a Menu Manager configuration form | SKIP | — |
| 16 | `8e6b53557` | feat(featurebox): render each menu placement's own parameters | SKIP | — |
| 17 | `6d060d57e` | Merge pull request #5966 | SKIP (merge commit) | — |
| 18 | `788b82be6` | chore(core): remove the dead admin copyright line and powered-by block | **APPLIED** | `8e25082cc` |
| 19 | `372947bce` | Merge pull request #5984 | SKIP (merge commit) | — |

**Applied: 7 · Skipped: 12 (6 merge commits + 6 featurebox-only) · Partial: 0 · Failed hunks: 0 · Stopped: none**

## Detail

### 1. `a21f29278` — feat(siteinfo): let {SITELOGO} take a class parm → `c13b3c475`

- Target: `e107_plugins/siteinfo/e_shortcode.php` → `eplugins/siteinfo/e_shortcode.php` (shipped).
- Both hunks applied cleanly (docblock on `sc_logo()`, `$parm['class']` appended
  to the default `logo img-responsive img-fluid` via `$tp->toAttribute()`).
- **Dropped:** `e107_tests/tests/unit/plugins/siteinfo/siteinfo_shortcodesTest.php`
  — Lite ships no test suite.
- No `LITE` markers in the file.

### 3. `b59c97699` — feat(featurebox): let item templates print their own category — SKIP

- Touches only `e107_plugins/featurebox/includes/item.php` and a test.
- `eplugins/featurebox/` does not exist in Lite. Checked for a rename: `ls eplugins/`
  gives `githubSyncLite navigation news page rss siteinfo tinymce4 user` — no
  featurebox equivalent under any name. Nothing to target.

### 5. `db21fadff` — fix(news): News Grid Featured setting → `cde0633e6`

- Target: `e107_plugins/news/e_menu.php` → `eplugins/news/e_menu.php` (shipped).
- One-line hunk applied cleanly: `$fields['feature']` → `$fields['featured']`,
  matching the key `e_news_tree::render()` actually reads.
- **Dropped:** `e107_tests/tests/unit/plugins/news/PluginNewsTest.php`.
- No `LITE` markers in the file.

### 6. `1fe177f56` — refactor(news): drop the dead news-grid placeholder substitution → `af81542e3`

- Target: `e107_handlers/news_class.php` → `ehandlers/news_class.php` (shipped).
- Hunk applied at line 544 (offset +1 vs upstream). The `$parmSrch`/`$parmReplace`
  pair is folded into the `str_replace()` call; the three deprecated tokens keep
  being stripped, so no behaviour change.
- **Marker judgement:** the file carries two `// LITE MODIFICATION (#84): news SEF
  via e107::url()` markers, at lines 412 and 1035. Each protects a single
  `$socialArray`/URL-construction line immediately below it. The hunk is at ~544,
  in `render_newsgrid()`, hundreds of lines from either marker and inside neither
  marked region. Not a collision — applied, both markers untouched and still 2 in
  the file after the change.

### 8. `143f76e3d` — fix(menus): stop {FEATUREBOX} killing the Menu Manager → `d14d08bde`

- Target: `e107_handlers/menumanager_class.php` → `ehandlers/menumanager_class.php` (shipped).
- Applied cleanly: bare `LAN_PLUGIN_FEATUREBOX_NAME` → `defset('LAN_PLUGIN_FEATUREBOX_NAME', 'Feature Box')`.
- Worth noting for Lite specifically: this is the *more* important of the
  featurebox-adjacent core fixes here, because Lite ships no featurebox plugin at
  all, so `LAN_PLUGIN_FEATUREBOX_NAME` is *never* defined on a Lite site. Any theme
  layout containing `{FEATUREBOX}` would have fatalled Admin > Menus on PHP 8
  unconditionally. `ethemes/bootstrap3/layouts/modern_business_home_layout.html`
  ships in Lite and contains `{FEATUREBOX}`.
- **Dropped:** `e107_tests/tests/unit/e_menuManagerTest.php`.
- No `LITE` markers in the file.

### 9. `65d87b4d6` — fix(admin-ui): close the featurebox batch link's opening tag → `29d51f556`

- Target: `e107_handlers/admin_ui.php` → `ehandlers/admin_ui.php` (shipped).
- Applied cleanly: missing `>` added to the `<a …>` start tag.
- `git apply` warned `has type 100644, expected 100755` — Lite's copy is not
  executable, upstream's is. Content-only patch; the mode was **not** changed,
  confirmed by `git show --stat` on the resulting commit (1 file, 1 +/1 -).
- No `LITE` markers in the file.

### 10. `a81acde03` — fix(core): guard the other plugin language constants core prints → `c2b551ad5`

- Targets: `e107_core/shortcodes/batch/signup_shortcodes.php` →
  `ecore/shortcodes/batch/signup_shortcodes.php`, and
  `e107_handlers/admin_ui.php` → `ehandlers/admin_ui.php`. Both shipped.
- All four hunks applied cleanly. Same 100644/100755 mode warning as above; mode
  left alone.
- Same Lite relevance as #8: `LAN_PLUGIN_SOCIAL_XUP_*` (social plugin) and
  `LAN_PLUGIN_FEATUREBOX_*` are constants from plugins Lite does not ship, so the
  `defset()` guards matter more on Lite than upstream.
- No `LITE` markers in either file.

### 12–16. `86f96a396`, `2d57e116e`, `d345ea85b`, `816790015`, `8e6b53557` — SKIP

All five touch only `e107_plugins/featurebox/` (`e_shortcode.php`,
`featurebox_menu.php`, `e_menu.php`) plus `e107_tests/`. No featurebox plugin in
Lite under that or any other name; no target for any hunk.

### 18. `788b82be6` — chore(core): remove the dead admin copyright line and powered-by block → `8e25082cc`

- Targets: `e107_core/templates/admin_template.php` and
  `e107_core/templates/footer_default.php` → `ecore/templates/…`. Both shipped.
- Both hunks applied cleanly.
- Deliberately checked against section 6 before applying: this commit **removes**
  the e107.org powered-by markup, it does not reintroduce it. It is upstream
  moving toward Lite's position, not against it. The removed block was already
  fully commented out (`// echo "<div …>Proudly powered by … e107 …"`), so no
  live output changes on Lite either way.

## Divergences that markers cannot show — verified intact at end of run

| item | check | result |
|---|---|---|
| `install.php` `$this->stats()` | `grep -n 'stats()' install.php` | both call sites still `//$this->stats(); LITE MODIFICATION phone-home stats stripped…` at lines 2018 and 2053 — **intact** |
| `coreUpdateAvailable()` | `ehandlers/e107_class.php:6954` | still `return false;` under its LITE MODIFICATION block — **intact** |
| `eadmin/ver.php` | `cat eadmin/ver.php` | `$e107info['e107_version'] = "2.4.0.3 (lite)";` — **no upstream bump applied** |
| `paragonie/constant_time_encoding` | `ehandlers/vendor/composer/installed.json` | `v2.6.3` — **pinned, untouched** |
| `e_session_db` shims | `grep -c ReturnTypeWillChange ehandlers/session_handler.php` | **7** — unchanged, not modernised |

None of the seven applied commits touched any of these files.

## PHP 7.4

No upstream commit in this run introduced PHP-8-only syntax. Scanned every added
line across the whole branch diff for `match(`, `str_contains`,
`str_starts_with`, `str_ends_with`, `array_is_list`, `?->`, `readonly`, `enum`,
`: mixed`, `: never`, and union return types — **no hits**. The one construct
worth naming is `defset('CONST', 'fallback')`, which is e107's own helper, not a
language feature, and is 7.4-safe. Nothing here needs a maintainer decision on
PHP version grounds.

## Verification output

```
$ git diff --name-only c73b26aa0d5ee24084297aa628f42963cec8c6b4..HEAD
ecore/shortcodes/batch/signup_shortcodes.php
ecore/templates/admin_template.php
ecore/templates/footer_default.php
ehandlers/admin_ui.php
ehandlers/menumanager_class.php
ehandlers/news_class.php
eplugins/news/e_menu.php
eplugins/siteinfo/e_shortcode.php
```

Eight files, all expected — every one is the Lite-remapped target of an applied
hunk. No file outside the remap table, no test/CI paths.

```
$ php -l on each of the eight
No syntax errors detected in ecore/shortcodes/batch/signup_shortcodes.php
No syntax errors detected in ecore/templates/admin_template.php
No syntax errors detected in ecore/templates/footer_default.php
No syntax errors detected in ehandlers/admin_ui.php
No syntax errors detected in ehandlers/menumanager_class.php
No syntax errors detected in ehandlers/news_class.php
No syntax errors detected in eplugins/news/e_menu.php
No syntax errors detected in eplugins/siteinfo/e_shortcode.php
```

```
$ grep -rn "LITE MODIFICATION\|LITE FEATURE" . | wc -l
122          (122 before the run — unchanged)

$ grep -rn "LITE" . | grep -v '^./.git/' | wc -l
189          (189 before the run — unchanged)
```

```
$ git status --short
             (clean)

$ find . -name '*.rej' -o -name '*.orig'   (excluding .git)
             (nothing)
```

## Nothing needs a maintainer decision

No failed hunks, no marker collision, no PHP-8 syntax, no security commit in the
enumeration. The only thing worth the maintainer's eye is the 122 vs 189 marker
grep discrepancy noted at the top, which is a question about which baseline
figure the task means — not a change this run made.

## Note on the `HEAD:` line in the status file

`status-2026-08-20-0602.md` records `HEAD: 8e25082cc`, the last **sync** commit —
the tip of the actual synced code. The branch's literal tip is one commit further
on: a docs-only `lite:` commit adding these three report files, which cannot
contain its own SHA. `git diff --name-only <branch-point>..8e25082cc` is the
eight-file list verified above; the docs commit adds only the three
`*-2026-08-20-0602.md` files at the repo root and touches no code.
