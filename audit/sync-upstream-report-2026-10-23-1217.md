# Sync report (round 3) — e107-2.4.x-Lite ← e107inc/e107 master

## Baseline

| | |
|---|---|
| Lite `main` HEAD | `7296f21bee73463031f6007529591047c22fcd49` (2026-10-23T13:56:01+02:00, "Merge pull request #116 from Jimmi08/sync-upstream-2026-10-09") |
| Upstream `master` (new baseline) | `4367279f17d4654e3f455f86e677ef1ad12f048e` (2026-10-22T23:57:54+02:00) |
| Previous baseline | `7ce7a121c7a72e5abf0cf02802ca39163649248d` (2026-10-05T22:18:45-05:00) — report `audit/sync-upstream-report-2026-10-09-0548.md` |
| Branch | `sync-upstream-2026-10-23` (created from `main`) |
| Version note | No new tag. `eadmin/ver.php` stays `2.4.0.6 (git)` (§7.1). |

Rules: the same as in the previous two rounds; every earlier decision stays valid except where §7 of the task changes it
(rss → rss_menu, chatbox_menu added, login_menu coreplugs marker). Files were processed with a 3-way merge
(base = previous upstream `7ce7a121c`, ours = Lite, theirs = new upstream `4367279f1`). Every unmarked file in scope was
byte-identical to the previous upstream version, except `class2.php` (the two mapped directory defaults only), so a clean
merge equals "replace". No added line of the synced code carries a new `e107_*` directory literal (checked on every
directory diff).

As in the previous round, each STOP was left untouched (the one-sided file neither added nor deleted, the colliding hunk
not applied) and everything else was processed and committed. The instructions came in a second step; each
resolution is a separate commit (see the STOP table).

Between `10204bf46` and `d0b504bcb` the branch did not run: the synced code called classes and files that existed only
in the STOP 1 / STOP 2 lists (e.g. `\e107\Userclass\Membership` from 47 files, the new session handlers from
`ehandlers/session_handler.php`, `\e107\Http\Request` and `\e107\User\LogoutConfirmation` from `class2.php`,
`e_CORE.'dispatch.php'` from `index.php`). Resolved by STOP 1 and STOP 2.

### Upstream commit range `7ce7a121c..4367279f1` (438 commits)

<details><summary>438 commits (short SHA, commit date, subject)</summary>

