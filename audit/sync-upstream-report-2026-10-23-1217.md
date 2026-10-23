# Sync report (round 3) — e107-2.4.x-Lite ← e107inc/e107 master

(Work in progress — the remaining sections are added in later commits of this branch.)

## rss: Lite modifications (inventory before removal)

Compared: `eplugins/rss` in `main` (`7296f21be`) with upstream `e107_plugins/rss_menu` at the new baseline
`4367279f1`. Pure mapping noise is left out: `rss` ↔ `rss_menu` in `e107::url()`, `isInstalled()`, `getScBatch()`,
`getTemplate()`, `tablerender()` id, `e_PLUGIN` / `e_PLUGIN_ABS` path literals, the `rss_addons::folders()` folder name,
class names `rss_url` / `rss_shortcodes` / `rss_setup` / `rss_rss`, `rss_path` value `'rss'` in `rss_setup::upgrade_post()`
and the doc-comment mentions. Every function named below was read in full in both versions.

### A. Lite modifications

| # | Lite file:line | Marker | What Lite does differently |
|---|---|---|---|
| 1 | `rss.php:463–470` (`rssCreate::buildRss()`, `case 1` RSS 0.92) | `// LITE MODIFICATION: RSS 0.92 <link> uses SITEURL, not $pref['siteurl'] — deliberate Lite modernisation` | Channel `<link>` is the `SITEURL` constant instead of the raw `siteurl` pref. |
| 2 | `rss.php:494–496` (`case 2` RSS 2.0, head) | `// LITE MODIFICATION: sitebutton via getConfig()+thumbUrl(w=500) and <link> SITEURL — replaces upstream SITEBUTTON / $pref['siteurl']` | `$sitebutton` is built with `thumbUrl()` from the `sitebutton` pref instead of from `SITEBUTTON`. **`$sitebutton` is not used afterwards in either version** (dead assignment on both sides). Channel `<link>` (`:509`) is `SITEURL`. |
| 3 | `rss.php:532–541` (`case 2`, `<image>`) | `// LITE MODIFICATION: image sitebutton via getConfig()+thumbUrl(w=500), <link> SITEURL, no upstream hardcoded 88x31` | Image URL via `thumbUrl($path, 'w=500&x=1', true, true)` (resized, raw) instead of `thumbUrl($path, array(), false, true)`; `<link>` is `SITEURL`; no `<width>88</width><height>31</height>`. |
| 4 | `rss.php:575–580` (`case 2`, item `<category>`) | `// LITE MODIFICATION: category tag rendering differs from upstream (domain attribute optional)` | Emits `<category>` whenever `category_name` is set; the `domain` attribute only when `$catlink` is set. Upstream emits the tag only when both are set. |
| 5 | `rss.php:636–643` (`case 3` RDF) | `// LITE MODIFICATION: RDF rdf:about/<link> use SITEURL, not $pref['siteurl'] — deliberate Lite modernisation` | `rdf:about` and `<link>` use `SITEURL`. |
| 6 | `rss.php:724–729` (`case 4` Atom, `<logo>`) | `// LITE MODIFICATION: <logo> sitebutton via getConfig()+thumbUrl(w=500) — replaces upstream SITEBUTTON handling` | `<logo>` is `thumbUrl($path, 'w=500&x=1', true, true)` of the `sitebutton` pref instead of `SITEBUTTON` (absolute when it contains `http:`, else `SITEURL` + path). |
| 7 | `admin_prefs.php:264–265` (`rss_form_ui::rss_url()`, `read`) | `// LITE MODIFICATION: feed link opens in a new tab (target='_blank') — deliberate, keep on sync` | Feed link in the admin list has `target='_blank'`. |
| 8 | `rss_setup.php:28–50` (`rss_setup::install_post()`) | `// LITE MODIFICATION: seed the default "news" feed row only when the news plugin is actually installed. …` | The default `news` feed row is inserted only `if (e107::isInstalled('news'))`; upstream inserts it unconditionally. Lite also drops the commented `//$mes->addInfo(print_a($insert,true));` line. |
| 9 | `rss_addons.php:203–208` (`rss_addons::loadLan()`) | `// LITE: Lite auto-loads the rss admin LAN pack via e107::lan('rss', true) …` | `e107::lan('rss', true)` (loads `rss/languages/English_admin.php`) instead of `includeLan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php')` guarded by `is_readable()`. |
| 10 | `admin_prefs.php:30`, `rss.php:52`, `rss_menu.php:20`, `rss_setup.php:12` | unmarked | Same LAN change as #9: `e107::lan("rss", true)` instead of `e107::includeLan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php')`. Follows from the Lite LAN file name (`languages/English_admin.php`, see C). |
| 11 | `plugin.xml:2–4` | `<!-- LITE MODIFICATION: Lite plugin metadata (version/date/compatibility/author), never take upstream -->` | See D. |

Side by side (short):

