# TinyMCE 4: audit of paste filtering and HTML sanitisation

- Date: 2026-10-26 13:12 UTC
- Base: `main` @ `f09c7400f8ea0540c1e3012de26d8313b09e8b1f` (commit date 2026-10-23 19:36:44 +0200, "Merge pull request #117 from Jimmi08/sync-upstream-2026-10-23")
- Branch: `audit-tinymce4-paste-filtering`
- Scope: read-only. No code was changed.

## 0. Conventions and caveats

- **Paths.** This is the "Lite" fork. The task names `e107_plugins/tinymce4`, but that directory does not exist here. The tree uses `eplugins/`, `ehandlers/`, `eadmin/`, `ecore/` and `ethemes/`. Every path below was checked in the working tree.
- **[READ]** means I read the whole function and at least one of its callers.
  **[ASSUMED]** means I inferred it and did not verify it.
  **[RUNTIME]** means a scratch experiment outside the repo (see §7).
- **The TinyMCE core is not in this repository.** `eplugins/tinymce4/` holds only e107 glue code and the local plugins `e107`, `bbcode`, `compat3x`, `smileys` and `example`. The core and its `paste` plugin come from cdnjs at runtime:
  `eplugins/tinymce4/e_footer.php:57` → `https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.11/tinymce.min.js`.
  To read the paste plugin source I fetched the npm package `tinymce@4.9.11` into a scratch directory. It is not committed. The `tinymce.js` header reads `// 4.9.11 (2020-07-13)`, and `majorVersion: '4'` / `minorVersion: '9.11'` are at `tinymce.js:26383-26384`.
  **[ASSUMED]** The cdnjs build is identical to the npm build. I could not compare them byte for byte because the sandbox proxy refused the cdnjs request (HTTP 403).
- The npm source line numbers below are prefixed `npm:` (for example `npm:plugins/paste/plugin.js:349`). They refer to the unminified npm 4.9.11 files, not to anything in the repo.

## 1. Pipeline overview

```
 [browser clipboard]
      │  Ctrl+V
      ▼
 paste plugin (TinyMCE 4.9.11, CDN)                       npm:plugins/paste/plugin.js
  1 registerEventHandlers → 'paste' event, reads text/html           :1755-1862
  2 pasteHtml$1 → ProcessFilters.process                             :1615-1621, :700-704
  3 isWordContent(html)?  (Word OR Google Docs OR OneNote)           :348-350
      yes → WordFilter.preProcess → filterWordContent (own schema)   :553-662
      no  → (webkit only) removeWebKitStyles on PastePreProcess      :2260-2303, :2309-2311
  4 PastePreProcess event → preProcess$1 (editor.schema parse)       :691-699, :667-679
  5 PastePostProcess event (only if listeners)                       :686-690
  6 SmartPaste.insertContent → editor.insertContent(html,{paste:1})  :707-763
      ▼
 TinyMCE core insertHtmlAtCaret                           npm:tinymce.js:11026-11145
  7 fires BeforeSetContent ──► e107 plugin hook            eplugins/tinymce4/plugins/e107/plugin.js:22-26
      synchronous AJAX POST mode=tohtml                    plugins/e107/plugin.js:232-261
      ──► parser.php::toHTML() → $tp->toHTML($c, true, 'WYSIWYG')  plugins/e107/parser.php:117-174
      ◄── response replaces the pasted HTML
  8 editor.parser.parse() with editor.schema (verify_html:false ⇒ valid_elements '*[*]')
  9 reduceInlineTextElements (paste_merge_formats)          npm:tinymce.js:10932-10944
      ▼
 Editor DOM
      │  source view / form submit (editor.save → getContent)
      ▼
 10 PostProcess(get) ──► e107 plugin hook                 plugins/e107/plugin.js:39-50
      synchronous AJAX POST mode=tobbcode → parser.php::toDB()       parser.php:178-208
      post_html user: imgToBBcode + wrap "[html]…[/html]"           parser.php:184-196
      ▼
 textarea value "[html]…[/html]" is POSTed
      ▼
 11 server: model sanitize 'str' → $tp->toDB(…,'model')    ehandlers/model_class.php:3024-3029
      (comments/forum call $tp->toDB() directly)            ehandlers/comment_class.php:937, eplugins/forum/forum_class.php:1316/1363/1656
      toDB → preFilter → cleanHtml()/cleanHtmlWithoutScript() ehandlers/e_parse_class.php:665-817, 7656-7939
      ▼
 DB
```

So the editor makes **two server round trips per paste or edit cycle**: `tohtml` on every `BeforeSetContent`, which includes each paste, and `tobbcode` on every `getContent`, which includes the source view and submit. Both run through `plugins/e107/parser.php`, and that endpoint is gated by `wysiwyg::isAllowed()` (`wysiwyg_class.php:22-25`, `parser.php:27-32`) [READ].

## 2. A. Configuration