| Commit | Date | Subject |
|---|---|---|
| `4367279f1` | 2026-10-22 | Merge pull request #6820 from e107inc/e107help/xml-parse-quietly |
| `5dedb6772` | 2026-10-22 | fix(xml): collect libxml's complaints about a document instead of raising them |
| `10c116e76` | 2026-10-22 | Merge pull request #6812 from e107inc/e107help/6788 |
| `52a06a2ae` | 2026-10-22 | Merge pull request #6811 from e107inc/e107help/6783 |
| `bf0c0505f` | 2026-10-22 | fix(media): store an import asked for '_common' under its type's common category |
| `482424677` | 2026-10-22 | fix(media): let the picker dialog's own search answer whoever may open the dialog |
| `dcca9dc34` | 2026-10-21 | test(session): cast the trimmed cookie path to a string for PHP 5.6 |
| `f28d1aece` | 2026-10-21 | test(db_verify): give back the table a round trip set aside, counter and all |
| `63226f8a3` | 2026-10-21 | test(session): read the session cookie path apart from its SameSite |
| `d3df16f30` | 2026-10-21 | fix(ui): encode an attachment's and a user's modal caption once |
| `b28dd8acc` | 2026-10-21 | fix(forum): encode a reported thread's caption once for the text it is |
| `c84b9213e` | 2026-10-19 | fix(parser): keep a '+' in a form value after a rich text editor |
| `e0c9f6c95` | 2026-10-19 | fix(bbcode): encode once what the other bbcodes read from a post |
| `a2a5e0176` | 2026-10-19 | fix(social): keep social login's requests to the web |
| `507b9df0d` | 2026-10-19 | fix(social): read a social login key file only from the server's own files |
| `1061712b5` | 2026-10-19 | fix(social): sign in through the addresses each provider's adapter knows |
| `b425251d2` | 2026-10-19 | fix(social): keep social login from unserialising what it is handed |
| `5684c60ab` | 2026-10-19 | fix(social): make social login's requests with Hybridauth's own curl options |
| `47a84243b` | 2026-10-19 | fix(social): keep social login's log where e107 puts it |
| `75b1828d7` | 2026-10-19 | fix(social): build each social login provider from its own adapter class |
| `9bdadd3ae` | 2026-10-19 | fix(social): show a provider's build error as text |
| `bb408dacd` | 2026-10-19 | fix(xup): show a failed social sign-in's error as text |
| `feb231f5b` | 2026-10-18 | fix(social): save login settings only under a provider Hybridauth has |
| `0f2509ae5` | 2026-10-18 | fix(social): print stored provider names as text |
| `bac6f12e8` | 2026-10-19 | fix(media): delete an avatar only inside the avatars folder |
| `1ec1901d7` | 2026-10-19 | fix(media): act on an item's file only in the media folder |
| `05bed8040` | 2026-10-19 | test(media): pin a quote in the address the duplicate check looks up |
| `e69baca6d` | 2026-10-19 | fix(media): create an item only from a file in the import folder |
| `9c20d5c31` | 2026-10-19 | fix(media): check the name an upload is stored under |
| `783ca7674` | 2026-10-19 | fix(media): import a file under the name the import list gave it |
| `bfe2e7201` | 2026-10-19 | fix(e107): keep the request path from naming another host |
| `410ac6170` | 2026-10-19 | fix(e107): keep path info, markup and other hosts out of the web path |
| `153f27fe4` | 2026-10-19 | test(admin-ui): check more fields and a page after a failed save |
| `6bfc42fd5` | 2026-10-19 | fix(admin-ui): bring a form back from a failed save as it was posted |
| `2960fd6a3` | 2026-10-18 | fix(forum): count a forum's moderators the way their class list does |
| `c75fbb829` | 2026-10-18 | fix(social): give Facebook's SDK the app id as digits in a JavaScript string |
| `1efc4b966` | 2026-10-18 | fix(social): encode the social page addresses wherever the plugin prints them |
| `bbd2128f6` | 2026-10-18 | fix(form): link an admin list's url and file columns to no address that runs script |
| `8a6faeddf` | 2026-10-18 | fix(upload): refuse a script address for an upload unless the uploader may post script |
| `9e2df1c9a` | 2026-10-18 | fix(featurebox): refuse a script address as an item's image link unless the saver may post script |
| `b07ad3a84` | 2026-10-18 | fix(download): refuse a script address for a mirror unless the saver may post script |
| `6c8eeabee` | 2026-10-18 | fix(linkwords): refuse a script address for a linkword unless the saver may post script |
| `b3e8ec2d3` | 2026-10-18 | fix(page): refuse a script address for a menu button or url field unless the saver may post script |
| `70053e41d` | 2026-10-18 | fix(hero): refuse a script address for a hero button unless the saver may post script |
| `b8cf3e8ea` | 2026-10-18 | fix(social): refuse a script address for a social page unless the saver may post script |
| `fc4e07f7f` | 2026-10-18 | test: open a page as an author outside or inside the class that may post script |
| `22279d729` | 2026-10-18 | fix(links): refuse a script address on save unless the saver may post script |
| `89b5f7890` | 2026-10-18 | fix(sitelinks): pass a popup link's address to open_window() as a JS string |
| `2852651d6` | 2026-10-18 | fix(sitelinks): encode the link description for the screentip title |
| `90a0c22dc` | 2026-10-18 | fix(parser): encode what a constant expands to in toAttribute() |
| `050d1670e` | 2026-10-18 | fix(bbcode): encode a [link] address once |
| `ce5b86b87` | 2026-10-18 | fix(parser): show the bbcode an x-bbcode alt holds as text |
| `d14966664` | 2026-10-18 | fix(parser): read a bbcode tag whose ] lies inside an HTML tag as text |
| `9e072efa3` | 2026-10-18 | fix(bbcode): encode the parameter of a [hide] written back as typed |
| `1e3c5063b` | 2026-10-18 | fix(parser): read each [hide] block's text once, however deep the nest |
| `4844c1fef` | 2026-10-18 | perf(parser): pair bbcode tags in time linear in the text |
| `2ad70ac63` | 2026-10-19 | test: add user classes for one test through one shared helper |
| `0c6e34de3` | 2026-10-17 | test(parser): give the script markers in three tests a neutral name |
| `2b3c58216` | 2026-10-17 | test: write every news row in the Cests through Test\Rows::news() |
| `ca4597706` | 2026-10-17 | test: seed the comment AJAX Cests through the shared helpers |
| `df460718c` | 2026-10-17 | test: keep one way to scope a preference to a test |
| `13151e115` | 2026-10-17 | test: convert the remaining copies of the shared test helpers |
| `5be6c77af` | 2026-10-17 | test(upload): turn uploads on for the {UPLOADFILE} handler test |
| `a976a1ae6` | 2026-10-17 | test(forum): sign the report caption browser test in through the probe |
| `47c772594` | 2026-10-17 | test(parser): pin the entities text_truncate() keeps whole at the cut |
| `c2e238b80` | 2026-10-17 | test(forum): fit three forum tests to the changes they now meet |
| `a84d37165` | 2026-10-17 | fix(forum): stop the edit box decoding a member's [code] a second time |
| `5106408cb` | 2026-10-17 | fix(parser): leave [hide] to a strip list that names it |
| `11af4a931` | 2026-10-17 | chore(siteinfo, tinymce4): raise both plugins to 1.0.1 |
| `7562d0bde` | 2026-10-16 | test: share the seeding and preference helpers that Cests copied |
| `58a3ae0ba` | 2026-10-19 | test: put the WYSIWYG preference back after the editor tests |
| `86b12f8ef` | 2026-10-11 | fix(debug): print bbcode and shortcode names and parameters as text |
| `2f9d9836f` | 2026-10-11 | fix(class2): print PHP diagnostics in the errors panel as text |
| `bc7a443a4` | 2026-10-12 | fix(news): show the news debug block's values as text |
| `8014da7c6` | 2026-10-11 | fix(signup): stop the activation resend rewriting an account's password |
| `18af958fe` | 2026-10-16 | fix(search): send the advanced author filter under the key the areas read |
| `e9416ba0c` | 2026-10-11 | fix(search): stop the advanced author field taking form options from the query string |
| `aef9dc675` | 2026-10-13 | fix(security): do not let a SEF route hand a page an unjudged e-token |
| `06ac85c84` | 2026-10-13 | fix(security): take a remembered return address only from the session |
| `121b204a7` | 2026-10-13 | fix(security): do not return to a token-marked address after signing in |
| `40da5a9e6` | 2026-10-13 | fix(security): do not return a front-end sign-in to a token-marked address |
| `1a9121f82` | 2026-10-12 | fix(security): place a same-site request without an Origin by its Referer |
| `b77d9450b` | 2026-10-13 | fix(security): judge every e-token a page will read |
| `e3d3446d8` | 2026-10-12 | fix(security): let only the visitor's own navigation overrule a wrong token |
| `d4ce6118b` | 2026-10-13 | fix(banlist): save the ban messages and durations for the main administrator only |
| `8528dca9f` | 2026-10-13 | fix(admin_ui): save only the options a screen declares |
| `f314c3bff` | 2026-10-12 | fix(admin_ui): run a batch copy, export, link or feature box only where the list offers it |
| `558db3162` | 2026-10-12 | fix(admin_ui): say when a batch leaves out rows the list no longer shows |
| `fbeb62c7c` | 2026-10-12 | fix(admin_ui): hold a plugin's batch command to the edit route |
| `d228cd5b7` | 2026-10-12 | fix(cpage): refuse every route the permission map does not name |
| `c2edd1ed3` | 2026-10-12 | fix(cpage): hold every custom page delete to the delete permission |
| `9b801e4b3` | 2026-10-12 | fix(cpage): hold custom pages and custom menus each to their own permission |
| `63c0193a6` | 2026-10-12 | fix(security): file front-end inline-edit maps under a new session key |
| `e9095b92f` | 2026-10-12 | fix(news): hold the front-end inline editor to news_limit_to_self |
| `c4e63d7dd` | 2026-10-12 | fix(admin_ui): hold the edit form and inline editor to the list's own rows |
| `8dcaf2fe9` | 2026-10-13 | fix(admin_ui): search and suggest only the columns a list searches |
| `9051a4129` | 2026-10-12 | fix(admin_ui): dispatch an action only to action methods |
| `2e32e1ced` | 2026-10-12 | fix(admin_ui): run an ajax page only on an ajax request |
| `612555062` | 2026-10-11 | fix(admin_ui): hold the default action to its own route |
| `c0dac1330` | 2026-10-12 | refactor(admin_ui): fold route names through the ASCII case shim |
| `d864a4107` | 2026-10-11 | fix(admin_ui): fold route names in ASCII, as PHP folds a method name |
| `494a3856e` | 2026-10-11 | fix(newspost): hold a self-limited editor to their own items |
| `4e4b23fd3` | 2026-10-11 | fix(admin_ui): act on a list batch only for rows the list selects |
| `b3e879f32` | 2026-10-11 | fix(admin_ui): hold a list delete to the declared delete route |
| `4bed3384e` | 2026-10-11 | fix(admin_ui): hold a list batch to the route it writes through |
| `8d8f72011` | 2026-10-11 | fix(admin_ui): let the route, not the request, decide what a form saves |
| `00c660f62` | 2026-10-11 | fix(admin_ui): refuse a row delete the list does not select |
| `e9ce0bbe3` | 2026-10-11 | fix(users): delete only rank rows from the superseded ranks editor |
| `a537e9698` | 2026-10-11 | fix(users): gate the superseded ranks editor on the ranks permission |
| `4cb363917` | 2026-10-11 | test: cover who may write a row through the inline and sort editors |
| `a3bce2e2e` | 2026-10-11 | fix(security): hold an inline or sort ajax write to the edit route's permission |
| `299ae8973` | 2026-10-11 | fix(security): refuse an inline save whose editable map declares no permission |
| `c444b0184` | 2026-10-11 | fix(security): gate front-end inline news editing on the item edit permission |
| `8f602a9e6` | 2026-10-17 | fix(security): answer comment.php's AJAX requests as JSON |
| `2fd757cea` | 2026-10-12 | fix(comments): ask comment switches and the member list before publishing |
| `eeba3c715` | 2026-10-12 | fix(comments): ask comment::canReadComments() on the comments page |
| `857aa8d5d` | 2026-10-12 | fix(comments): take no comment on an item the poster may not read |
| `fdf63e9b4` | 2026-10-12 | fix(comments): page through an item's comments only where they may be read |
| `5b2a011c4` | 2026-10-12 | fix(comments): ask whether the item may be read before a reply form |
| `2f22061c5` | 2026-10-12 | fix(comments): publish no forum poll's comments in the menu or the feed |
| `8d4e0c939` | 2026-10-12 | fix(comments): keep a custom page's withheld comments out of the menu |
| `9f8d748ea` | 2026-10-12 | fix(comments): ask on comment.php what the item's own page asks |
| `070b3883c` | 2026-10-12 | fix(comments): compare a comment's type as text |
| `1e7676a2c` | 2026-10-11 | fix(search): ask whether a comment's item may be read before publishing it |
| `96cb8f3bc` | 2026-10-13 | fix(forum): offer in the search's forum list only forums the caller may view |
| `d78daa8e5` | 2026-10-13 | fix(forum): close a sub-forum to whoever its forum is closed to |
| `d755e6d56` | 2026-10-12 | fix(forum): list on a forum's page only the sub-forums the caller may view |
| `5b5a49c1c` | 2026-10-12 | fix(forum): ask the view class before sending a caller to a post |
| `982392e4c` | 2026-10-12 | fix(forum): list no forum on the index to a caller who may view none |
| `acd7e92c9` | 2026-10-12 | fix(forum): leave [hide] blocks out of the feeds and the posts menu |
| `7338ef76a` | 2026-10-12 | fix(forum): put threads only in forums, never in a category |
| `32b15e211` | 2026-10-12 | fix(forum): grant nothing in a forum the caller may not view |
| `9b9dfb845` | 2026-10-18 | fix(forum): list only forums the moderator moderates and may view |
| `7b9c6ca8a` | 2026-10-12 | fix(forum): offer a new thread only where the posting page accepts one |
| `94409cb23` | 2026-10-11 | fix(forum): check the forum's new-thread class before starting a thread |
| `9ab0b05f5` | 2026-10-11 | fix(forum): let only an opening post's edit rename or pin its thread |
| `215fe6c10` | 2026-10-11 | fix(forum): decide the posting page's moderator from the forum it acts on |
| `b46d87ef3` | 2026-10-11 | fix(forum): split posts only into a forum the moderator moderates |
| `0114f0168` | 2026-10-12 | refactor(forum): ask e107forum who may attach a poll |
| `fe70fb526` | 2026-10-12 | fix(poll): let only a poll administrator write the poll a request names |
| `affb22d0e` | 2026-10-11 | fix(forum): ask the forum's poll setting before saving a thread's poll |
| `cfd7a230b` | 2026-10-11 | fix(poll): never let a forum thread's poll rewrite an existing poll |
| `0ce51e588` | 2026-10-16 | docs(class2): deprecate class_list(), whose two forms disagree |
| `c95bbedab` | 2026-10-16 | fix(js_manager): name the combined JS and CSS file for the release |
| `07b8f9c1e` | 2026-10-17 | fix(security): load a Menu Manager option only from the Menu Manager |
| `2a380d24e` | 2026-10-17 | test(webdriver): record a page's requests through a helper |
| `a1c5088b2` | 2026-10-16 | fix(js): keep an e-ajax link from fetching script from another site |
| `821a2bda4` | 2026-10-16 | fix(js): skip a rating element that carries no hints |
| `829ddff36` | 2026-10-16 | fix(js): write a rating's status as text, into the line beside it |
| `8fcb39ca5` | 2026-10-13 | fix(parser): keep data-* attributes only for an author who may post script |
| `82d048145` | 2026-10-13 | fix(js): put a link's caption and loading icon into the page as text |
| `46a5bafe6` | 2026-10-17 | fix(security): build the comment edit control as an element |
| `b6bbd65b5` | 2026-10-16 | fix(security): load only a web address into the .e-modal frame |
| `8faeb3a33` | 2026-10-16 | fix(security): look up an .e-expandit target, never build it |
| `8999909f5` | 2026-10-13 | fix(download): keep a mirror's URL and size inside their fields |
| `fef86281d` | 2026-10-19 | fix(hero): print an old bullet's text as an HTML author's |
| `57d375542` | 2026-10-18 | fix(hero): keep a bullet or button value the saver left unchanged |
| `92cbe5759` | 2026-10-16 | fix(hero): print a slide's bullets and buttons as their author may |
| `457fdd157` | 2026-10-18 | fix(customfields): keep a setting the saver left unchanged as it was stored |
| `d9933486b` | 2026-10-18 | fix(customfields): print a custom field's title and settings as their author may |
| `13493217c` | 2026-10-19 | fix(customfields): print an old one-line value as an HTML author's |
| `71f2d7c0e` | 2026-10-19 | fix(customfields): print an old rich text value as an HTML author's |
| `367b1b8fb` | 2026-10-19 | fix(parser): store content as an author who may post HTML but not script |
| `aa634a2d5` | 2026-10-18 | fix(customfields): keep a value the saver left unchanged as it was stored |
| `6e0432d6e` | 2026-10-18 | fix(parser): tell an unchanged form value from a changed one |
| `fbc1396fd` | 2026-10-16 | fix(customfields): print a custom page field's value as its author may |
| `18f8a2ce2` | 2026-10-17 | fix(tinymce4): keep a quote inside an attribute value when the editor saves |
| `b3d8e4e02` | 2026-10-17 | test(parser): pin what a bare "<" after the body end tag keeps |
| `96cd2e7c0` | 2026-10-17 | test(parser): guard the control-byte strip on libxml2 2.9.4 and 2.9.10 |
| `b66fbc9fa` | 2026-10-17 | fix(parser): keep a bare "<" in text that also holds markup |
| `7ff0fff79` | 2026-10-13 | fix(parser): keep the text inside an iframe as text in toHTML() |
| `35c2a9322` | 2026-10-13 | fix(parser): clean markup written inside a processing instruction |
| `c4349d6e8` | 2026-10-13 | fix(parser): keep a stored HTML comment one comment in toHTML() |
| `7f9004f33` | 2026-10-13 | fix(form): print a list's stored names and titles as HTML |
| `9ecd5fd26` | 2026-10-17 | test(parser): pin a bbcode inside a tag in an [scode] block |
| `814214c64` | 2026-10-17 | test(parser): pin a shortcode beside a [code] block left unrendered |
| `5b943b70a` | 2026-10-17 | fix(parser): restore entities inside [scode] and [code] as in body text |
| `b8e0c12aa` | 2026-10-17 | fix(parser): encode what a bbcode expands to inside an attribute |
| `56c01e293` | 2026-10-13 | fix(parser): keep a quote inside an attribute value inside it |
| `6b9b1f7e9` | 2026-10-16 | test(update): check the 2.x update leaves stored markup as it was saved |
| `192740d6a` | 2026-10-12 | fix(parser): compare an attribute value with the deny list in ASCII |
| `ccb5ad841` | 2026-10-13 | fix(faqs): pass a question to the share buttons as one parameter |
| `893d5076c` | 2026-10-16 | fix(parser): expand shortcodes only in text an administrator wrote |
| `4f642e9ab` | 2026-10-13 | fix(page): name page navigation links only from a title the reader may see |
| `a63a89174` | 2026-10-19 | fix(parser): encode what a shortcode expands to inside an attribute |
| `2bb651b2d` | 2026-10-13 | refactor(upload): decide who may use public uploads in one place |
| `6f6856b8d` | 2026-10-13 | fix(upload): close upload.php while public uploads are switched off |
| `0fd32c05a` | 2026-10-13 | fix(shortcodes): load the admin phrases {UPLOADFILE} prints |
| `e845baf16` | 2026-10-13 | fix(shortcodes): make {UPLOADFILE} honour the upload settings outside the admin area |
| `21ce48145` | 2026-10-13 | fix(shortcodes): print {UPLOADFILE} handler values as JavaScript strings |
| `e91e088cd` | 2026-10-13 | fix(parser): read an embedded icon from the icon folder only |
| `8e25c4244` | 2026-10-13 | fix(menus): keep a plugin menu's path inside the plugins folder |
| `cc146c2df` | 2026-10-13 | fix(shortcodes): read {BREADCRUMB=name} crumbs from named arrays only |
| `4ea553f5b` | 2026-10-13 | fix(shortcodes): encode the posted address {IMAGEPREVIEW} links to |
| `a8f050ab5` | 2026-10-13 | fix(shortcodes): refuse a script address in the {IMAGESELECTOR} preview link |
| `bc42996bf` | 2026-10-13 | fix(shortcodes): print {IMAGESELECTOR} handler values as JavaScript strings |
| `5f2758238` | 2026-10-13 | fix(parser): print shortcode parameters written in text as text |
| `4c9d37e73` | 2026-10-12 | fix(shortcodes): print {NEXTPREV} page titles and bullets as text |
| `d7aff6231` | 2026-10-12 | fix(shortcodes): encode the address {NEXTPREV} prints in its links |
| `1d6dba814` | 2026-10-12 | fix(shortcodes): print only language terms as {NEXTPREV} page titles |
| `a6008317a` | 2026-10-12 | fix(parser): print only a language term that a whole field names |
| `deda84b3a` | 2026-10-12 | fix(shortcodes): print only language terms from {LAN=} |
| `b595f0495` | 2026-10-12 | fix(core): read schemes, tag names and disallowed names in ASCII, any locale |
| `c1a87c1ee` | 2026-10-12 | fix(parser): read a URL's scheme in ASCII, whatever the locale |
| `2ecd7771e` | 2026-10-12 | feat(shims): add strtolower() and strtoupper() that fold ASCII in any locale |
| `905b9d5a1` | 2026-10-11 | test(harness): give the web image a Turkish locale |
| `78911692c` | 2026-10-12 | fix(parser): leave an address the browser would run as script unlinked |
| `a2fefda3e` | 2026-10-12 | fix(parser): expand only web path constants in an attribute |
| `429ec0297` | 2026-10-12 | fix(bbcode): list only the site's image folder for a wildcard [img] |
| `c74fb88fa` | 2026-10-19 | fix(bbcode): check the URL [stream] prints |
| `547ea3296` | 2026-10-19 | fix(bbcode): refuse an [img] thumbnail link whose scheme can execute |
| `dcf910c26` | 2026-10-19 | fix(bbcode): keep the parser's globals when a bbcode URL renders a shortcode |
| `f4351f415` | 2026-10-19 | fix(bbcode): check the URL [url], [link] and [flash] print, not the one typed |
| `7f1b36e70` | 2026-10-19 | fix(bbcode): refuse a [file] link whose scheme can execute |
| `3fcec49b0` | 2026-10-12 | fix(media): take the line breaks out of eleven Bootstrap 3 icon names |
| `3237bbfe2` | 2026-10-12 | fix(bbcode): keep [video] text out of attribute markup |
| `bc3471d9b` | 2026-10-12 | fix(bbcode): keep [glyph] text out of attribute markup |
| `8209f17fd` | 2026-10-13 | fix(forum): parse a forum's thread list once |
| `0119742e2` | 2026-10-13 | fix(download): keep a download's name inside its field on the edit form |
| `8bc582c19` | 2026-10-12 | fix(parser): add no element or attribute when decoding for feeds and mail |
| `7a7e232c0` | 2026-10-17 | test(forum): keep the edited post storage test independent of libxml |
| `29c863fac` | 2026-10-13 | fix(forum): report on upgrade the edited posts that hold markup |
| `9a7852c3a` | 2026-10-13 | fix(forum): store an edited post under its author's permissions |
| `adbc0543b` | 2026-10-13 | fix(forum): keep a member's text as text in the rich-text editor |
| `0f6bfb42f` | 2026-10-13 | fix(parser): decode a form value's percent-escapes to text, not markup |
| `f5db9655b` | 2026-10-13 | fix(form): print a decoded value as text in text boxes and pickers |
| `b17d5118e` | 2026-10-12 | fix(parser): render the bbcode inside [table] and [hide] once |
| `49a7db513` | 2026-10-12 | fix(parser): restore entities before rendering bbcode inside [table], [hide] and [html] |
| `604ab1a4e` | 2026-10-12 | fix(forum): store and print a guest's name as text |
| `084713874` | 2026-10-12 | fix(list_new): filter profanity in the headings of a section's records |
| `66449cd54` | 2026-10-12 | fix(list_new): print a section's info as the section built it |
| `a218b70e6` | 2026-10-12 | fix(list_new): print a section's heading as the section built it |
| `4b4db6f6b` | 2026-10-12 | fix(forum): render a thread name's inline bbcode and strip the rest |
| `10e7de0f9` | 2026-10-12 | fix(bbcode): define the placeholder [sanitised] shows to readers |
| `eeb8491d0` | 2026-10-11 | fix(forum): name a reported thread in its caption as text |
| `8e3c46882` | 2026-10-11 | fix(forum): print a stored thread name as text |
| `7a327369f` | 2026-10-11 | fix(forum): store an edited thread subject as a new one is stored |
| `56b682126` | 2026-10-11 | fix(forum): check and write a post's own thread, not the URL's |
| `cbc9ff96d` | 2026-10-11 | fix(forum): keep the action a post form was checked for |
| `4b66ec52c` | 2026-10-11 | fix(upload): print an upload's owner as text on the admin upload pages |
| `af0fe82e4` | 2026-10-16 | refactor(forum): set the forum and topic descriptions through e107::meta() |
| `63a7e44e1` | 2026-10-16 | fix(social): copy a page's description into og: and twitter: as it prints |
| `579cf3e46` | 2026-10-11 | test(parser): pin that text_truncate() keeps the newlines of a multi-line text |
| `153d632f8` | 2026-10-11 | fix(forum): strip quotes from the meta description after truncating it |
| `b0b86b7a9` | 2026-10-11 | fix(parser): stop text_truncate() decoding the entities it returns |
| `9853ae5b5` | 2026-10-16 | refactor(forum): read an attachment entry's file through fileName() |
| `b280af729` | 2026-10-16 | fix(mail): read an email's sender and copies as they were stored |
| `2cdd4ae20` | 2026-10-11 | fix(forum): print an attachment's name and index as text |
| `87f55d792` | 2026-10-11 | fix(core): read a stored array as written before trying it unslashed |
| `8d15b5264` | 2026-10-19 | Merge pull request #6802 from e107inc/e107help/6497-news-rss-comments |
| `eaaa54607` | 2026-10-19 | fix(news): put the RSS comments link on items open to comments |
| `b8d8c37ca` | 2026-10-19 | test: plant news items through one shared trait |
| `33b62cbc0` | 2026-10-19 | Merge pull request #6772 from e107inc/e107help/6771 |
| `34c2f6754` | 2026-10-19 | Merge pull request #6750 from e107inc/e107help/6741 |
| `b7138e84a` | 2026-10-19 | test(flood): give the timeout row the default timeout |
| `52fc26d31` | 2026-10-19 | fix(login): announce a failed-login ban only when add_ban() reports adding it |
| `1675fa914` | 2026-10-19 | fix(banlist): keep one ban-list entry per address and type on every path |
| `ebda05aae` | 2026-10-19 | feat(logout): ask before logging out, as the first v3 route |
| `e9671764c` | 2026-10-19 | Merge pull request #6761 from Kanonimpresor/fix/request-filter-wget-prose |
| `7ea2b19a3` | 2026-10-19 | fix(request): remove the wget, curl -o, lwp-download and onmouse refusal |
| `8a0ee2653` | 2026-10-19 | fix(online): ban a flooding address once when its requests arrive together |
| `ff7496064` | 2026-10-19 | Merge pull request #6430 from e107inc/e107help/6328 |
| `6931e16dd` | 2026-10-18 | Merge pull request #6583 from e107inc/e107help/6435 |
| `d0ff83a88` | 2026-10-18 | Merge pull request #6797 from e107inc/e107help/harness-boundary-name-covers-its-addresses |
| `2d531795b` | 2026-10-18 | Merge pull request #6795 from e107inc/e107help/unit-tests-read-notices-on-every-php |
| `cf2ef2615` | 2026-10-17 | test(harness): let a declared host cover a connection that named nothing |
| `8f8842438` | 2026-10-17 | test: catch a missing key, variable or constant on PHP 7 and 5.6 too |
| `9873ddaba` | 2026-10-17 | Merge pull request #6790 from e107inc/e107help/todb-member-ampersand |
| `d0513ff8f` | 2026-10-16 | fix(parser): encode an ampersand once for an author who may not post HTML |
| `b6926826f` | 2026-10-16 | Merge pull request #6789 from e107inc/e107help/security-md-post-html-boundary |
| `cff2c3731` | 2026-10-16 | docs(security): keep posting HTML and posting script apart |
| `7197aacf7` | 2026-10-13 | Merge pull request #6786 from e107inc/e107help/security-md-threat-model |
| `5196e1c67` | 2026-10-13 | docs(security): state the threat model the policy rates reports by |
| `b2c2f7157` | 2026-10-13 | Merge pull request #6785 from e107inc/e107help/test-image-self-contained |
| `5a31c9c07` | 2026-10-13 | Merge pull request #6784 from e107inc/e107help/install-php-floor-5.6 |
| `0e85c7d7d` | 2026-10-13 | fix(install): stop naming E_STRICT in the installer's error handler |
| `b4d830a34` | 2026-10-13 | test: share one class extractor between the tests that load install.php |
| `1c2a3524b` | 2026-10-13 | ci(acceptance): stop presenting the matrix as e107's PHP range |
| `caf943282` | 2026-10-13 | docs(readme): state the PHP versions each release line supports |
| `62f65965e` | 2026-10-13 | docs(readme): leave the PHP and database versions to the installer |
| `143b173f6` | 2026-10-13 | fix(install): accept PHP 5.6 again, as the rest of master does |
| `6873a49ea` | 2026-10-13 | docs(readme): Corrections to the release lifecycle table |
| `010d46bf6` | 2026-10-13 | feat(harness): a self-contained default stage with a bundled MariaDB |
| `7b09bc6ea` | 2026-10-13 | build(harness): build the web image from the repository root |
| `7ed5b61b4` | 2026-10-13 | Merge pull request #6781 from e107inc/e107help/forum-quote-prefill |
| `d0805d265` | 2026-10-13 | Merge pull request #6779 from e107inc/e107help/plugin-uninstall-comment-ids |
| `65b76439e` | 2026-10-13 | Merge pull request #6778 from e107inc/e107help/plugin-handler-without-admin-language |
| `be4464eb6` | 2026-10-13 | Merge pull request #6776 from e107inc/e107help/get-files-tmp-filter |
| `00c33c6cd` | 2026-10-13 | Merge pull request #6774 from e107inc/e107help/6691 |
| `8824601da` | 2026-10-13 | fix(forum): quote a post as a [quote] block again |
| `ca55d3a51` | 2026-10-13 | feat(security): let a SealedToken subclass bind its tokens to the key alone |
| `766310080` | 2026-10-13 | fix(search): answer a visitor's comment search without the plugin handler |
| `6c39f93ab` | 2026-10-13 | fix(plugin): remove a plugin's declared comments along with its tables |
| `a4202f906` | 2026-10-13 | fix(file): skip only names starting with .tmp in get_files()'s filter |
| `eb83560bb` | 2026-10-13 | fix(forum): show a [hide] block to a member who has posted in the thread |
| `9f4aa9af8` | 2026-10-13 | fix(parser): save a post's bbcode in the order it was typed |
| `7a4704116` | 2026-10-13 | fix(parser): decide [hide] blocks once, with the parser's own pairing |
| `fde9b6429` | 2026-10-13 | fix(update): remove a stored session row the 2.x update cannot re-key |
| `356bad391` | 2026-10-12 | Merge pull request #6769 from e107inc/e107help/6573 |
| `7be2fe1e1` | 2026-10-12 | Merge pull request #6763 from Kanonimpresor/fix/lancheck-plugin-edit-modal-submit |
| `a2289f53e` | 2026-10-12 | fix(admin_ui): archive before the edit and the restore, and refuse the change when that write fails |
| `03045a1f0` | 2026-10-12 | fix(parser): read a [hide] block's unpaired opening tag as [hide] |
| `5cc916a33` | 2026-10-12 | fix(ui): name every bundled Bootstrap on core's right floats |
| `0b62b231a` | 2026-10-12 | refactor(tests): share the source scan and the tree scope between conventions tests |
| `f9e72343b` | 2026-10-12 | fix(ui): settle one class list for a right-aligned dropdown menu |
| `4db279ce8` | 2026-10-12 | fix(parser): pair a closing bbcode only with an opener of its digits |
| `143488924` | 2026-10-12 | Merge pull request #6765 from e107inc/e107help/comment-type-numeric-admin-counts |
| `0b45ddd7f` | 2026-10-12 | Merge pull request #6581 from e107inc/e107help/6509 |
| `830bf39dd` | 2026-10-12 | Merge pull request #6577 from e107inc/e107help/6444 |
| `55edf0856` | 2026-10-12 | Merge pull request #6570 from e107inc/e107help/6439 |
| `17c6d753f` | 2026-10-12 | Merge pull request #6512 from e107inc/e107help/6501 |
| `5829c06f4` | 2026-10-12 | Merge pull request #6754 from e107inc/e107help/6302-session-handlers |
| `bc202b52d` | 2026-10-12 | fix(comments): compare a comment's type as text in two counts |
| `b85715810` | 2026-10-12 | fix(lancheck): show the Save button in the modal footer for plugin and theme files |
| `6a5cfa3e9` | 2026-10-12 | fix(request): stop refusing POSTed prose that merely mentions wget |
| `a6cf82cc0` | 2026-10-12 | Merge pull request #6454 from e107inc/e107help/6443 |
| `aace0cdb7` | 2026-10-12 | Merge pull request #6446 from e107inc/e107help/6323 |
| `8e1ef9ff7` | 2026-10-12 | feat(session): add a files save method that does not lock |
| `4d8048b66` | 2026-10-12 | refactor(session): move the database session handler into e107\SessionHandlers |
| `e922fca53` | 2026-10-12 | fix(prefs): say what Disallow multiple logins does, and stop preselecting Database |
| `be3bcb865` | 2026-10-12 | fix(login): end the account's other sessions in every storage mode |
| `37d97d50b` | 2026-10-12 | feat(user): record each sign-in, check it on every request, end it at sign-out |
| `c08ca1cc4` | 2026-10-12 | feat(user): keep a record of each account's signed-in sessions |
| `960b57ef8` | 2026-10-11 | Merge pull request #6757 from e107inc/e107help/db-parity-whitelists-stay-current |
| `1946d56ef` | 2026-10-11 | Merge pull request #6711 from e107inc/e107help/webdriver-probe-signin |
| `0967efd5f` | 2026-10-11 | test(db): fail the parity test on a whitelist entry that no longer differs |
| `b59488c8f` | 2026-10-11 | test(webdriver): clear the browser's cookies without loading a page |
| `d0867d6f6` | 2026-10-11 | test(webdriver): sign in through a probe unless the test is about signing in |
| `e22293267` | 2026-10-11 | test: share one token walker between the tests that read source |
| `ac3c94576` | 2026-10-11 | Merge pull request #6733 from e107inc/e107help/fpw-release-reset-on-failed-send |
| `45c34dc4f` | 2026-10-11 | Merge pull request #6729 from e107inc/e107help/write-file-atomic-opcache-invalidate |
| `cf2f9b1a1` | 2026-10-11 | Merge pull request #6727 from e107inc/e107help/perf-tagcloud-no-forced-write |
| `6b9d928e9` | 2026-10-11 | Merge branch 'master' into e107help/perf-tagcloud-no-forced-write |
| `9ef1e52b6` | 2026-10-11 | Merge pull request #6725 from e107inc/e107help/perf-online-ua-once |
| `b6e3b11cd` | 2026-10-11 | Merge pull request #6723 from e107inc/e107help/perf-pref-cache-json |
| `1fd2712e8` | 2026-10-11 | Merge pull request #6714 from e107inc/e107help/mysqli-insert-id-per-instance |
| `f913d36f3` | 2026-10-11 | Merge pull request #6712 from e107inc/e107help/file-move-across-filesystems |
| `8e36350b3` | 2026-10-11 | Merge pull request #6710 from e107inc/e107help/harness-up-retry-services |
| `94e3a915d` | 2026-10-11 | Merge pull request #6709 from e107inc/e107help/perf-session-per-request |
| `7d2506e70` | 2026-10-11 | Merge pull request #6708 from e107inc/e107help/fpw-expired-code-not-outstanding |
| `ff1ca8b91` | 2026-10-11 | Merge pull request #6707 from e107inc/e107help/admin-feeds-stale-while-revalidate |
| `508066096` | 2026-10-11 | Merge pull request #6703 from e107inc/e107help/perf-share-pdo-connection |
| `02ddfcaba` | 2026-10-11 | Merge pull request #6719 from e107inc/e107help/webdriver-headless-shell |
| `e2734439f` | 2026-10-11 | Merge pull request #6718 from e107inc/e107help/ci-merge-layouts-and-schemes |
| `dfc41e21c` | 2026-10-11 | Merge pull request #6717 from e107inc/e107help/ci-unit-per-php |
| `d74bb255b` | 2026-10-11 | Merge pull request #6716 from e107inc/e107help/ci-bringup-trims |
| `5d3f73275` | 2026-10-11 | Merge pull request #6756 from e107inc/e107help/6751 |
| `b27e39f03` | 2026-10-11 | Merge pull request #6436 from e107inc/e107help/6412 |
| `a380f3f14` | 2026-10-11 | Merge pull request #6285 from e107inc/e107help/6282 |
| `c5bf2ca68` | 2026-10-11 | Merge pull request #6605 from e107inc/e107help/6485 |
| `81e397672` | 2026-10-11 | Merge pull request #6484 from e107inc/e107help/6475 |
| `85ccb7869` | 2026-10-11 | Merge pull request #6602 from e107inc/e107help/6464 |
| `bea6cb6f6` | 2026-10-11 | test(webdriver): keep each test's page focused while other sessions run |
| `8ddc20795` | 2026-10-11 | perf(admin): show the dashboard feeds from the cache, renewing behind it |
| `746b4b6df` | 2026-10-11 | test(harness): call the browser service browser |
| `8c3b8975c` | 2026-10-11 | test(webdriver): drive Chrome's headless shell through chromedriver |
| `e396ee0a9` | 2026-10-11 | fix(fpw): release a reset code whose link could not be sent |
| `fffe0748e` | 2026-10-11 | fix(cache): make OPcache forget a PHP file e107 replaces or deletes |
| `5379fbbb2` | 2026-10-11 | perf(tagcloud): write the cloud to the content cache only while it is on |
| `f476a2645` | 2026-10-11 | perf(online): classify each distinct user agent once per request |
| `ada3bf5be` | 2026-10-11 | perf(prefs): cache preferences as JSON wherever JSON reads back the same |
| `37b863cfe` | 2026-10-11 | fix(db): keep each e_db_mysql instance's insert id its own |
| `7726f9503` | 2026-10-11 | fix(file): move a plugin or theme folder between file systems |
| `f0461be75` | 2026-10-11 | ci: run acceptance's layouts and WebDriver's schemes at once, a job each |
| `64e72bae8` | 2026-10-11 | ci(unit): run every database of a PHP version at once, in one job |
| `bb4f5d2ef` | 2026-10-11 | fix(harness): take turns installing into a shared composer cache |
| `4b4d51c77` | 2026-10-11 | perf(prefs): serialise preferences read from the cache once, not twice |
| `9cfeeafa7` | 2026-10-11 | perf(harness): start the database without its time zone tables |
| `a49253b6a` | 2026-10-11 | perf(harness): probe a starting stack every second on Docker Engine 25+ |
| `33b48951c` | 2026-10-11 | ci: pull Docker Hub images through the mirror without restarting dockerd |
| `0dc3bd3af` | 2026-10-11 | perf(bootstrap): prune expired e107_tmp rows on one request in a hundred |
| `fe6d6db3f` | 2026-10-11 | fix(harness): keep up's registry-refusal retry to the requested services |
| `88565ae57` | 2026-10-11 | fix(fpw): let an expired reset code stop counting as the outstanding one |
| `d380f785d` | 2026-10-11 | perf(db): let e_db_pdo instances share one connection per set of parameters |
| `c09cb43e7` | 2026-10-11 | perf(session): read and destroy in one statement each, no gc on close |
| `86a12f74d` | 2026-10-10 | fix(schema): let every class column hold the negative of a class id |
| `91ed47ddf` | 2026-10-10 | fix(media, banner, gsitemap, hero, wmessage, pm): honour a class-excluding visibility |
| `4316f245e` | 2026-10-10 | fix(forum, faqs, featurebox): show what a class-excluding visibility hides |
| `a35c64f02` | 2026-10-10 | fix(download): show downloads whose visibility excludes a class |
| `53e50d6bc` | 2026-10-10 | fix(news): show news whose visibility excludes a class |
| `9fd4ebf72` | 2026-10-10 | fix(page): show pages and chapters whose visibility excludes a class |
| `a728fb7a5` | 2026-10-10 | feat(db): carry bound values through the search engine and the models |
| `32afe6198` | 2026-10-10 | refactor(sitelinks): read link visibility through Membership |
| `d7f9d3007` | 2026-10-10 | fix(sublinks): render without a theme linkstyle() |
| `9c534bf3f` | 2026-10-10 | ci(sqli): let the scanner see a fragment bound into execute() |
| `8e6e984ac` | 2026-10-10 | feat(userclass): express a userclass rule as a bound SQL predicate |
| `fa4bd2a97` | 2026-10-10 | Merge pull request #6747 from e107inc/e107help/forum-svg-attachments-as-files |
| `2f57362ab` | 2026-10-10 | Merge pull request #6742 from e107inc/e107help/send-email-failure-checks |
| `b9f59fc27` | 2026-10-10 | Merge pull request #6739 from e107inc/e107help/signup-resend-release-on-failed-send |
| `50981b26b` | 2026-10-10 | Merge pull request #6745 from e107inc/e107help/harness-network-boundary |
| `672b7ddd9` | 2026-10-10 | Merge pull request #6735 from e107inc/e107help/harness-overlay-sandboxes |
| `3915d5b19` | 2026-10-10 | fix(forum): offer an svg attachment as a download wherever it was filed |
| `fb91b8502` | 2026-10-10 | test(harness): refuse and record every connection a run makes past the stack |
| `678be37a2` | 2026-10-10 | test(harness): run the suites in overlay sandboxes, several at once |
| `a5e27c587` | 2026-10-10 | fix(mail): read a failed send as failed wherever the answer is checked |
| `a9514e5c3` | 2026-10-10 | fix(signup): reopen the resend window when the activation email fails |
| `46f0b5fa8` | 2026-10-10 | Merge pull request #6736 from e107inc/e107help/forum-stats-test-flake |
| `46c0fd7aa` | 2026-10-10 | Merge pull request #6705 from e107inc/e107help/harness-image-by-context |
| `32e6a845a` | 2026-10-10 | test: put the URL router back after a test changes the URL configuration |
| `63b553021` | 2026-10-10 | test: stop tests leaning on what other tests leave behind |
| `33f67db3d` | 2026-10-10 | fix(harness): tag the web image by what it is built from |
| `ca24334ad` | 2026-10-09 | Merge pull request #6640 from e107inc/e107help/6422 |
| `ea5c39d35` | 2026-10-09 | Merge pull request #6643 from e107inc/e107help/6601 |
| `e258a2318` | 2026-10-09 | Merge pull request #6615 from e107inc/e107help/6519 |
| `9b0002b11` | 2026-10-09 | Merge pull request #6622 from e107inc/e107help/6385 |
| `6e7a77c72` | 2026-10-09 | Merge pull request #6701 from e107inc/e107help/rename-delayeddb |
| `2f031e954` | 2026-10-09 | Merge pull request #6632 from e107inc/e107help/6514 |
| `3a9d5a2dd` | 2026-10-09 | Merge pull request #6699 from e107inc/e107help/admin-feeds-release-session |
| `fb2e02e12` | 2026-10-09 | Merge pull request #6683 from e107inc/e107help/perf-glyph-lookup |
| `771a94a03` | 2026-10-09 | Merge pull request #6682 from e107inc/e107help/perf-userclass-show-columns |
| `38a54c8e6` | 2026-10-09 | Merge pull request #6681 from e107inc/e107help/perf-strftime-shim |
| `b7ccbfa80` | 2026-10-09 | Merge pull request #6694 from e107inc/e107help/6690 |
| `aae51029e` | 2026-10-09 | test: rename Helper\DelayedDb to Helper\SiteDb |
| `704fc379e` | 2026-10-06 | chore(tests): remove the cPanel deployer and its client submodule |
| `792324f0f` | 2026-10-06 | perf(admin): release the session before the dashboard asks e107.org |
| `5ef89f449` | 2026-10-06 | perf(media): build each precompiled glyph set once per request |
| `095a2bfd8` | 2026-10-06 | perf(shims): reuse each IntlDateFormatter and skip unused strftime() parts |
| `176862407` | 2026-10-06 | perf(userclass): drop the SHOW COLUMNS probe in front of the class tree read |
| `7149e1f47` | 2026-10-04 | fix(core): keep canonical links absolute when the Site URL has no host |
| `76b11c638` | 2026-10-04 | test: borrow core preferences through one helper on the unit base |
| `0a0439a46` | 2026-10-04 | feat(download): give the feed's items the type of the file they enclose |
| `43665ad21` | 2026-09-27 | fix(search): keep the comments search without a plugin search handler |
| `e7645d61e` | 2026-09-27 | perf(social): build Hybridauth only when a caller needs it |
| `d92285e88` | 2026-09-27 | fix(upload): match an upload's category by its owner on the list and form |
| `e64cba8c5` | 2026-09-27 | fix(upload): refuse to accept an upload whose owner has no upload handler |
| `921abcee7` | 2026-09-27 | fix(signup): answer every activation resend alike and ration it per account |
| `32bbac669` | 2026-09-27 | test(acceptance): share the page normaliser FpwRequestCest kept to itself |
| `43eaff6d3` | 2026-09-26 | docs(parser): keep html_truncate() and text_truncate() supported |
| `8550d8cb9` | 2026-09-26 | feat(parser): let truncate() cut at a word boundary |
| `3fe7665a6` | 2026-09-25 | fix(parser): return the rest of the string when usubstr() is given no length |
| `eeb4fad33` | 2026-09-20 | fix(parser): keep the parameter on an unmatched opening bbcode |
| `a3468113b` | 2026-09-20 | fix(search): select every column the search addons weight |
| `25215d809` | 2026-09-16 | fix(signin): hide the sign-in dropdown where a site takes no public login |
| `3b537b849` | 2026-09-14 | fix(parser): trim only an entity the cut left unterminated |
| `00a977d17` | 2026-09-14 | fix(parser): keep an inexact truncation with no space to walk back to |
| `47706a28a` | 2026-09-14 | fix(admin-ui): disable inline editing when the edit route is denied |
| `75eff22ae` | 2026-09-14 | fix(parser): sniff for any registered bbcode, not nine closing tags |
| `898737121` | 2026-09-14 | fix(admin_ui): refuse a delete whose Admin History write failed |

