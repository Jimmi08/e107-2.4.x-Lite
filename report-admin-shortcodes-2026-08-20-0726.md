# Report — realign `ecore/shortcodes/batch/admin_shortcodes.php` with upstream

Date: 2026-08-20 07:26 UTC

## 0. Tree state

| item | value |
|---|---|
| `main` SHA | `a58e05d09c41401f99f55ebcb26de9114bad3663` |
| `main` commit date | `Thu Aug 20 09:16:13 2026 +0200` (today — fresh) |
| upstream remote | `https://github.com/e107inc/e107` (added this session) |
| `upstream/master` SHA | `372947bce57a5fb66c0c4918389b8639518c5b72` (Tue Aug 18 23:34:38 2026 +0200) |

### Branch chosen: `lite-ecore-realign`

After `git fetch origin --prune`, the branch named in the harness preamble
(`lite-ecore-realign-wr68oa`) no longer exists on origin — it was a placeholder
sitting at `main`'s tip with no commits of its own. `origin/lite-ecore-realign`
does exist, is 3 commits ahead of / 1 behind `main`, and is **not** merged into
`main`:

```
54a3bbd98 lite: realign news shortcode batches with upstream
1c84dfe8f lite: realign ecore templates with upstream
7872387b9 lite: realign ecore shortcode batches with upstream
```