### 2.1 Where the init config is built [READ]

| Step | File:line | What |
|---|---|---|
| Loader | `eplugins/tinymce4/e_footer.php:39` | Editor JS is loaded only when `e107::wysiwyg(null,true)==='tinymce4' && check_class($pref['post_html'])`, or on the plugin's own `admin_config.php`. |
| Core | `e_footer.php:57` | TinyMCE 4.9.11 from cdnjs. |
| Config script | `e_footer.php:62-69` | `wysiwyg.php`, with `?config=<e_TINYMCE_TEMPLATE>` if that constant is defined. |
| Entry | `eplugins/tinymce4/wysiwyg.php:330-336` | `new wysiwyg()` → `renderConfig($_GET['config'])`. |
| Builder | `eplugins/tinymce4/wysiwyg_class.php:236-885` `getConfig()` | Picks an XML profile (`:241-292`), copies every XML child into the config (`:317-328`), then appends hardcoded keys (`:330-718`). |
| Output | `wysiwyg_class.php:739-759`, `:27-49` | Emits `key: value` JS via `convertBoolean()` (`:197-232`). Strings that start with `{`, `[` or `function(` are emitted raw. |

### 2.2 Every option relevant to paste and filtering

| Option | Value | Source | File:line |
|---|---|---|---|
| `paste_as_text` | `true` only if plugin pref is set; default pref `0` | admin pref (`tinymce4` plugin) | `wysiwyg_class.php:330-335`; default `plugin.xml:15`; UI `admin_config.php:59` |
| `verify_html` | `false` (overwrites any XML value; set after the XML loop) | hardcoded | `wysiwyg_class.php:352` |
| `schema` | `"html5"` | hardcoded | `wysiwyg_class.php:716` |
| `element_format` | `"html"` | hardcoded | `wysiwyg_class.php:717` |
| `convert_fonts_to_spans` | `false` | hardcoded | `wysiwyg_class.php:695` |
| `extended_valid_elements` | `i[*], object[*],embed[*],bbcode[*]` | XML profile | `templates/mainadmin.xml:16`, `templates/admin.xml:9` |
| | `i[*], bbcode[*]` | XML profile | `templates/member.xml:13`, `templates/public.xml:13` |
| `media_filter_html` | `false` | XML (mainadmin only) | `templates/mainadmin.xml:12` |
| `convert_urls` | `false` | XML (mainadmin, admin) | `templates/mainadmin.xml:14`, `templates/admin.xml:11` |
| `plugins` (incl. `paste`) | see 2.3 | XML, filtered by `filter_plugins()` | `wysiwyg_class.php:319-322`, `:1030-1071` |
| `external_plugins` | `e107` (+`example compat3x` for admin.xml) | XML | `templates/*.xml:7` → `wysiwyg_class.php:742-746`, `:168-193` |
| `valid_elements` | **not set** | — | — |
| `invalid_elements` | **not set** (only inside the dead block below) | — | — |
| `valid_styles` / `invalid_styles` / `valid_classes` | **not set** | — | — |
| `paste_preprocess` / `paste_postprocess` | **not set** | — | — |
| `paste_webkit_styles`, `paste_remove_styles_if_webkit`, `paste_retain_style_properties`, `paste_word_valid_elements`, `paste_data_images`, `paste_merge_formats`, `paste_enable_default_filters`, `smart_paste` | **not set**, so TinyMCE defaults apply (`npm:plugins/paste/plugin.js:72-117`) | — | — |

**Dead code.** `wysiwyg_class.php:766-884` is one `/* … */` block. It contains `invalid_elements => 'font,align,script,applet'`, `auto_cleanup_word`, `paste_auto_cleanup_on_paste`, `paste_convert_headers_to_strong` and other TinyMCE 3-era settings. None of it is emitted [READ].

**Effect of `verify_html:false` in 4.9.11.** In the `Schema()` constructor, `if (settings.verify_html === false) settings.valid_elements = '*[*]';` (`npm:tinymce.js:5645-5647`) [READ]. The editor schema therefore accepts every element with every attribute. `extended_valid_elements` adds nothing on top of that. The old `strong/b` and `em/i` renaming is not applied (`npm:tinymce.js:5856-5862`, which is skipped when `valid_elements` is set and also for `schema:'html5'`). `valid_styles` is compiled separately (`npm:tinymce.js:5648`), so it would still work if a profile set it. **[ASSUMED]** I did not test that at runtime.

### 2.3 Profiles and contexts [READ]

**How a profile is chosen** (`wysiwyg_class.php:241-292`):