</details>

### Commits

| Commit | Scope |
|---|---|
| `67b6b25c1` | sync(elanguages): align with upstream master 4367279f1 |
| `10204bf46` | sync(ehandlers): align with upstream master 4367279f1 |
| `94da6c82a` | sync(eadmin): align with upstream master 4367279f1 |
| `28a76b0e1` | sync(eweb): align with upstream master 4367279f1 |
| `a59b0c80c` | sync(root): align with upstream master 4367279f1 |
| `223c7f215` | sync(ecore): align with upstream master 4367279f1 |
| `81095a569` | sync(ethemes): align with upstream master 4367279f1 |
| `fe2e8be1c` | audit: rss inventory before removal |
| `61aabc50f` | sync(eplugins/rss_menu): replace Lite rss with upstream rss_menu 4367279f1 |
| `e3515e1bd` | sync(eplugins/chatbox_menu): add from upstream master 4367279f1 |
| `fc43710e4` | sync(eplugins/download): align with upstream master 4367279f1 |
| `8ecdbec6b` | sync(eplugins/featurebox): align with upstream master 4367279f1 |
| `4312c9e80` | sync(eplugins/forum): align with upstream master 4367279f1 |
| `be5ffc22b` | sync(eplugins/news): align with upstream master 4367279f1 |
| `b86181a23` | sync(eplugins/page): align with upstream master 4367279f1 |
| `6d885b6d8` | sync(eplugins/pm): align with upstream master 4367279f1 |
| `c9739b34d` | sync(eplugins/siteinfo): align with upstream master 4367279f1 |
| `dafe98406` | sync(eplugins/tinymce4): align with upstream master 4367279f1 |
| `99bbd9837` | sync(eplugins/login_menu): align with upstream master 4367279f1 |
| `30ee8b52c` | sync(eplugins/login_menu): restore upstream get_coreplugs(), remove marker (7.5) |
| `25f0c519c` | sync(ecore): default_install.xml rss entries back to rss_menu (7.3) |
| `248ad545a` | audit: full report |
| `d0b504bcb` | sync(ehandlers): add 22 upstream-only classes (STOP 1) |
| `2887a564b` | sync(ecore): add dispatch.php, routes.php, logout_template.php (STOP 2) |
| `dda43b6bd` | sync(eplugins/forum): add forum_hidden_token.php (STOP 4) |
| (this commit) | audit: STOP resolutions |