```php
// 1 / 5 — Lite                                   // upstream
<link>" . SITEURL . "</link>                       <link>".$pref['siteurl']."</link>
<channel rdf:about=\"".SITEURL."\">                <channel rdf:about=\"".$pref['siteurl']."\">

// 2 — Lite
$path = e107::getConfig()->get('sitebutton');
$sitebutton = e107::getParser()->thumbUrl($path, 'w=500&x=1', true, true);
// 2 — upstream
$sitebutton = (strpos(SITEBUTTON, "http:") !== false ? SITEBUTTON : SITEURL.str_replace("../", "", SITEBUTTON));

// 3 — Lite                                                    // upstream
$imgPath = …->thumbUrl($path, 'w=500&x=1', true, true);         $imgPath = …->thumbUrl($path, array(), false, true);
<link>". SITEURL ."</link>                                      <link>".$pref['siteurl']."</link>
                                                                <width>88</width> <height>31</height>

// 4 — Lite
if ($value['category_name'])
{
    $domain = $catlink ? " domain='$catlink'" : '';
    echo "<category$domain>" . $tp->toRss($value['category_name']) . "</category>\n";
}
// 4 — upstream
if($value['category_name'] && $catlink)
{
    echo "<category domain='".$catlink."'>".$tp->toRssForAuthor($value['category_name'], 0)."</category>\n";
}

// 6 — Lite                                                    // upstream
<logo>" . $imgPath . "</logo>                                    <logo>".(strpos(SITEBUTTON, "http:") !== false ? SITEBUTTON : SITEURL.str_replace("../", "", SITEBUTTON))."</logo>

// 7 — Lite                                                    // upstream
"<a target='_blank' href='" . $link . "'>"                      "<a href='" . $link . "'>"

// 8 — Lite                                                    // upstream
if (e107::isInstalled('news')) { $insert = …; insert; add() }   $insert = …; insert; add()   (always)

// 9 / 10 — Lite                                               // upstream
e107::lan('rss', true);                                         e107::includeLan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php');
```

### B. Differences that are not Lite modifications (upstream changes after the previous baseline `7ce7a121c`)

These differences exist only because `main` carries the previous baseline; they are not Lite code.

| Lite file:line | Upstream commit | Difference |
|---|---|---|
| `rss.php:218`, `:317` | `893d5076c` fix(parser): expand shortcodes only in text an administrator wrote | Upstream adds `author_id` to `rssCreate::ITEM` and to each item. |
| `rss.php:481–484`, `:561`, `:568`, `:572`, `:579`, `:589`, `:609`, `:677–682`, `:741–745`, `:757–762`, `:812`, `:829` | `893d5076c` | Upstream renders titles, descriptions, `content:encoded`, authors, categories and custom tags with `$tp->toRssForAuthor($text, $authorId)` (shortcodes only for administrator-written text); Lite still uses `$tp->toRss()`. |
| `e_rss.php:146–161` (`whereClassPermits()`), `:178`, `:190`, `:203–205`, `:218` | `53e50d6bc` fix(news): show news whose visibility excludes a class | Upstream uses `\e107\Userclass\Membership::current()->predicate()` for the class columns; Lite uses `e_CLASS_REGEXP` / `whereIn(USERCLASS_LIST)`. |
| `e_rss.php:190–195`, `:203–207`, `:218–219` | `2fd757cea` fix(comments): ask comment switches and the member list before publishing | Upstream also requires `news_allow_comments = 0`, `download_comment > 0`, `page_comment_flag > 0`. |
| `e_rss.php:210` | `2f22061c5` fix(comments): publish no forum poll's comments in the menu or the feed | Upstream adds `poll_type = 1`. |
| `plugin.xml:3` | `893d5076c` | Upstream metadata `date="2026-10-16"` (Lite metadata kept by marker anyway). |

### C. Lite-only files and file names

- `README.md` (18 lines) — Lite-only: "e107 RSS Menu plugin commissioned by hpkizi.sk o.z. and jointly open sourced",
  customised version of the core RSS Menu plugin, PHP 7.4–8.2, "Recommended for e107 Lite 2.3.4", development disclaimer.
- `languages/English_admin.php` — byte-identical to upstream `languages/English_admin_rss_menu.php` (only the file name
  differs; Lite loads it with `e107::lan('rss', true)`).
- Identical to upstream: `e_meta.php`, `e_url.php`, `rss_resolver.php`, `rss_shortcodes.php` (mapping only),
  `languages/English_global.php`, `rss_sql.php`, `templates/rss_template.php`, the six images.

### D. Lite `plugin.xml` metadata

| | Lite | upstream `4367279f1` |
|---|---|---|
| `version` | `2.1` | `1.4` |
| `date` | `2026-08-01` | `2026-10-16` |
| `compatibility` | `2.4` | `2.0` |
| `<author>` | `name="Jimmi" url="https://www.e107sk.com"` | `name="e107 Inc." url="https://e107.org"` |
| marker | `<!-- LITE MODIFICATION: Lite plugin metadata (version/date/compatibility/author), never take upstream -->` | — |

Name, LAN keys, description, category, copyright, adminLinks and pluginPrefs are the same. Lite ends the file with a
newline, upstream does not.