1. If `?config=X` is passed, which happens only when a page defines `e_TINYMCE_TEMPLATE` (`e_footer.php:64-67`), the profile is `X.xml`. The name is sanitised with `$tp->filter(…,'file')`. `mainadmin` and `admin` are downgraded to `public` for users who lack the right level (`:273-276`).
2. Otherwise the profile follows the role: main admin (`getperms('0')`) → `mainadmin.xml`; `ADMIN` → `admin.xml`; `USER` → `member.xml`; otherwise `public.xml`.
3. File lookup: `THEME/templates/tinymce/<name>.xml` first, then `eplugins/tinymce4/templates/<name>.xml`, then `public.xml` (`:278-292`). No bundled theme ships such an override: `ethemes/*/templates/tinymce` does not exist.

**There are no per-content-area profiles.** `e_form::bbarea($name,$value,$template,…)` takes a `$template` such as `'news'`, `'page'`, `'comment'` or `'forum'`. That argument reaches only `e107::getBB()->renderButtons()`, the bbcode toolbar, and only when the user lacks `post_html` (`ehandlers/form_handler.php:2462-2580`, notably `:2559-2567`). It never reaches TinyMCE. TinyMCE attaches to every `.e-wysiwyg` (`wysiwyg_class.php:305`), so one config applies to every editor on the page.

The only code that sets `e_TINYMCE_TEMPLATE` is the forum, to `member` or `public` (`eplugins/forum/shortcodes/batch/post_shortcodes.php:194-197`, `eplugins/forum/shortcodes/batch/view_shortcodes.php:1320-1323`). In practice it changes nothing, because role-based selection would already pick `member` or `public` for non-admins.

**Profile differences** (all four include `paste`; none sets any paste or filter option):

| Profile (`templates/…`) | name attr | `paste` plugin | `code` (source) plugin | `image`/`media` | external_plugins | extended_valid_elements | other |
|---|---|---|---|---|---|---|---|
| `mainadmin.xml` | Main Admin | yes (`:3-4`) | yes, toolbar `code` (`:6`) | image+media | `e107` | `i[*], object[*],embed[*],bbcode[*]` | `media_filter_html=false`, `convert_urls=false`, `autosave` |
| `admin.xml` | Administrators | yes (`:3-4`) | plugin yes (menu only, no toolbar button) | image+media | `e107 example compat3x` | same as mainadmin | `visualblocks`, `save` |
| `member.xml` | Member | yes (`:3-4`) | no | image+media | `e107` | `i[*], bbcode[*]` | — |
| `public.xml` | Public | yes (`:3-4`) | no | no image, no media | `e107` | `i[*], bbcode[*]` | — |

The hardcoded keys appended after the XML are the same for every profile, with two exceptions: `templates` is replaced by snippets for `ADMIN` (`:728-731`), and the `smileys` external plugin is added when `smiley_activate` is on (`:177-180`).

**Where `bbarea` (and so potentially TinyMCE) appears** [READ, from the `bbarea(` and `'type' => 'bbarea'` call sites]:

| Area | Call site | Save path |
|---|---|---|
| News body / extended | `eadmin/newspost.php:1647`, `:1652` | admin-ui model, `data=>'str'` (`newspost.php:452-453`) → `toDB(…,'model')` |
| News subheader pref | `eadmin/newspost.php:512`, `:1309` | prefs |
| Custom pages / menus | `eadmin/cpage.php:530`, `:555`, `:699` | model `str` → `toDB(…,'model')` |
| Welcome message | `eadmin/wmessage.php:89` | model `str` |
| Mailout body | `eadmin/mailout.php:355` | not traced |
| Maintenance text | `eadmin/ugflag.php:96` | prefs, not traced |
| Featurebox | `eplugins/featurebox/admin_config.php:276` | model, not traced |
| PM (admin) | `eplugins/pm/admin_config.php:110` | model `str` |
| Forum post / quick reply / rules | `eplugins/forum/shortcodes/batch/post_shortcodes.php:238`, `view_shortcodes.php:1371`, `forum_admin.php:594-631,1172` | `$tp->toDB()` in `forum_class.php:1316,1363,1656` |
| Downloads | `eplugins/download/includes/admin.php:1450,2133,2335,2339` | not traced |
| Comments | `ecore/shortcodes/batch/comment_shortcodes.php:363`, only for `{COMMENT_INPUT=bbcode}` | `comment_class.php:937` `$tp->toDB()`; edits go through `toText()` first (`:687`) |
| Signature | `ecore/shortcodes/batch/usersettings_shortcodes.php:309`, `signup_shortcodes.php:568` | not traced |
| User extended rich text | `ehandlers/user_extended_class.php:1479` | not traced |
| Admin-ui generic `bbarea` field | `ehandlers/form_handler.php:7046-7049` | model, by `data` type |

In every one of these areas the TinyMCE config depends only on the user's role (§2.3).

### 2.4 Version and "Paste from Word" [READ in npm 4.9.11 source]

- Bundled at runtime: **TinyMCE 4.9.11** from CDN. The repo has no local copy; earlier pinned versions are commented out at `e_footer.php:43-56`.
- The paste plugin registers **only** the `pastetext` button and menu item (`npm:plugins/paste/plugin.js:2327-2344`). There is no `pasteword` button. Word cleanup runs automatically whenever `isWordContent()` matches. The default `edit` menu in the modern theme lists `pastetext` (`npm:themes/modern/theme.js:756`). No e107 toolbar includes `pastetext`.