### STOPs and their resolution

| # | Where | What | Instruction / resolution |
|---|---|---|---|
| 1 | `ehandlers/` | 22 upstream-only files (new classes the synced code uses) | add all 22 without changes — `d0b504bcb` |
| 2 | `ecore/` | upstream-only `dispatch.php`, `routes.php`, `templates/logout_template.php` | add all three without changes — `2887a564b` |
| 3 | `eadmin/includes/infopanel.php` | upstream e107.org feed rework (`DashboardFeed`) touches what the `LITE-SKIP` "no phone-home" marker protects | take upstream constructor, disable the feeds with `//LITE-SKIP`, keep `renderFeedTabs()` — **open: second STOP** (`renderFeedTabs()` breaks on the disabled `$this->feeds`), not committed |
| 4 | `eplugins/forum/` | upstream-only `forum_hidden_token.php` | add without changes — `dda43b6bd` |

Details are in the directory sections.

---

## 1. elanguages/ (← e107_languages/)

- One-sided files: none.
- **Replaced (unmarked), 10 files:** `English/English.php` (`LAN_LOGOUT_CONFIRM_QUESTION`, `LAN_SANITISED`,
  `LAN_LINK_SCRIPT_SCHEME_REFUSED`), `English/admin/lan_admin.php` (`LAN_UI_BATCH_ROWS_NO_LONGER_LISTED`,
  `LAN_UI_CHANGE_REFUSED_NO_ARCHIVE`, `LAN_UI_HISTORY_UTF8_SUBSTITUTED`, `LAN_UI_DELETE_REFUSED_NO_ARCHIVE`),
  `English/admin/lan_banlist.php` (`BANLAN_IMPORT_RESTORE_FAILED`, `BANLAN_ENTRY_ALREADY_LISTED`),
  `English/admin/lan_cpage.php` (`CUSLAN_CHAPTER_ROWS_LEFT_OUT`), `English/admin/lan_image.php`
  (`LAN_IMA_FILE_KEPT_OUTSIDE_MEDIA`, `…_AVATARS`), `English/admin/lan_links.php` (`LAN_LINKS_URL_HELP` wording),
  `English/admin/lan_prefs.php` (`PRFLAN_130` wording, `PRFLAN_MULTI_LOGIN_NEEDS_DATABASE_UPDATE`,
  `PRFLAN_SESSION_SAVE_METHOD_*`), `English/admin/lan_upload.php` (`UPLLAN_62` replaced by
  `UPLLAN_ACTIVATION_REFUSED_OWNER_MISSING`; no Lite file uses `UPLLAN_62` any more), `English/lan_signup.php`
  (`LAN_SIGNUP_RESEND_ANSWERED`), `English/lan_usersettings.php` (`LAN_USET_DELETE_EMAIL_UNSENT`).