Per the task rule ("continue on `lite-ecore-realign` if it still exists on
origin and is not merged into `main`"), work continues on `lite-ecore-realign`.

---

## 1. Markers recorded before overwrite

`grep -n "LITE MODIFICATION\|LITE FEATURE"` on the pre-overwrite file (2495 lines)
returned exactly two markers. A case-insensitive grep for `lite` found no other
marker forms.

```
681:	/* LITE MODIFICATION for backend admin_template.php */
1906:		// LITE MODIFICATION: admin template override allowed.
```

### Marker 1 — line 681 — `sc_admin_logo()` (lines 681–699)

Marker line:

```
681|	/* LITE MODIFICATION for backend admin_template.php */
```

Full block it covers:

```php
 681| 	/* LITE MODIFICATION for backend admin_template.php */
 682| 	public function sc_admin_logo($parm=null)
 683| 	{
 684| 		//	this is hardcoded admin navbar logo for e107 2
 685| 		$default = '<img class="admin-logo" src="'.e_THEME_ABS.'bootstrap3/images/logo.webp" alt="e107"  />';
 686| 
 687| 		//check if custom core plugin is installed
 688| 		if (e107::isInstalled('SP_Core'))
 689| 		{  
 690| 			$admin_logo = e107::getPlugConfig('SP_Core')->getPref('admin_logo');
 691| 			if($admin_logo) {
 692| 				$admin_logo = e107::getParser()->replaceConstants($admin_logo, 'full') ;
 693| 				$image = '<img class="admin-logo" src="' . $admin_logo .  '" alt="e107"  />';
 694| 				return $image;
 695| 			}
 696| 			
 697| 		}
 698| 		return $default; 
 699| 	}
```

Upstream's `sc_admin_logo()` (upstream lines 682–719) probes `THEME.images/e_adminlogo.png`
then `e_IMAGE.adminlogo.png`, calls `getimagesize()` for inline dimensions, and
optionally wraps the `<img>` in a link. Lite replaces the whole body with a
hardcoded bootstrap3 webp logo plus an `SP_Core` plugin-pref override.

### Marker 2 — line 1906 — `getCoreTemplate('admin','nav')` override flag (lines 1906–1912)

Marker line:

```
1906|		// LITE MODIFICATION: admin template override allowed.
```

Full block it covers:

```php
1906| 		// LITE MODIFICATION: admin template override allowed.
1907| 		// $override=true (vs upstream's false) lets a custom admin theme
1908| 		// override the admin template. Lite uses its own `backend` admin
1909| 		// theme. See upstream issue #5722 — revert if upstream fixes the
1910| 		// override default for admin templates, or if Lite stops shipping
1911| 		// its own admin theme.
1912| 		$template = e107::getCoreTemplate('admin', 'nav', true);
```

Upstream (line 1927) is the same statement with the third argument `false`.

### Note on "log handling"

The task said "at least one [marker] is around the log handling". There is no
log-handling divergence in this file. The nearest match is `sc_admin_logo()` —
marker 1, immediately after `sc_admin_logged()` — which I read as the intended
reference. The pre-overwrite diff against upstream (below) was 86 lines total
and contained no other divergence anywhere in the file, so nothing log-related
was missed.

### Third, unmarked divergence — reverted

The pre-overwrite file also differed from upstream at line 227:

```php
-		if(e_PAGE === 'menus.php') // quite fix to disable e107_admin/menus.php help file in all languages.   <- upstream
+		if(e_PAGE === 'menus.php') // quite fix to disable eadmin/menus.php help file in all languages.       <- Lite
```

Unmarked, and a comment-only directory rename with no executable effect. Per the
task rule ("everything unmarked returns to upstream form", and step 4 "leave
comments, docblocks and user-facing prose in upstream wording") it was **not**
carried forward — the line is back to upstream's `e107_admin/menus.php` wording.

---

## 2. Overwrite

```
git show upstream/master:e107_core/shortcodes/batch/admin_shortcodes.php > ecore/shortcodes/batch/admin_shortcodes.php
```

`git rev-parse upstream/master` = `372947bce57a5fb66c0c4918389b8639518c5b72`
(2510 lines, 0 markers after overwrite).

## 3. Re-application

Both marked blocks had a clean, unmodified equivalent place in the new upstream
file — upstream has not rewritten either area, so nothing had to be improvised
and no Lite logic was merged into upstream logic by judgement.

- Marker 1: upstream's `sc_admin_logo()` body (upstream lines 682–719) replaced
  wholesale by the recorded Lite block, marker included. Upstream's blank-line
  separators before and after the function were left in place (the old Lite file
  had eaten both; that whitespace churn was unmarked and did not return).
- Marker 2: upstream line 1927 replaced by the recorded 7-line Lite block.

Marker count after re-application: 2 — matches the 2 recorded in step 1.

## 4. Directory literals

`grep -n "e107_[a-z]*/"` on the realigned file — one hit:

| line | literal | context | classification | action |
|---|---|---|---|---|
| 227 | `e107_admin/menus.php` | trailing `//` comment on the `e_PAGE === 'menus.php'` guard | **comment / prose** — not required, built, compared or emitted | left in upstream wording |

A broader `grep -n "e107_"` returned only non-directory matches, none needing a
remap: the `e107_INIT` guard constant, the `e107_adminLeftPanel` cookie name,
local variables `$e107_var` / `$e107_plug`, the `e107_debug` class, and the
filenames `e107_update.php` and `e107_config.php` (explicitly excluded by the
task). `$e107_paths` does not appear in this file.

**Functional directory remaps applied: 0.** All admin/plugin/theme paths in this
file are already built from the `e_ADMIN`, `e_ADMIN_ABS`, `e_PLUGIN`,
`e_PLUGIN_DIR`, `e_THEME_ABS`, `e_IMAGE` constants, which Lite resolves through
its own `e107_config.php` — no hardcoded directory names to translate.

---

## 5. Verification

### Complete diff vs upstream

```diff
681a682
> 	/* LITE MODIFICATION for backend admin_template.php */
684c685,686
< 		//	parse_str($parm);
---
> 		//	this is hardcoded admin navbar logo for e107 2
> 		$default = '<img class="admin-logo" src="'.e_THEME_ABS.'bootstrap3/images/logo.webp" alt="e107"  />';
686,715c688,695
< 
< 		if (isset($file) && $file && is_readable($file))
< 		{
< 			$logo = $file;
< 			$path = $file;
< 		}
< 		else if (is_readable(THEME.'images/e_adminlogo.png'))
< 		{
< 			$logo = THEME_ABS.'images/e_adminlogo.png';
< 			$path = THEME.'images/e_adminlogo.png';
< 		}
< 		else
< 		{
< 			$logo = e_IMAGE_ABS.'adminlogo.png';
< 			$path = e_IMAGE.'adminlogo.png';
< 		}
< 
< 		$dimensions = getimagesize($path);
< 
< 		$image = "<img class='logo admin_logo' src='".$logo."' style='width: ".$dimensions[0]. 'px; height: ' .$dimensions[1]."px' alt='".ADLAN_153."' />\n";
< 
< 		if (isset($link) && $link)
< 		{
< 			if ($link === 'index')
< 			{
< 				$image = "<a href='".e_ADMIN_ABS."index.php'>".$image.'</a>';
< 			}
< 			else
< 			{
< 				$image = "<a href='".$link."'>".$image.'</a>';
---
> 		//check if custom core plugin is installed
> 		if (e107::isInstalled('SP_Core'))
> 		{  
> 			$admin_logo = e107::getPlugConfig('SP_Core')->getPref('admin_logo');
> 			if($admin_logo) {
> 				$admin_logo = e107::getParser()->replaceConstants($admin_logo, 'full') ;
> 				$image = '<img class="admin-logo" src="' . $admin_logo .  '" alt="e107"  />';
> 				return $image;
716a697
> 			
718c699
< 		return $image;
---
> 		return $default; 
1927c1908,1914
< 		$template = e107::getCoreTemplate('admin', 'nav', false);
---
> 		// LITE MODIFICATION: admin template override allowed.
> 		// $override=true (vs upstream's false) lets a custom admin theme
> 		// override the admin template. Lite uses its own `backend` admin
> 		// theme. See upstream issue #5722 — revert if upstream fixes the
> 		// override default for admin templates, or if Lite stops shipping
> 		// its own admin theme.
> 		$template = e107::getCoreTemplate('admin', 'nav', true);
```

63 diff lines, all accounted for: the marker 1 block + its marker, and the
marker 2 block + its marker. No third hunk — nothing unmarked survived the
overwrite.

### Checks

| check | result |
|---|---|
| `php -l` | `No syntax errors detected` |
| markers in file, before → after | 2 → 2 (unchanged) |
| tree-wide marker count, before → after | 89 → 89 (unchanged) |
| `git diff --name-only` | `ecore/shortcodes/batch/admin_shortcodes.php` only |
| `git status` | clean apart from that one file (+ these report files) |
| file length | 2510 upstream → 2497 realigned |

### PHP-8-only syntax scan

Nothing found — nothing was downgraded, because there was nothing to downgrade.

| construct | hits |
|---|---|
| union return types (`): A\|B`) | none |
| `mixed` return type | none |
| `never` return type | none |
| `match(` | none |
| constructor property promotion | none |
| `enum` | none |
| `readonly` | none |
| nullsafe `?->` | none |
| `str_contains` | none |
| `str_starts_with` | none |
| `str_ends_with` | none |
| `array_is_list` | none |
| attributes `#[...]` | none |
| return type declarations of any kind | none |

The re-applied Lite blocks are themselves PHP 5/7-compatible (`e107::isInstalled`,
`getPlugConfig`, `replaceConstants`, plain `->`).

---

## 6. Commit

```
lite: realign ecore admin_shortcodes with upstream, keep marked divergences

Source: e107inc/e107@372947bce57a5fb66c0c4918389b8639518c5b72
File taken from upstream; only marked Lite modifications re-applied.
```

Pushed to `lite-ecore-realign`. No merge, no PR, nothing pushed to `main`.