## 3. B. Paste behaviour

### 3.1 Detection [READ] — `npm:plugins/paste/plugin.js:348-350`

```js
/<font face="Times New Roman"|class="?Mso|style="[^"]*\bmso-|style='[^'']*\bmso-|w:WordDocument/i
 || /class="OutlineElement/  || /id="?docs\-internal\-guid\-/
```

- **Google Docs** (`id="docs-internal-guid-…"`) takes the **Word branch**.
- **MS Word** (`class="MsoNormal"`, `mso-*`) takes the Word branch.
- **LibreOffice Writer** takes the non-Word branch, unless its HTML happens to contain `<font face="Times New Roman…` (the regex is a prefix match) or an `mso-` style. **[ASSUMED]** I am inferring what LibreOffice exports; I did not capture a real clipboard.

### 3.2 Word branch: `filterWordContent()` (`:553-658`) and `filterStyles()` (`:486-552`) [READ]

1. Regex pre-strip (`:559-581`): removes `<br class="Apple-interchange-newline">`, the **opening** `<b … id="docs-internal-…">`, comments, and `<img|meta|link|style|\w:\w+ …>` tags. That last pattern covers namespaced tags such as `<o:p>` (only the tag itself, not its content). It also maps `<s>` to `<strike>` and handles `mso-spacerun` spans.
2. It builds a **private schema** from `paste_word_valid_elements`, which defaults to `-strong/b,-em/i,-u,-span,-p,-ol,-ul,-li,-h1..h6,-p/div,-a[href|name],sub,sup,strike,br,del,table[width],tr,td[…],th[…],thead,tfoot,tbody` (`:108-111`), and adds `class` and `style` to every element (`:584-594`). In that schema, `b` parsed from the source is renamed to `strong`, `i` to `em`, and `div` to `p`.
3. Style filter (`filterStyles`):
   - `mso-*` properties are dropped, after their side effects for lists and comments are applied.
   - `font-weight` and `font-style` are **kept whenever the value is not `normal`, regardless of any whitelist** (`:515-520`).
   - Every other property is kept only if `paste_retain_style_properties` lists it, or is `'all'`. e107 does not set it, so **all other properties are dropped**: font-size, font-family, color, background, vertical-align, text-decoration, white-space.
   - If the kept `font-weight` matches `/bold/`, the property is removed and the node is wrapped in a **new `b` node**. If `font-style` matches `/italic/`, the node is wrapped in a new **`i`** node (`:539-546`). These wrapper nodes are created after parsing, so the `strong/b` and `em/i` renaming does not apply to them. They stay `b` and `i`.
   - A `span` left with no attributes is unwrapped (`:599-601`).
4. `class` attributes are removed (`:605-614`), `del` is removed, and `a` elements are restricted.
5. Then `preProcess$1` reparses with `editor.schema` (`*[*]`) (`:667-679`). `removeWebKitStyles` is **skipped** for Word content (`:2261-2263`).

**Core detail that decides bold** [READ]: `DOMUtils.parseStyle` (Styles.parse) rewrites `font-weight:700` to `bold` (`npm:tinymce.js:6172-6173`). Only `700` gets this treatment. `600`, `800` and `900` stay numeric, fail `/bold/`, and remain as inline `style="font-weight: 600"`.

### 3.3 Non-Word branch [READ]

- **WebKit/Blink (Chrome, Edge, Safari):** `removeWebKitStyles` (`:2260-2303`) runs on `PastePreProcess`. With `paste_webkit_styles` unset it executes `content.replace(/(<[^>]+) style="([^"]*)"([^>]*>)/gi,'$1$3')`, which removes **every** style attribute. Spans styled bold or italic lose their formatting. Real `<b>`, `<i>`, `<strong>` and `<em>` elements survive. This is the default `paste_remove_styles_if_webkit:true` behaviour (`:93-95`).
- **Firefox:** that quirk is not registered (`:2309-2311`), so style attributes pass through untouched, because the editor schema is `*[*]`.
- Then `preProcess$1` runs with `editor.schema` (`*[*]`): every element and every attribute survives on the client, including `<font>`.

### 3.4 Per-source result (client side, before and after the e107 server round trip)

All rows: **from code reading, not runtime tested** in e107. §7 adds an isolated runtime check of the TinyMCE-only part, which agrees with these rows.