- Merged around markers: none. Skipped no-target changes: none.

## 2. ehandlers/ (← e107_handlers/)

- **One-sided files — STOP 1 (new, not decided in §7).** Upstream-only, nothing Lite-only. Added upstream in this range;
  the "used by" column lists the synced Lite files that reference them:

  | File | Added in | Used by (Lite, after this sync) |
  |---|---|---|
  | `Admin/AddonUpdateStatus.php` | `792324f0f` perf(admin): release the session before the dashboard asks e107.org | `eadmin/boot.php`, `eadmin/plugin.php`, `eadmin/theme.php`, `eadmin/includes/infopanel.php`, `ecore/shortcodes/batch/admin_shortcodes.php`, `ehandlers/plugin_class.php` |
  | `Admin/DashboardFeed.php` | `8ddc20795` perf(admin): show the dashboard feeds from the cache, renewing behind it | `eadmin/boot.php` (the e107.org feed AJAX endpoints `mode=core&type=feed`, `mode=addons`) — see STOP 3 |
  | `Database/SharedHandles.php` | `d380f785d` perf(db): let e_db_pdo instances share one connection per set of parameters | `ehandlers/e_db_pdo_class.php`, `e_db_interface.php` |
  | `Http/HeaderLinesTrait.php`, `Http/Request.php`, `Http/RequestHandlerInterface.php`, `Http/Response.php` | `ebda05aae` feat(logout): ask before logging out, as the first v3 route | `class2.php` (logout request test), the `Routing/*` and `User/LogoutConfirmation.php` classes, several admin pages |
  | `Routing/Route.php`, `Routing/RouteMatch.php`, `Routing/RouteTable.php` | `ebda05aae` | `ecore/dispatch.php` (STOP 2) |
  | `SessionHandlers/DatabaseSessionHandler.php` | `4d8048b66` refactor(session): move the database session handler into e107\SessionHandlers | `ehandlers/session_handler.php` |
  | `SessionHandlers/NonblockingFilesSessionHandler.php`, `SessionHandlers/SessionId.php` | `8e1ef9ff7` feat(session): add a files save method that does not lock | `ehandlers/session_handler.php` |
  | `Shims/Internal/StrtolowerTrait.php`, `Shims/Internal/StrtoupperTrait.php` | `2ecd7771e` feat(shims): add strtolower() and strtoupper() that fold ASCII in any locale | `ehandlers/Shims/InternalShimsTrait.php` |
  | `SocialLogin/SessionStorage.php` | `b425251d2` fix(social): keep social login from unserialising what it is handed | `ehandlers/user_handler.php` |
  | `Theme/Page.php` | `ebda05aae` | upstream route pages |
  | `User/AccountSession.php`, `User/AccountSessions.php` | `c08ca1cc4` feat(user): keep a record of each account's signed-in sessions | `eadmin/prefs.php`, `ehandlers/session_handler.php`, `user_handler.php`, `user_model.php` |
  | `User/LogoutConfirmation.php` | `ebda05aae` | `class2.php`, `ehandlers/redirection_class.php`, `ecore/routes.php` (STOP 2) |
  | `User/SignInToken.php` | `37d97d50b` feat(user): record each sign-in, check it on every request, end it at sign-out | `ehandlers/session_handler.php`, `user_handler.php`, `user_model.php` |
  | `Userclass/Membership.php` | `8e6e984ac` feat(userclass): express a userclass rule as a bound SQL predicate | 47 Lite files (`page.php`, `comment.php`, `media_class.php`, `login_menu.php`, `download/request.php`, `pm/pm_func.php`, …) |

  **Instruction: add all 22 without changes** — added in `d0b504bcb`, byte-identical to upstream (no `e107_*` directory
  literal in them); `php -l` clean.
- **Replaced (unmarked), 53 files:** `Flood/SourceGate.php`, `Security/SealedToken.php`, `Shims/Internal/StrftimeTrait.php`,
  `Shims/InternalShimsTrait.php`, `admin_ui.php`, `application.php`, `bbcode_handler.php`, `cache_handler.php`,
  `comment_class.php`, `core_functions.php`, `cron_class.php`, `db_debug_class.php`, `e_customfields_class.php`,
  `e_db_interface.php`, `e_db_pdo_class.php`, `e_emote_class.php`, `e_file_inspector.php`, `e_parse_class.php`,
  `e_signup_class.php`, `e_thumbnail_class.php`, `file_class.php`, `form_handler.php`, `iphandler_class.php`,
  `js_manager.php`, `language_class.php`, `login.php`, `mail.php`, `mail_manager_class.php`, `menu_class.php`,
  `menumanager_class.php`, `model_class.php`, `mysql_class.php`, `news_class.php`, `notify_class.php`, `online_class.php`,
  `plugin_class.php`, `pref_class.php`, `redirection_class.php`, `search/comments_download.php`,
  `search/comments_news.php`, `search/comments_page.php`, `search/search_comment.php`, `search_class.php`,
  `session_handler.php`, `shortcode_handler.php`, `sitelinks_class.php`, `theme_handler.php`,
  `token_injector_handler.php`, `user_handler.php`, `user_model.php`, `userclass_class.php`, `validator_class.php`,
  `xml_class.php`.
- **Merged around markers:**
  - `e107_class.php` — 16 upstream hunks, all outside `coreUpdateAvailable()` (marker "no phone-home"); clean 3-way merge.
    The directory arrays in `overridableDirs()` / `defaultDirs()` keep the Lite names (§2).
  - `media_class.php` — `typedCategories`, `commonCategory()` / `importCategory()`, `Membership` predicates, icon-list
    cache, upload rename checks taken; the marker line (default order `media_id DESC`, "media_category ASC dropped") kept.
    The file differs from upstream only by that marker line.
- **Marked, no upstream change:** `message_handler.php`.
- Skipped no-target changes: none.

## 3. eadmin/ (← e107_admin/)

- **One-sided files (decided in §7):** upstream-only `includes/{categories,classis,combo,compact,tabbed}.php` not added;
  Lite-only `includes/dashboard.php` kept (not synced).
- **Replaced (unmarked), 18 files:** `boot.php`, `cache.php`, `comment.php`, `cpage.php`, `cron.php`, `history.php`,
  `image.php`, `lancheck.php`, `links.php`, `mailout.php`, `menus.php`, `phpinfo.php`, `prefs.php`, `search.php`,
  `upload.php`, `users.php`, `users_extended.php`, `wmessage.php`. (`boot.php` already served the e107.org feed AJAX
  requests before this round; it now renews them through `DashboardFeed`.)
- **Merged around markers:**
  - `admin.php` — one commented debug line removed upstream; the `dashboard` whitelist marker kept (revert condition
    not met: upstream still allows only `infopanel` / `flexpanel`).
  - `banlist.php` — upstream moves the `WHERE` of both `$listQry` into `$listScope`. git reported a conflict only because
    the line sits next to the marked `$perPage = 100` line. Judged by subject: the marker protects `$perPage`, the
    upstream change is the query. Resolution: both marked `$perPage = 100` lines kept, upstream `$listQry` /
    `$listScope` taken. The file differs from upstream only by the two marked lines.
  - `newspost.php` — `limitedToAuthor()` list scope, `USER_TITLE` parse context, `asElementContent()` for the news body,
    `category_manager` classlist taken; the two `news_id` markers (#84) kept.
  - `plugin.php` — `AddonUpdateStatus::clear()` and the float classes taken; the four markers kept.
  - `theme.php` — `AddonUpdateStatus::clear()` and the float classes taken; the marker (commented `main/online`,
    `main/upload`, `convert/main`) kept.
  - `update_routines.php` — 116/27 upstream lines taken; the three `admin-exas-core.css` markers kept.
  - `includes/infopanel.php` — taken: `AddonUpdateStatus` check for the addons-update notice, float classes on the
    comment buttons. **STOP 3** for the rest, see below.
- **Skipped, no target:** `includes/flexpanel.php` — upstream's only change (news tabs built by the new
  `renderFeedTabs()`) targets the e107.org feed panel that the `LITE-SKIP` block (lines 222–237) keeps commented out.
  The file is unchanged. Same for the `$text2 = …renderFeedTabs()…` line in `infopanel.php` (target in `LITE-SKIP`
  lines 300–308).
- **§7 decision:** `ver.php` unchanged (`2.4.0.6 (git)`, marker kept); upstream did not change it.
- **Marked, no upstream change:** `auth.php`, `header.php`, `ver.php`.