| Source | Branch | Inline styles after paste (client) | Elements after paste (client) | `<span style="font-weight:700">` | `<span style="font-style:italic">` | After save (`cleanHtml`, §4) |
|---|---|---|---|---|---|---|
| Google Docs (sample from the task) | Word (`docs-internal-guid`) | Only `font-weight`/`font-style` with non-`normal` values survive. Google's `font-weight:400` survives as `style="font-weight: 400;"`. Everything else is dropped. | `p`, `span`, `b`, `i`, lists, tables, `a[href]`. Outer `<b id=docs-internal…>` opening tag removed. | → wrapped in **`<b>`**, style removed (700→bold) | → wrapped in **`<i>`**, `font-style` removed; `<span style="font-weight: 400;">` stays inside | `b`, `i`, `span` allowed; `style` kept as-is |
| MS Word | Word | Same rule: only non-`normal` font-weight/style; `mso-*` and others dropped | Source `<b>`→`<strong>`, `<i>`→`<em>`, `<div>`→`<p>`; `<o:p>` tag removed; `class` removed | → `<b>` | → `<i>` | allowed |
| LibreOffice Writer (no Times New Roman / mso markers) | non-Word | **Chrome:** all `style` attributes removed. **Firefox:** all styles kept. | Everything kept on the client, incl. `<font face=…>`; `<b>`/`<i>` kept as-is | Chrome: plain `<span>` (bold **lost**); Firefox: span with style (bold kept as style) | Chrome: plain `<span>` (italic **lost**); Firefox: kept as style | **`<font>` is not in the allowlist, and `cleanHtml` deletes the element *together with its text*** (§4.2) |

### 3.5 Why "bold survives, italic does not" — my code reading does **not** reproduce the reported symptom

My reading predicts that the task's Google Docs sample comes out as

```html
<span style="font-weight: 400;">normal </span><b>bold </b><i><span style="font-weight: 400;">italic</span></i>
```

So **both bold and italic survive** in the TinyMCE layer, and the `e107` round trip through `parser.php` (`toHTML` with `WYSIWYG` and `toDB`) contains no step that removes `<i>` [READ: `e_parse::renderText()` `ehandlers/e_parse_class.php:2071-2311`, `processModifiers()` `:8421-8644`, `WYSIWYG` supermodifier `:247-250` (`hook=false`, `link_click=false`, `retain_nl=true`), `parseBBTags()` `:7479-7499`, `e_bbcode::parseBBCodes()` `ehandlers/bbcode_handler.php:276-309`, `imgToBBcode()` `:1186-1284`, `parser.php::toDB()` `:178-208`].

The isolated runtime check in §7 agrees: with an e107-equivalent TinyMCE config in Chromium, italic **does** survive as `<i>`.

The diagnosis "the paste filter keeps bold and drops italic" therefore does not hold for this sample, this TinyMCE build and this e107 code. **I cannot explain the reported behaviour from the code.** It has to come from something I have not seen. Candidates, all **[ASSUMED / unverified]**:

1. The real clipboard HTML differs from the sample. For example, bold may be `font-weight:700` while italic sits on an element that the Word branch removes, or italic may be expressed differently.
2. A theme override `THEME/templates/tinymce/<profile>.xml` on the affected site adds options I have not seen. The lookup is at `wysiwyg_class.php:278-292`.
3. The site is not running this branch: a different `e_footer.php` or a different CDN version.
4. Something on the site modifies the content during the `tohtml` or `tobbcode` AJAX round trip, such as mod_security or a proxy (see the FIXME at `plugins/e107/plugin.js:240`).
5. The browser or extension differs.

**Suggested next diagnostic step (needs you, the site owner):** in DevTools → Network, paste once, then capture (a) the request and response body of the `parser.php` POST with `mode=tohtml`, and (b) the clipboard HTML itself (`document.addEventListener('paste', e => console.log(e.clipboardData.getData('text/html')))`). Those two captures show exactly which layer drops italic.

### 3.6 Does e107 add its own paste hooks? [READ]

- There is no `paste_preprocess` or `paste_postprocess`, and no `PastePreProcess` or `PastePostProcess` listener, in `eplugins/tinymce4/**`.
- e107 does hook **`BeforeSetContent`** and **`PostProcess`** in `plugins/e107/plugin.js:22-26,39-53`. These are not paste-specific, but `insertContent()` fires `BeforeSetContent` (`npm:tinymce.js:11037-11043`). Every paste is therefore POSTed synchronously to `parser.php` (`mode=tohtml`) and replaced by the server's `toHTML(…,'WYSIWYG')` output (`parser.php:133-153`). Side effects:
  - Pasted text containing square-bracket bbcode, such as `[img]…[/img]`, is **parsed as bbcode**.
  - `{e_BASE}` is rewritten.
  - The paste fails silently if `parser.php` returns 403 or an error.
- `plugins/bbcode/plugin.js` exists but no profile loads it. It is not in any `<plugins>` or `<external_plugins>` list.

## 4. C. Save path

### 4.1 Client side [READ]