**STOP 3 — `eadmin/includes/infopanel.php`** (upstream `8ddc20795` "show the dashboard feeds from the cache, renewing
behind it"). The `LITE-SKIP` marker says "Lite removes the e107.org admin RSS feed entirely (no phone-home).
e-adminfeed panel + ADMINFEEDMORE dropped. See Lite #88." In Lite the feed panel is commented out, but the three jQuery
`.load()` calls that fetch the feeds were left in the constructor, unmarked (they were taken from upstream in round 1).
Upstream now replaces those three calls with a `DashboardFeed` object per feed and a renewal script, and adds
`renderFeedTabs()` to show the cached copy. This is the subject of the marker (the e107.org feed), so it is not applied.

Lite (kept, `infopanel.php:101–110`, constructor):
```php
$code = "
jQuery(function($){
    $('#e-adminfeed').load('".e_ADMIN."admin.php?mode=core&type=feed&e-token=".defset('e_TOKEN')."');
    $('#e-adminfeed-plugin').load('".e_ADMIN."admin.php?mode=addons&type=plugin&e-token=".defset('e_TOKEN')."');
    $('#e-adminfeed-theme').load('".e_ADMIN."admin.php?mode=addons&type=theme&e-token=".defset('e_TOKEN')."');
    ".$coreUpdateCheck."
    ".$addonUpdateCheck."
});
";
```
Upstream (`4367279f1`):
```php
private $feeds = array();   // new property
…
$cache = e107::getCache(); $xml = e107::getXml(); $tp = e107::getParser();
$this->feeds = array(
    'e-adminfeed'        => array('query' => 'mode=core&type=feed',     'feed' => new e107\Admin\DashboardFeed('core', $cache, $xml, $tp)),
    'e-adminfeed-plugin' => array('query' => 'mode=addons&type=plugin', 'feed' => new e107\Admin\DashboardFeed('plugin', $cache, $xml, $tp)),
    'e-adminfeed-theme'  => array('query' => 'mode=addons&type=theme',  'feed' => new e107\Admin\DashboardFeed('theme', $cache, $xml, $tp)),
);
$feedRenewals = '';
foreach($this->feeds as $id => $panel)
{
    if($panel['feed']->isDue())
    {
        $feedRenewals .= "$('#".$id."').load('".e_ADMIN."admin.php?".$panel['query']."&e-token=".defset('e_TOKEN')."');\n";
    }
}
$code = "jQuery(function($){ ".$feedRenewals." ".$coreUpdateCheck." ".$addonUpdateCheck." });";
…
protected function renderFeedTabs() { … $this->feeds[…]['feed']->copy() … }   // new method
```
Context: Lite's admin style is `dashboard` (`eadmin/includes/dashboard.php`, forced by `auth.php`), which does not extend
`adminstyle_infopanel`; `infopanel` / `flexpanel` run only if that style is chosen. Options: (a) keep Lite as is
(feeds still fetched when the infopanel style is used — as today); (b) take upstream (feeds fetched only when due,
cached copy shown in the — commented-out — panel); (c) remove the three `.load()` calls too, consistent with the marker.
Note that `eadmin/boot.php` (unmarked) is the endpoint that asks e107.org.

**Instruction (STOP 3):** take the upstream constructor, then disable the e107.org feeds with `//LITE-SKIP` line by
line (the `$this->feeds` array, the loop that builds `$feedRenewals`, every line that only exists to load the feeds),
first line carrying the reason `//LITE-SKIP Lite removes the e107.org admin feeds entirely (no phone-home). See Lite #88.`;
keep the `AddonUpdateStatus` check; keep `renderFeedTabs()` and the existing `//LITE-SKIP` panel block; STOP if
`renderFeedTabs()` or anything else would reference the disabled `$this->feeds` and break.

**Result — second STOP, nothing committed.** The prepared change (php -l clean) is: the upstream `$feeds` property,
the constructor with 16 new `//LITE-SKIP` lines (reason line, `$cache` / `$xml` / `$tp` — used only by the feeds,
the five-line `$this->feeds = array(…)`, the seven-line `foreach` loop), `$feedRenewals = '';` left active because the
`$code` string below interpolates `".$feedRenewals."` (commenting it out would raise an undefined-variable warning on
every infopanel load), and upstream `renderFeedTabs()`. With that change the file issues no e107.org feed request:
the only active jQuery requests left are `admin.php?mode=core&type=update` (answered by `e107::coreUpdateAvailable()`,
which returns `false` — "no phone-home" marker) and `admin.php?mode=addons&type=update` (the `AddonUpdateStatus` check
kept by the instruction; `eadmin/boot.php` answers it through `admin_shortcodes::getUpdateable()` →
`e_marketplace::getVersionList()`, an add-on version check, not a feed — unchanged from `main`).

The collision: upstream `renderFeedTabs()` (would be `infopanel.php:208–217`) reads
`$this->feeds['e-adminfeed']['feed']->copy()` (and the plugin / theme entries) and `ADMINFEEDMORE`. With the feeds
disabled `$this->feeds` stays `array()`, so a call would raise "Undefined array key" warnings and then the fatal
`Call to a member function copy() on null`; `ADMINFEEDMORE` is not defined either (its `define()` is under `LITE-SKIP`
at `infopanel.php:19–21`; PHP 8 throws an `Error` for an undefined constant). Today nothing calls it: its only two call
sites are the commented-out panel blocks (`infopanel.php:300–308` and `flexpanel.php:222–237`, both `LITE-SKIP`), and
`git grep renderFeedTabs` finds no other caller. So it is dead code that breaks only when called. Waiting for an
instruction.

## 4. eweb/ (← e107_web/)

- One-sided files: none. Marked files: none.
- **Replaced (unmarked), 4 files:** `js/core/admin.jquery.js`, `js/core/all.jquery.js`, `js/core/front.jquery.js`,
  `js/inline.php`.

## 5. Root

- **One-sided files:** all decided in §7 — upstream-only `.editorconfig`, `banner.php`, `composer.json`, `composer.lock`,
  `contact.php`, `download.php`, `email.php`, `gsitemap.php`, `online.php`, `print.php`, `request.php`, `search.php`,
  `submitnews.php`, `top.php`, `unsubscribe.php`, `upload.php`, `userposts.php` not added; Lite-only `README.sk.md`,
  `README_links.md`, `REGISTRY-RULES.md` kept. `README.md` (changed upstream), `.gitignore`, `e107.robots.txt` kept as in
  Lite. `LICENSE`: unchanged upstream. `.gitmodules`: not in the new upstream tree, not in Lite.
- **Replaced (unmarked):** `comment.php`, `fpw.php`, `index.php` (now `require_once(e_CORE.'dispatch.php')` with
  `$_E107['route_table']` — depends on STOP 2), `usersettings.php`.
- **Merged (unmarked, directory mapping only):** `class2.php` — temp prune 1 in 100 requests, logout without token
  redirects to the logout confirmation (`\e107\User\LogoutConfirmation`, `\e107\Http\Request` — STOP 1), cookie path via
  `e107::cookiePath()`, deprecated `get_user_classes()` doc, error handler escaping / Xdebug 3 detection; the two
  mapped defaults (`ehandlers/`, `eplugins/`) kept.
- **Merged around marker:** `page.php` — `Membership` predicates for chapters / pages / menus, page template parsed before
  `[[PAGE]]` is substituted, `[newpage]` split with `e_parse::splitAtBBCodeTags()`; the 404 marker block untouched.
  The file differs from upstream only by the ten marked lines.
- **Marked, no upstream change:** `thumb.php`. Unchanged upstream: `e107.htaccess` (Lite mapping kept).
- **§7 — `install.php`:** Lite version kept, nothing taken. Upstream changes in this range not taken:
  - `143b173f6` fix(install): accept PHP 5.6 again — `MIN_PHP_VERSION` `'8.0'` → `'5.6'`.
  - `0e85c7d7d` fix(install): stop naming E_STRICT in the installer's error handler — `case E_STRICT` removed from
    `installLog`.
  - `fffe0748e` fix(cache): make OPcache forget a PHP file e107 replaces or deletes — three `opcache_invalidate()`
    blocks replaced by `e107::invalidateOpcache()`.
  - `410ac6170` fix(e107): keep path info, markup and other hosts out of the web path — cookie paths through
    `e107::cookiePath()` (`install_set_state_cookie()`, `install_clear_state_cookie()`, `cookie()`).
  - `5cc916a33` fix(ui): name every bundled Bootstrap on core's right floats — `pull-right float-right float-end` in
    the installer template (two places).

## 6. ecore/ (← e107_core/)

- **One-sided files decided in §7:** Lite-only `override/controllers/news/sef_url.php`, `override/url/news/sef_url.php`,
  `override/url/usersettings/{index.html,sef_url.php,url.php}`, `templates/bootstrap5/{login,membersonly,signup,user}_template.php`
  kept; upstream-only `templates/bootstrap4/user_template.php` not ported; `shortcodes/batch/submitnews_shortcodes.php`,
  `templates/submitnews_template.php`, `shortcodes/single/{news_categories,news_category,newsfile,newsimage}.sc`,
  `templates/legacy/*` pending, not added.
- **One-sided files — STOP 2 (new):** upstream-only, added in `ebda05aae` "ask before logging out, as the first v3 route":
  - `dispatch.php` — the v3 front controller; required by the synced root `index.php`.
  - `routes.php` — the route table (the logout confirmation route).
  - `templates/logout_template.php` — the "Are you sure you want to log out?" page (`LAN_LOGOUT_CONFIRM_QUESTION`,
    synced in `elanguages/`).

  **Instruction: add without changes** — added in `2887a564b`, byte-identical to upstream (no `e107_*` directory
  literal); `php -l` clean.
- **Replaced (unmarked), 31 files:** `bbcodes/{bb_glyph,bb_img,bb_video}.php`, `bbcodes/{email,file,flash,hide,link,quote,sanitised,stream,url}.bb`,
  `controllers/system/xup.php`, `shortcodes/batch/{navigation,news_archive,news,page}_shortcodes.php`,
  `shortcodes/single/breadcrumb.sc`, `shortcodes/single/{iconpicker,imagepreview,imageselector,lan,nextprev,sitelinks_alt,sublinks,uploadfile,wmessage}.php`,
  `sql/core_sql.php`, `templates/{admin,page}_template.php`, `templates/footer_default.php`.
- **Merged around markers:**
  - `shortcodes/batch/admin_shortcodes.php` — `AddonUpdateStatus` in the addon update notice, new
    `renderAddonUpdateNotice()`, float classes; the three markers (`sc_admin_logo()` whole method, admin template override,
    external links hidden) untouched.
  - `xml/default_install.xml` — upstream removes the `users` entry from the `search` pref; the XML marker and the Lite
    admin prefs kept. The `rss` keys: see §7.3 below.
- **Marked, no upstream change:** `shortcodes/batch/login_shortcodes.php`, `shortcodes/batch/user_shortcodes.php`,
  `shortcodes/single/custom.php`, `templates/admin_icons_template.php`, `url/user/url.php`.

## 7. ethemes/ (← e107_themes/)

- `_blank`, `voux` (upstream-only; `voux` changed upstream): **skipped by §7**. `backend` (Lite-only): not synced.
  `ethemes/index.html`: kept.
- `bootstrap3`, `bootstrap5`: **taken fully from upstream (§7)**.
  - **Changed (2):** `bootstrap3/templates/page_template.php`, `bootstrap3/theme_shortcodes.php`.
  - **Deleted Lite-only files:** none. **Added upstream-only files:** none.
  - After the replace the only differences to upstream are the two mapped `href=&quot;eadmin/admin.php&quot;` lines in
    `bootstrap3/install/install.xml` and `bootstrap5/install/install.xml` (as before).
- §7.6 Lite-only fixes checked in `main` before the run, all as described: `eadmin/auth.php:30` forces the Lite admin
  theme (marker); `ethemes/backend/theme_shortcodes.php:274–276` logout link carries `e-token`;
  `ecore/shortcodes/batch/admin_shortcodes.php:2245–2246` external links behind `if(false /* getperms('0') */)`;
  `eadmin/includes/dashboard.php` and `ethemes/backend/templates/dashboard_template.php` present with the dashboard fixes —
  none of them synced. After the run: unchanged (`auth.php`, `backend/*`, `dashboard.php` not touched; the
  `admin_shortcodes.php` marker moved to lines 2285–2286 by the upstream lines added above it).

## 8. eplugins/ (← e107_plugins/)

### rss: Lite modifications (inventory before removal)

Written and committed before any change to `eplugins/rss` (`fe2e8be1c`). Compared: `eplugins/rss` in `main` (`7296f21be`) with upstream `e107_plugins/rss_menu` at the new baseline
`4367279f1`. Pure mapping noise is left out: `rss` ↔ `rss_menu` in `e107::url()`, `isInstalled()`, `getScBatch()`,
`getTemplate()`, `tablerender()` id, `e_PLUGIN` / `e_PLUGIN_ABS` path literals, the `rss_addons::folders()` folder name,
class names `rss_url` / `rss_shortcodes` / `rss_setup` / `rss_rss`, `rss_path` value `'rss'` in `rss_setup::upgrade_post()`
and the doc-comment mentions. Every function named below was read in full in both versions.

#### A. Lite modifications

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

#### B. Differences that are not Lite modifications (upstream changes after the previous baseline `7ce7a121c`)

These differences exist only because `main` carries the previous baseline; they are not Lite code.

| Lite file:line | Upstream commit | Difference |
|---|---|---|
| `rss.php:218`, `:317` | `893d5076c` fix(parser): expand shortcodes only in text an administrator wrote | Upstream adds `author_id` to `rssCreate::ITEM` and to each item. |
| `rss.php:481–484`, `:561`, `:568`, `:572`, `:579`, `:589`, `:609`, `:677–682`, `:741–745`, `:757–762`, `:812`, `:829` | `893d5076c` | Upstream renders titles, descriptions, `content:encoded`, authors, categories and custom tags with `$tp->toRssForAuthor($text, $authorId)` (shortcodes only for administrator-written text); Lite still uses `$tp->toRss()`. |
| `e_rss.php:146–161` (`whereClassPermits()`), `:178`, `:190`, `:203–205`, `:218` | `53e50d6bc` fix(news): show news whose visibility excludes a class | Upstream uses `\e107\Userclass\Membership::current()->predicate()` for the class columns; Lite uses `e_CLASS_REGEXP` / `whereIn(USERCLASS_LIST)`. |
| `e_rss.php:190–195`, `:203–207`, `:218–219` | `2fd757cea` fix(comments): ask comment switches and the member list before publishing | Upstream also requires `news_allow_comments = 0`, `download_comment > 0`, `page_comment_flag > 0`. |
| `e_rss.php:210` | `2f22061c5` fix(comments): publish no forum poll's comments in the menu or the feed | Upstream adds `poll_type = 1`. |
| `plugin.xml:3` | `893d5076c` | Upstream metadata `date="2026-10-16"` (Lite metadata kept by marker anyway). |

#### C. Lite-only files and file names

- `README.md` (18 lines) — Lite-only: "e107 RSS Menu plugin commissioned by hpkizi.sk o.z. and jointly open sourced",
  customised version of the core RSS Menu plugin, PHP 7.4–8.2, "Recommended for e107 Lite 2.3.4", development disclaimer.
- `languages/English_admin.php` — byte-identical to upstream `languages/English_admin_rss_menu.php` (only the file name
  differs; Lite loads it with `e107::lan('rss', true)`).
- Identical to upstream: `e_meta.php`, `e_url.php`, `rss_resolver.php`, `rss_shortcodes.php` (mapping only),
  `languages/English_global.php`, `rss_sql.php`, `templates/rss_template.php`, the six images.

#### D. Lite `plugin.xml` metadata

| | Lite | upstream `4367279f1` |
|---|---|---|
| `version` | `2.1` | `1.4` |
| `date` | `2026-08-01` | `2026-10-16` |
| `compatibility` | `2.4` | `2.0` |
| `<author>` | `name="Jimmi" url="https://www.e107sk.com"` | `name="e107 Inc." url="https://e107.org"` |
| marker | `<!-- LITE MODIFICATION: Lite plugin metadata (version/date/compatibility/author), never take upstream -->` | — |

Name, LAN keys, description, category, copyright, adminLinks and pluginPrefs are the same. Lite ends the file with a
newline, upstream does not.

### §7.3 rss → rss_menu

- **7.3.1 + 7.3.2 (`61aabc50f`):** `eplugins/rss` deleted entirely (22 files); upstream `e107_plugins/rss_menu` copied to
  `eplugins/rss_menu` (21 files). `git diff upstream/master:e107_plugins/rss_menu HEAD:eplugins/rss_menu` is empty — the
  folder is byte-identical to upstream (no `e107_*` directory literal in it). Gone with the folder: the Lite-only
  `README.md`, the Lite file name `languages/English_admin.php` (upstream `languages/English_admin_rss_menu.php`, same
  content), the Lite `plugin.xml` metadata and every Lite modification listed in the inventory above.
- **7.3.3 — references outside the plugin.** Searched the whole tree (outside `eplugins/rss_menu` and `audit/`) for
  `e107::url('rss'`, `isInstalled('rss')`, `e_PLUGIN.'rss/'`, `e_PLUGIN_ABS.'rss/'`, `eplugins/rss/`, `{e_PLUGIN}rss/`,
  `e107::lan('rss'`, `getScBatch('rss'`, `getTemplate('rss'`, `rss_rss`, `class rss_*`, `'rss' =>` / `=> 'rss'`, and
  compared the `rss_menu` count of every Lite file with its upstream counterpart. Result: the only Lite-name uses were the
  five keys in `ecore/xml/default_install.xml` (marker there is for the admin theme, not for rss — no STOP). Changed in
  `25f0c519c`:

  | file:line | pref | before | after |
  |---|---|---|---|
  | `ecore/xml/default_install.xml:120` | `e_meta_list` | `'rss' => 'rss'` | `'rss_menu' => 'rss_menu'` |
  | `ecore/xml/default_install.xml:129` | `e_rss_list` | `'rss' => 'rss'` | `'rss_menu' => 'rss_menu'` |
  | `ecore/xml/default_install.xml:148` | `e_sql_list` | `'rss' => 'rss_sql'` | `'rss_menu' => 'rss_sql'` |
  | `ecore/xml/default_install.xml:152` | `e_url_list` | `'rss' => 'rss'` | `'rss_menu' => 'rss_menu'` |
  | `ecore/xml/default_install.xml:254` | `plug_installed` | `'rss' => '1.4'` | `'rss_menu' => '1.4'` |

  Confirmed already in the upstream form (unmarked files, replaced by §4 this or an earlier round):
  `ehandlers/plugin_class.php:100` (core plugin list `"rss_menu"`), `ethemes/bootstrap3/theme.xml:10` and
  `ethemes/bootstrap5/theme.xml:10` (`<plugin name='rss_menu' …>`), the commented links in
  `eplugins/forum/templates/forum_viewforum_template.php:183–185` and `forum_viewtopic_template.php:185`.
  Not plugin references (left as they are): the MIME map key `'rss'` in `ehandlers/application.php:4165`,
  `ehandlers/file_class.php:4159`, the vendor `MimeType.php`; the `rss` table in the theme `install.xml` files (the table
  name is `rss` upstream too); the `Jimako-e107-plugins/rss_menu` repository links in `README.md:16` and
  `README_links.md:16` (§7.1 keeps both files). After the change `default_install.xml` differs from upstream only by the
  Lite admin prefs and the XML marker.
- **7.3.4 — XML check.** `xmlClass::loadXMLfile(…, 'advanced')` (Lite `ehandlers/xml_class.php`, with a stub `e107` class
  providing `getParser()` / `getLog()` / `getFile()` / `getMessage()`) on the file before (`223c7f215`) and after
  (`25f0c519c`): both give 3 top-level keys (`@attributes`, `prefs`, `database`), no `comment` key; `var_export` of the
  two arrays differs only in the five keys above (`md5(serialize())` `847e463003…` → `73d7cb511d…`).
- **Existing installations** have the plugin registered as `rss` (`plug_installed['rss']`, the `e_*_list` prefs, the
  `rss_path` values of their feed rows, menu entries for `rss/rss_menu`). After this change the folder is `rss_menu`:
  the plugin has to be uninstalled as `rss` and installed again as `rss_menu`. **No migration is provided.**

### §7.4 chatbox_menu

`e3515e1bd`: upstream `e107_plugins/chatbox_menu` copied to `eplugins/chatbox_menu` (19 files), byte-identical to
upstream. The only `e107_*` strings are in comments (`e_dashboard.php:16` `@package e107_plugins`,
`languages/English/English.php:11` CVS `$Source`), kept per §2.

#### chatbox_menu: dependencies

Read: `chatbox_menu.php`, `chat.php`, `chatbox_menu_shortcodes.php`, `admin_chatbox.php`, `e_dashboard.php`, `e_list.php`,
`e_notify.php`, `e_rss.php`, `e_search.php`, `e_user.php`, `plugin.xml`, the template.

Root files not in Lite:

| file:line | target | context |
|---|---|---|
| `e_search.php:18–62` | `search.php` | The `e_search` addon (`chatbox_menu_search`) is called by the root `search.php` only, which Lite does not have; `eadmin/search.php:42–49` only registers it in the search prefs. Inert in Lite. |
| `chat.php:108–115`, `:150–174` | `search.php` | Handles the `?<id>.fs` query that a search result link (`e_search.php:54`) carries. Works on its own; nothing in Lite produces such a link. |
| `e_list.php:56` | `user.php` | inside a comment — not rendered. |

`login.php` / `signup.php` (`chatbox_menu.php:167`, `:173`, through `e_LOGIN` / `e_SIGNUP`) exist in Lite.

Plugins not in Lite:

| plugin | file:line | guarded? |
|---|---|---|
| list_new | `e_list.php:14` (`class list_chatbox_menu`) | not needed — the addon is loaded only by list_new itself (upstream `e107_plugins/list_new/list_class.php:550`); inert in Lite |

No other plugin is called. `e_rss.php` is consumed by `rss_menu` (now in Lite); `e_dashboard.php` by the Lite dashboard
(`eadmin/includes/dashboard.php:473`, `:542`, `e107::getAddonConfig('e_dashboard', …)`); `e_notify.php` by core notify;
`e_user.php` by the core user profile addons. The only `isInstalled()` calls are for `chatbox_menu` itself.

Anything else it needs (all present in Lite): `floodprotect` (`class2.php:1516`) and `FLOODTIMEOUT` (`class2.php:1135`,
defined whenever flood protection can refuse a post), `r_emote()` (`ehandlers/core_functions.php:458`), the JS
`sendInfo()` / `expandit()` / `storeCaret()` (`eweb/js/core/all.jquery.js:2053`, `:1702`, `:1893`), the `user_chats` column
(`ecore/sql/core_sql.php:611`), `notify::sendForAuthor()` (`ehandlers/notify_class.php:151`), `e_search`
(`ehandlers/search_class.php:23`), QueryBuilder `increment()` / `decrement()` / `fetchEach()` / `orderByDesc()` / `limit()`
(`ehandlers/Database/QueryBuilder.php:2485`, `:2500`, `:2712`, `:1677`, `:1750`), the `user/profile/view` URL
(`ecore/url/user/`), `LAN_SEARCH_7` / `LAN_SEARCH_61` (`elanguages/English/lan_search.php:29`, `:81`, used only by the
inert `e_search.php`). `chatbox_menu` now also appears in the login menu statistics (§7.5).

### §7.5 login_menu — coreplugs marker

Checked in this branch after the `login_menu` sync (`99bbd9837`), whole functions read in
`eplugins/login_menu/login_menu_class.php`:

1. `parse_external_list()` (`:135–195`) calls the stats methods with `call_user_func(array($this, "get_{$item}_stats"))`
   (`:170`) — on the instance. **Pass.**
2. `get_forum_stats()` (`:198–222`) and `get_chatbox_menu_stats()` (`:225–244`) write `$lbox_stats[0][…]`; the renderer
   `sc_lm_plugin_stats()` (`login_menu_shortcodes.php:455–500`) and `get_stats_total()` (`:337–360`) iterate
   `$lbox_infos['stats'][$plugin]` as a list of items and read `$lbox_item['stat_new']` etc. — index `0` is that item.
   **Pass.**
3. Both read `(int) defset('USERLV', 0)` (`:216`, `:240`). `e107forum::visibleForumIds()` exists as a public static method
   in `eplugins/forum/forum_class.php:1086` (calls the static `permList()`, `:1056`). **Pass.**
4. Neither `eplugins/forum/e_loginbox.php` nor `eplugins/chatbox_menu/e_loginbox.php` exists, so no duplicate entries.
   **Pass.**

All checks pass: `get_coreplugs()` taken from upstream (`$list = array('forum', 'chatbox_menu');`) and the marker
removed (`30ee8b52c`, **removed by instruction**). `login_menu_class.php` is now identical to upstream.

### Plugins in both Lite and upstream

| Plugin | One-sided files | Result |
|---|---|---|
| download | none | Replaced (unmarked): `download_shortcodes.php`, `e_gsitemap.php`, `e_list.php`, `e_notify.php`, `e_rss.php`, `e_search.php`, `e_sitelink.php`, `e_tagwords.php`, `e_upload.php`, `handlers/category_class.php`, `handlers/download_class.php`, `includes/admin.php`, `plugin.xml`. Merged around markers: `request.php` — `Membership` predicate in `check_download_limits()` (two hunks), outside the five marked regions; clean. Marked, no upstream change: `e_url.php`. |
| featurebox | none | Replaced: `admin_config.php`, `featurebox.php`, `includes/category.php`, `includes/tree.php`, `plugin.xml`, `templates/featurebox_template.php`. |
| forum | upstream-only `forum_hidden_token.php` (**STOP 4**) | Replaced (unmarked), 22 files: `e_list.php`, `e_notify.php`, `e_print.php`, `e_rss.php`, `e_search.php`, `forum.php`, `forum_admin.php`, `forum_attachments.php`, `forum_class.php`, `forum_post.php`, `forum_setup.php`, `forum_stats.php`, `forum_viewforum.php`, `forum_viewtopic.php`, `newforumposts_menu.php`, `plugin.xml`, `shortcodes/batch/{forum,post,stats,view,viewforum}_shortcodes.php`, `templates/forum_template.php`. `forum_update.php` (unchanged upstream) keeps its earlier Lite text `efiles/public`. |
| login_menu | none | Replaced (unmarked): `login_menu.php` (`Membership` predicate for new news). Then §7.5 on `login_menu_class.php`. Marked, no upstream change: `login_menu_shortcodes.php`, `login_menu_template.php`. |
| navigation | none | Identical to upstream — no change. |
| news | none | Replaced: `e_featurebox.php`, `e_gsitemap.php`, `e_related.php`, `e_rss.php`, `e_search.php`, `e_sitelink.php`, `news.php`, `news_archive_menu.php`, `news_months_menu.php`, `other_news2_menu.php`, `other_news_menu.php`, `plugin.xml`. |
| page | none | Replaced: `chapter_menu.php`, `e_related.php`, `e_search.php`, `e_shortcode.php`, `e_sitelink.php`, `page_menu.php`, `plugin.xml`. |
| pm | none | Replaced: `e_shortcode.php`, `plugin.xml`, `pm_class.php`, `pm_func.php`, `templates/pm_template.php`. |
| siteinfo | none | Replaced: `e_shortcode.php`, `plugin.xml`. |
| tinymce4 | none | Replaced: `plugin.xml`, `plugins/e107/parser.php`. `wysiwyg.php:27` / `wysiwyg_class.php` keep the earlier mapping (unchanged upstream). |
| user | none | Identical to upstream — no change. |

**STOP 4 — `eplugins/forum/forum_hidden_token.php`** (upstream-only, added in `8824601da` "fix(forum): quote a post as a
[quote] block again"; 28 lines, `class forum_hidden_token extends \e107\Security\SealedToken` with `issuer()` returning
`null`). The synced `forum_class.php:3593–3602` (`hiddenToken()`) `require_once`s it; it is reached from
`sealHidden()` (`post_shortcodes.php:259`, quoting a post that has `[hide]` blocks) and `unsealHidden()`
(`forum_viewtopic.php:294`, `$e_hide_unseal` callback). Without the file those paths end in a fatal error.
`\e107\Security\SealedToken` is present (`ehandlers/Security/SealedToken.php`). **Instruction: add without changes** —
added in `dda43b6bd`, byte-identical to upstream; `php -l` clean.

### forum: dependencies (changes since the previous round only)

- **poll:** the preview (`forum_post.php:1135`) and the new-thread poll (`forum_post.php:1229`, `$hasPoll`) are now
  guarded — both call `e107forum::canAttachPoll()` (`forum_class.php:815–818`: `e107::isInstalled('poll') &&
  check_class($prefs->get('poll'))`), and so does `sc_forum_post_poll()` (`post_shortcodes.php:481–489`). Still
  **unguarded**: `forum_post.php:221` → `submitPoll()` (`:283–286`, `require_once(e_PLUGIN.'poll/poll_class.php')`) on any
  POST with `submitpoll`. `forum_viewtopic.php:198–204` loads `poll_class.php` only when a poll row exists.
- Root links moved: `email.php` / `print.php` now `shortcodes/batch/view_shortcodes.php:940–941` (were `:948–949`); the
  `pm` guards now `view_shortcodes.php:516`, `:889`. The other root files listed in round 1 are unchanged.
- **New:** `forum_hidden_token.php` (STOP 4); `\e107\Userclass\Membership` (`forum_attachments.php:339`, STOP 1);
  `\e107\Security\Jwe\Compact::MAX_LENGTH` (`forum_class.php:3565`, present in `ehandlers/Security/Jwe/Compact.php`).
- The login menu now asks `get_forum_stats()` (§7.5).

### pm: dependencies (changes since the previous round only)

- **New:** `\e107\Userclass\Membership` (`pm_func.php:111`, STOP 1). `pm_class.php` compares `sendEmail()` with `=== true`.
  Nothing else.

### Skipped (report only)

- `githubSyncLite` (Lite-only) — skipped by §7.
- Upstream-only plugins skipped by §7: `_blank`, `admin_menu`, `alt_auth`, `banner`, `blogcalendar_menu`, `comment_menu`,
  `contact`, `faqs`, `gallery`, `gsitemap`, `hero`, `import`, `linkwords`, `list_new`, `newforumposts_main`, `newsfeed`,
  `newsletter`, `online`, `poll`, `search_menu`, `signin`, `social`, `tagcloud`. Changed upstream in this range (not
  taken): `_blank`, `banner`, `blogcalendar_menu`, `comment_menu`, `faqs`, `gallery`, `hero`, `linkwords`, `list_new`,
  `online`, `poll`, `signin`, `social`, `tagcloud`.

---

## Markers

Counted with `git grep` over the tracked `*.php`, `*.js`, `*.css` files (combined) and `*.xml` (separately), lines.

| Marker | before (php/js/css) | before (xml) | after (php/js/css) | after (xml) |
|---|---|---|---|---|
| `LITE MODIFICATION` | 61 | 2 | 52 | 1 |
| `LITE FEATURE` | 3 | 0 | 3 | 0 |
| `LITE-SKIP` | 57 | 0 | 57 | 0 |

- **Removed with the plugin (§7.3)** — 8 php + 1 xml:
  `eplugins/rss/admin_prefs.php:264` (target `_blank`), `eplugins/rss/rss.php:463` (RSS 0.92 `SITEURL`), `:494`
  (RSS 2.0 sitebutton / `SITEURL`), `:532` (image sitebutton, no 88x31), `:575` (category domain optional), `:636`
  (RDF `SITEURL`), `:724` (Atom `<logo>`), `eplugins/rss/rss_setup.php:28` (news seed guard), and
  `eplugins/rss/plugin.xml:2` (Lite metadata). The `// LITE:` note in `rss_addons.php:205` is not a counted marker.
- **Removed by instruction (§7.5)** — 1 php: `eplugins/login_menu/login_menu_class.php:64` (coreplugs).
- Added: none. (The 16 `LITE-SKIP` lines of the STOP 3 instruction are not committed — second STOP; the counts above are
  the committed state after the STOP 1 / 2 / 4 commits.)

## Checks

- `php -l` (PHP 8.3.6) on all 227 added/modified PHP files of the branch (`git diff --name-only --diff-filter=AM
  main..HEAD -- '*.php'`): no errors. Done per directory before each commit as well.
- `git diff --name-only main..HEAD`: `elanguages/` (10), `ehandlers/` (77: 55 modified, 22 added), `eadmin/` (25), `eweb/` (4), root (6:
  `class2.php`, `comment.php`, `fpw.php`, `index.php`, `page.php`, `usersettings.php`), `ecore/` (36: 33 modified, 3 added), `ethemes/` (2),
  `eplugins/` (135 paths with `--no-renames`: 22 `rss` deleted, 21 `rss_menu` added, 19 `chatbox_menu` added, 1 forum file added, 72 modified; 115 with rename detection) — plus `audit/` (this report).
- `git status` clean, no `.rej` / `.orig`.
- Branch `sync-upstream-2026-10-23` pushed; no merge, no PR, nothing pushed to `main`.