- On `getContent`, which covers the source view and `triggerSave` on submit, `plugins/e107/plugin.js:48-50` calls `parser.php` with `mode=tobbcode`. The `e107TinyMceParser::toDB()` it reaches (`parser.php:178-208`) is **not** `$tp->toDB()`.
  - For a `post_html` user: `trim` → `imgToBBcode()` (rewrites `<img>` to `[img]`) → `html_entity_decode` with `&lt; &gt; &amp; &quot;` pre-escaped → wrap in `[html]…[/html]`.
  - Otherwise: `e_bbcode::htmltoBBcode()`. This is unreachable in practice, because the editor only loads for `post_html` users (`e_footer.php:39`).
- There is no client-side tag or style filtering in this step.

### 4.2 Server side [READ]

- Admin-ui models with `data => 'str'` (news, page, wmessage, PM, forum admin) go through `e_model::sanitizeValue()` → `$tp->toDB($value,false,false,'model',…)` (`ehandlers/model_class.php:3024-3029`).
  `data => 'code'` would use `'pReFs'`, which **skips** `cleanHtml` (`:3038-3040`, `e_parse_class.php:706`). None of the TinyMCE fields listed above use `'code'`.
- `e_parse::toDB()` (`e_parse_class.php:665-817`):
  1. `preFilter()` runs bbcode `toDB` hooks. `[html]` passes through unchanged (`bbcode_handler.php:804-815`, `ecore/bbcodes/html.bb`).
  2. Then `cleanHtml()`, or `cleanHtmlWithoutScript()` when the author lacks `post_script` (`:710`).
  3. If the author lacks `post_html`: `strip_tags()` (`:729-733`).
  4. Quote and `$` entity encoding (`:739-776`).
  5. `e_parse` addon `toDB` hooks run **only when `$mod === 'model'`** (`:783-814`).
- `cleanHtml()` (`:7697-7939`):
  - DOMDocument walk. Tag allowlist: `allowedTags` (`init()` `:5783-5787`):
    `html body div a img table tr td th tbody thead colgroup b i pre code strong u em ul ol li h1–h6 p section article blockquote hgroup aside figure figcaption abbr span audio video source br small caption noscript hr iframe sub sup cite x-bbcode label dl dt dd`.
    `script applet form input button embed object ins select textarea` are added only with script access (`:5789`, `grantScriptAccess()` `:7626-7651`).
  - **Disallowed elements are removed together with all their content** (`$node->parentNode->removeChild($node)`, `:7841-7845`). Affected examples include `font`, `s`, `strike`, `del`, `mark`, `tfoot`, `col`, `q`, `kbd`, `var`, `center` and `nav`. For LibreOffice pastes that use `<font>`, **the text itself is lost on save**.
  - Attribute allowlist per tag (`:5791-5810`): default `id style class title lang accesskey`, plus per-tag extras (`img src alt width height`, `a href target rel`, `td colspan rowspan name bgcolor`, and so on). `on*` attributes are allowed only with script access (`:5812-5815`).
  - Attribute values are checked only against the blacklist `javascript:`, `alert(`, `vbscript:`, `data:text/html`, `mhtml:`, `data:image` (`:5819`, `invalidAttributeValue()` `:8077-8092`).
  - **There is no per-property CSS whitelist.** `style` is kept verbatim on every allowed element.
  - `<pre>` and `<code>` contents are re-wrapped as text (`:7847-7889`).
- **Differences by context.** There is no per-context tag or style policy. The only differences are:
  - whether the author is in `post_html` (if not, `strip_tags`),
  - whether the author is in `post_script` (script tags and `on*`),
  - `'model'` saves running `e_parse` addon hooks while direct `toDB()` calls (comments, forum) do not,
  - comment edits being flattened with `toText()` (`comment_class.php:687`).

  News, page, comment, forum and plugin areas all use the same `allowedTags` and `allowedAttributes`.

**What survives a save for a `post_html` user without `post_script`:** the allowlisted tags above with `id/style/class/title/lang/accesskey` plus the per-tag attributes, and **any CSS property** in `style` (for example `position:fixed`, `background:url(https://…)`, `font-weight: 400`).

## 5. Normalising pasted bold/italic to `<strong>`/`<em>` without loosening the style whitelist

**Constraint (applies to all options).** e107 uses `<i class="fa …">` for FontAwesome glyphs (`extended_valid_elements i[*]` in every profile; `e_parse_class.php:6227`). A blanket `i`→`em` rename would break icons. Every option below converts only `<i>` that has **no class**, or only `<i>` that the paste filter produced.

### Option 1: client-side `PastePostProcess` normaliser (e107 `plugin.js`, or `paste_postprocess` in a profile XML)

- **Where it lives.** Step 5 of §1, after TinyMCE's own filters and before `insertContent`. Two ways to wire it:
  - (a) Add `editor.on('PastePostProcess', …)` to `plugins/e107/plugin.js`. This is new code that applies to all profiles.
  - (b) Put `<paste_postprocess>function(plugin,args){…}</paste_postprocess>` into a theme override `THEME/templates/tinymce/<profile>.xml`. `convertBoolean()` emits strings that start with `function(` as raw JS (`wysiwyg_class.php:200-203`). **[ASSUMED]** That the e107 XML loader keeps such a value intact is not tested.
- **Logic.** Walk `args.node`:
  - `b` → `strong`;
  - `i` without `class` → `em`;
  - `span` whose `style` has `font-weight` ≥ 600 or `bold` → wrap in `strong`;
  - `span` with `font-style:italic|oblique` → wrap in `em`;
  - then **remove every `style` attribute from the pasted fragment** and unwrap attribute-less spans.
- **Pros.**
  - Fixes Google Docs `600`/`800`/`900` weights and the Chrome LibreOffice case, where styles are stripped before any later stage would see them. (Pair it with `paste_webkit_styles: 'font-weight font-style'` so Chrome keeps exactly those two properties long enough for the normaliser to read them.)
  - The user sees the normalised result immediately.
  - It is narrower than today: pasted content ends up with **zero** inline styles.
- **Cons.**
  - Client-only, so it is not a security boundary. A crafted POST bypasses it.
  - It does not touch content that enters by other routes (source editing, old data).
  - Option (b) is per role, not per content area (§2.3).
- **Security.** It reduces XSS surface by removing pasted `style` altogether. The server must still be the authority (Option 3), because the client can be bypassed.

### Option 2: TinyMCE schema renaming (`valid_elements` with `strong/b,em/i`) plus a narrow `valid_styles`

- **Where it lives.** `wysiwyg_class.php` (core plugin code). `verify_html:false` at `:352` must stop being unconditional, because it forces `valid_elements='*[*]'` (`npm:tinymce.js:5645-5647`). The profile can then define `valid_elements` with `strong/b` (and `em/i` handled carefully) plus `valid_styles` such as `{'*': 'text-align'}`.
- **Pros.**
  - Uses TinyMCE's built-in mechanism, so every parse is normalised: paste, `setContent` and source view.
  - `valid_styles` also stops arbitrary CSS on the client.
- **Cons.**
  - Turning on `verify_html` with an explicit `valid_elements` is a large behaviour change. Everything not listed is dropped in the editor, including e107 `x-bbcode`, iframes, Bootstrap markup and `bbcode[*]`, and that risks breaking existing content.
  - The `em/i` alias conflicts with FontAwesome `<i class>`: it renames every `i`.
  - Bold and italic expressed through `font-weight:600` or a Chrome-stripped span are still not fixed, because no element is created for them.
  - It is client-only, like Option 1.
- **Security.** It tightens client-side markup, but the server stays the authority.

### Option 3: server-side normalisation in `e_parse` (`cleanHtml` DOM pass, or an `e_parse` addon `toDB` hook)

- **Where it lives.** Step 11 of §1.
  - (a) Core: inside `cleanHtml()`'s DOM walk (`e_parse_class.php:7763-7839`), add a conversion. Turn `b` into `strong` and class-less `i` into `em`. Turn spans with bold or italic `style` into `strong`/`em`. Optionally apply a **per-property CSS allowlist** to `style` (e.g. `text-align` only).
  - (b) Plugin: an `e_parse.php` addon with `toDB()`. Note it only runs for `$mod==='model'` saves (`:783-814`), so comments and forum posts that call `toDB()` without `'model'` are not covered.
- **Pros.**
  - Authoritative: it cannot be bypassed from the client.
  - It covers all input routes.
  - It can finally add the CSS property allowlist that `cleanHtml` lacks today, which closes the "any CSS survives" gap.
  - It can normalise legacy content when re-saved.
- **Cons.**
  - (a) is a core change that affects every HTML field on the site.
  - It runs after the editor, so the user does not see the normalised markup until reload.
  - Chrome has already stripped the styles of non-Word pastes before the server sees them (§3.3), so that case still needs Option 1 to keep `font-weight`/`font-style` long enough.
  - (b) misses non-`model` saves.
- **Security.**
  - This is the right layer for the style policy.
  - A property allowlist (instead of today's value blacklist of `javascript:` and similar) removes CSS-based UI redress (`position:fixed` overlays), external `url()` tracking, and legacy `expression()`/`behavior` vectors. TinyMCE drops the last two on the client (`npm:tinymce.js:6169-6171`), but `cleanHtml` does not.
  - Converting a span into `strong`/`em` must create new elements and never copy attributes blindly.

**Recommendation.** Combine Option 1 and Option 3. Option 1 handles what the user sees at paste time and keeps the two style properties in Chrome long enough to convert them. Option 3 is the authoritative server rule. Option 2 is the most invasive and still does not catch span-only formatting.

## 6. Can "basic HTML only" be enforced per context with the existing mechanism?

**No. It needs new code.** [READ]

- **Client side.**
  - TinyMCE profiles are chosen by role, or by the page-wide constant `e_TINYMCE_TEMPLATE`. The per-field `$template` given to `bbarea()` is never passed to TinyMCE (§2.3).
  - All editors on one page share one `tinymce.init` (selector `.e-wysiwyg`).
  - Even inside a profile, any `valid_elements` is neutralised by the hardcoded `verify_html:false` (`wysiwyg_class.php:352`).
  - A profile *can* set `valid_styles` and paste options, because XML keys pass through (`:317-328`), but only per role. **[ASSUMED]** I did not test that at runtime.
- **Server side.**
  - `allowedTags` and `allowedAttributes` are single global arrays (`e_parse_class.php:5783-5810`).
  - `setAllowedTags()` and `addAllowedAttribute()` exist (`:5843-5859`), but they change state for the whole request. `toDB()` takes no context argument that would select a policy.
  - `cleanHtml()` has no CSS property allowlist at all.
- **Minimum new code needed:**
  1. Pass the `bbarea` `$template`, or a new option, through to TinyMCE. Today that means one `tinymce.init` per profile with distinct selectors, instead of the single `.e-wysiwyg`.
  2. Make `verify_html` configurable per profile.
  3. Add a context-aware server policy, such as a `toDB()` parameter or a model field option that selects a tag, attribute and style allowlist inside `cleanHtml()`.

## 7. Scratch runtime check (TinyMCE layer only, not e107)

- **Setup.** npm `tinymce@4.9.11` in the session scratch directory. Headless Chromium through the pre-installed Playwright. Init with e107's effective keys: `plugins:'paste …'`, `schema:'html5'`, `element_format:'html'`, `verify_html:false`, `convert_fonts_to_spans:false`, `extended_valid_elements:'i[*], bbcode[*]'`. The **e107 external plugin was not loaded**, so there was no `parser.php` round trip. The test called `editor.plugins.paste.clipboard.pasteHtml(html)` and then `editor.getContent()`. Only Chromium was tested, not Firefox.
- **Results:**
  - Google Docs sample (task's markup plus font-size/family) → `<p><span style="font-weight: 400;">normal </span><b>bold </b><i><span style="font-weight: 400;">italic</span></i></p>`. **Italic preserved.**
  - Word-like `<p class="MsoNormal">… <b><span style="mso-…">bold</span></b> <i>…</i> <span style="font-weight:bold">…</span> <span style="font-style:italic">…</span><o:p></o:p></p>` → `<p>normal <strong>bold</strong> <em>italic</em> <b>sbold</b> <i>sitalic</i></p>`.
  - LibreOffice-like `<p style=…>… <b>bold</b> <i>italic</i> <span style="font-weight: bold">…</span> <span style="font-style: italic">…</span> <font face="Liberation Serif">font</font></p>` → `<p>normal <b>bold</b> <i>italic</i> <span>sbold</span> <span>sitalic</span> <font face="Liberation Serif">font</font></p>`. The WebKit quirk stripped the span styles, and `<font>` was kept on the client.
- These results match §3.2–3.4 and **contradict** the reported Google Docs symptom (§3.5).

## 8. What I read vs. what I assume

- **Read in full (whole function plus a caller):**
  - `wysiwyg_class.php` (`getConfig`, `filter_plugins`, `getExternalPlugins`, `convertBoolean`, `renderConfig`, `isAllowed`)
  - `wysiwyg.php`, `e_footer.php`, `plugin.xml`, all four `templates/*.xml`
  - `plugins/e107/plugin.js`, `plugins/e107/parser.php`, `plugins/bbcode/plugin.js`
  - `e_form::bbarea()`
  - `e107::wysiwyg()`
  - `e_parse::toDB()`, `cleanHtml()`, `cleanHtmlWithoutScript()`, `grantScriptAccess()`, `init()`, `toHTML()`/`renderText()`, `processModifiers()`, `parseBBTags()`, `invalidAttributeValue()`
  - `e_bbcode::parseBBCodes()`, `imgToBBcode()`
  - `e_model::sanitize()`/`sanitizeValue()`
  - npm TinyMCE 4.9.11: paste plugin `Settings`, `isWordContent`, `filterStyles`, `filterWordContent`, `preProcess$1`, `filterContent`, `process`, `pasteHtml$1`, `registerEventHandlers`, `removeWebKitStyles`, `Quirks`, `Buttons`, plugin registration; core `Schema()` head, `Styles.parse` (font-weight branch), `insertHtmlAtCaret`, `reduceInlineTextElements`.
- **Not read or not traced:**
  - the save handlers of mailout, featurebox, downloads, signature and user extended fields;
  - `e_bbcode::walkBBCodes()` beyond its head;
  - `e_parse` hide-block helpers;
  - the TinyMCE `HtmlSerializer` and `DomParser` internals beyond the lines cited.
- **Assumed:**
  - the cdnjs build equals npm 4.9.11;
  - what LibreOffice and Word actually put on the clipboard;
  - how the e107 XML loader handles `function(` values;
  - that a profile-level `valid_styles` works;
  - the causes listed in §3.5.
