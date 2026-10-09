# Admin logout refused (LAN_LOGOUT_REFUSED_TOKEN_MISSING) — diagnosis

- Repo / branch: `Jimmi08/e107-2.4.x-Lite` / `sync-upstream-2026-09-26`
- HEAD analysed: `bb196796df5fe7f80807d8713a422fb6bf48daa4` (commit date Sun Sep 27 11:47:35 2026 +0000)
- Upstream compared: `e107inc/e107` master `3cd96ea259bd90b64e775bf6918c1174efbca941` (Fri Sep 25 2026)
- Scope: diagnosis only, no code changed.

## TL;DR

1. **The code on this branch cannot refuse `admin.php?logout&e-token=<hex>` on a PHP whose `$_GET` is built normally.**
   I reproduced this end-to-end on a fresh install of this HEAD (MariaDB + PHP 8.4 built-in server + headless Chromium,
   admin theme `backend`, clicking the real `{ADMIN_NAVIGATION=enav_logout}` item): `302 -> /eadmin/admin.php`,
   the session was destroyed, and the admin login form was shown. There was no refusal message.
2. **The working assumption is wrong.** The message does not come from a second request or from a message stored in the session.
   It comes from the tokened request itself **when PHP's `$_GET` does not contain `e-token` but `$_SERVER['QUERY_STRING']` does**.
   I reproduced the exact symptom this way: the address bar keeps the real `&` URL, the status is 200, the message shows and the admin stays logged in.
3. Root cause in code: `logout_requested()` reads **`$_SERVER['QUERY_STRING']`**, while `logout_refused()` reads **`$_GET['e-token']`**
   (`class2.php:1956-1970`). When the two sources disagree, the result is always a refusal.
   On a correctly configured PHP they never disagree, so something on the **affected server** differs.
   The confirmed triggers are `arg_separator.input` without `&`, or `variables_order` without `G`.
   Other triggers are plausible but untested (a proxy/WAF re-encoding `&` as `%26`, or a filter stripping the parameter).
   **This is unverified for the user's server.** It needs the three checks in the section "What to check on the affected server".
4. Separate real Lite bug: the `login_menu` plugin still emits **tokenless** logout links (`index.php?logout`).
   Upstream already fixed this. Those links are always refused, which may be part of "every logout link behaves the same way".

## 1. Code path (read in full)

### 1.1 `class2.php:1956-1970`

```php
function logout_requested()
{
	$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
	return ($query === 'logout' || strpos($query, 'logout&') === 0);
}
function logout_refused()
{
	return (logout_requested() && defined('e_TOKEN') && empty($_GET['e-token']));
}
```

- They use two different sources: the raw or normalised query string, and PHP's parsed `$_GET`.
- `logout_refused()` checks only that a token is **present**. The token **value** is validated elsewhere (1.3).

### 1.2 What `$_SERVER['QUERY_STRING']` looks like by the time of the check

- `e107::_init()` calls `prepare_request()` (`ehandlers/e107_class.php:634`) and then `set_request()` (`:665`).
- `prepare_request()` (`e107_class.php:5337-5441`) runs `array_walk($_GET, filter_request)`.
  The callback takes its input **by value** (`filter_request($input,...)`, `:5454`), so `$_GET` is not modified.
  It only calls `die_http_400()` on suspicious input. It also strips `ajax_used=1` from QUERY_STRING (`:5401`).
- `set_request()` (`e107_class.php:6453-6516`) runs outside single-entry mode (admin pages).
  It sets `$_SERVER['QUERY_STRING'] = htmlspecialchars(post_toForm(rawurldecode(...)))` (`:6511`).
  `logout&e-token=X` therefore becomes `logout&amp;e-token=X`. That still starts with `logout&`, so `logout_requested()` is true.
  Note that `rawurldecode` also turns `logout%26e-token=X` into `logout&amp;...`. **On admin pages, a percent-encoded `&` is still detected as a logout request.**
- The front end (`index.php` defines `e_SINGLE_ENTRY`, `index.php`) keeps the raw query string.
- I verified this with a harness that replicates `set_request()` and both helpers verbatim.
  For `logout&e-token=<32hex>` it gives `logout_requested()=true` and `logout_refused()=false`.
- No other code before `class2.php:860` changes `$_GET` or QUERY_STRING for `eadmin/admin.php`:
  - `ehandlers/application.php:2088/4022/4029` rewrites `$_GET`/QUERY_STRING only in the front-end router.
  - `e107_class.php:6041` calls `parse_str(..., $_GET)` only when `e_MENU` is set (`?[menu]...`).
  - No `e_module.php` is registered on a clean install (`e_module_list` is empty). On the user's site this is **unverified**.
  - The repo `.htaccess` (`e107.htaccess:52-89`) only rewrites non-existing files to `index.php`, so it does not affect `eadmin/admin.php`.

### 1.3 Token value validation happens earlier, in the session handler

- `class2.php:597`: `e107::getSession()->challenge()->check(false)`. When it returns false, the response is `403 Unauthorized access!` and the script exits (`:599-617`).
- `e_session::check()` → `attest()` (`ehandlers/session_handler.php:1570-1760`).
  `hasSubmittedToken()` (`:1638`) and `submittedTokenIsValid()` (`:1758`) read **`$_GET['e-token']`**.
  A present-but-invalid token is refused, including on GET requests (`:1697-1700`).
- I confirmed this in Chromium: `admin.php?logout&e-token=000…0` → **403**.
- Consequence: `logout_refused()` does not need to validate the value itself, but only because `check()` has already done it **from `$_GET`**.
  If `$_GET` lacks the token, `check()` sees *no* token. A GET is not state-changing, so it passes (`:1719`), and nothing validates the value.

### 1.4 The logout branch, `class2.php:858-900` (wrapped in `if (e107::getUser()->isUser())`)

1. Optional `user_audit(USER_AUDIT_LOGOUT)` and an update of the `online` row.
2. `e107::getEvent()->trigger('logout')`. There is no `logout` handler in core or bundled plugins (grep of `eplugins`/`ecore`: only `login_menu` templates).
3. `$prev = e107::getRedirect()->getPreviousUrl()` (`ehandlers/redirection_class.php:193`). The value comes from session key `_previousUrl` or cookie `<e_COOKIE>__previousUrl`.
4. `e107::getUser()->logout()` (`ehandlers/user_model.php:2235-2250`): `logoutAs()`, `_destroySession()`, `parent::destroy()`, `e107::getSession()->destroy()`. The last call deletes the cookies and calls `session_destroy()` (`session_handler.php:1436-1446`).
5. `cookie(e_COOKIE, '', past)`.
6. `redirect($prev ?: SITEURL)`, then `go()` (`redirection_class.php:1035-1140`). `go()` normalises `&amp;` to `&`, refuses off-site targets and sends `Location` with `exit`.

Can the redirect target be a tokenless logout URL? No:
- `setPreviousUrl()` is called only from `init_session()` for ADMIN users (`class2.php:1682-1685`).
- It is guarded by `isCapturable()` → `queryIsExcepted()` (`redirection_class.php:443-455, 524`). `query_exceptions = ['logout']` (`:126`) excludes every `logout` / `logout&...` query.
- I observed `Location: /eadmin/admin.php` in the reproduction.

### 1.5 Messages

- `addError($msg)` defaults to `$session=false` (`ehandlers/message_handler.php:343`). The refusal message is **not** persisted to the session.
  It can only be rendered by the same request that called `logout_refused()`.
  This rules out the idea that a "message stored by an earlier attempt" survives into a later page.

### 1.6 Other redirects before the block (none strips the token)

- SSL force (`class2.php:439-449`) and the `www.`/`redirectsiteurl` host redirects (`:481-517`) redirect to `e_REQUEST_URL`, built from `REQUEST_URI` (`e107_class.php:6010-6047`), or to `host()`, which is skipped for `ADMINDIR`.
- `go()` converts `&amp;` to `&` in all cases.
- `force_userupdate` is skipped for logout (`class2.php:814`).
- The maintenance and members-only redirects exempt admins and the `logout` query.

## 2. Reproduction matrix (fresh install of `bb19679`, admin theme `backend`, headless Chromium)

| # | PHP / request | Final URL | HTTP | Refusal msg | Still logged in |
|---|---|---|---|---|---|
| 1 | default php.ini, click nav logout (`?logout&e-token=<32hex>`) | `/eadmin/admin.php` (after 302) | 200 | no | **no** (login form) |
| 2 | default, `?logout&amp;e-token=…` (literal entity) | same URL | 200 | **yes** | yes |
| 3 | default, `?logout` (no token) | same URL | 200 | **yes** | yes |
| 4 | default, `?logout&e-token=000…0` (forged) | same URL | **403** | no | yes |
| 5 | `-d arg_separator.input=";"`, click nav logout (real `&`) | same URL | 200 | **yes** | **yes**. This is the exact user symptom |
| 6 | `-d variables_order=EPCS` (no G), click nav logout | same URL | 200 | **yes** | **yes**. This is the exact user symptom |
| 7 | `arg_separator.input=";"`, forged `e-token=000…0` | same URL | 200 | yes | yes. The forged token is *not* 403, because `check()` cannot see it |

Row 1 contradicts the working assumption: on a normal PHP the tokened request logs the user out.
Rows 5 and 6 match the report exactly: real `&` in the address bar, message shown, still logged in.
Row 7 matters for the security analysis of the fixes below.

In rows 5 and 6 the final URL **remains the logout URL** and there is no 302. So on the affected site, the browser's Network tab should show the logout request answered with **200**, not 302. This is the quickest way to confirm the diagnosis (see the next section).

## 3. What to check on the affected server (unverified until done)

1. DevTools → Network, click logout. Does `admin.php?logout&e-token=…` return **200** (same request refused; this diagnosis applies) or **302** (then a later request is refused; report the `Location`)?
2. `eadmin/phpinfo.php`, or `php -i` from the web SAPI: check `arg_separator.input` (must contain `&`) and `variables_order`/`request_order` (must contain `G`).
3. If both look normal, temporarily add one line in `class2.php` just before line 860:
   `error_log(var_export([$_SERVER['QUERY_STRING'], $_GET], true));`. Then compare the two after the click.
   A proxy/WAF/CDN that rewrites or percent-encodes the query (`%26`) or drops the `e-token` parameter would show up here. **This case is untested (hypothesis).**

Note: settings #5 and #6 would also break most multi-parameter admin URLs (`?mode=…&action=…`).
If the rest of the admin works normally on that server, a proxy/WAF that touches only `e-token` (or percent-encodes `&`) is the more likely variant. **This is an assumption; it needs check 3.**

## 4. Upstream comparison

- The core code on this path is identical to upstream master `3cd96ea25`:
  - `class2.php` differs only in the directory-name default.
  - `redirection_class.php` and `session_handler.php` are identical.
  - `e107_class.php` differs only in the directory names and the Lite "no phone-home" change.
- The `QUERY_STRING` vs `$_GET` split in `logout_requested()`/`logout_refused()` is therefore an **upstream** weakness (a latent upstream bug). It is not Lite-specific.
- **Lite-specific:** `eplugins/login_menu/login_menu.php:52` and `eplugins/login_menu/login_menu_shortcodes.php:319,324` still use `index.php?logout` without a token.
  Upstream has `index.php?logout&amp;e-token=…` in the same places. With the new upstream refusal (synced into `class2.php` by `8e5a09a`/`53eee3e`), **every login_menu logout link is always refused**.
  This fully explains the "every logout link" part for the front-end login menu, independently of the server issue.
- Sites upgraded from older versions may also still have a `links` table row `index.php?logout` without `{E_TOKEN}`.
  A fresh install uses `index.php?logout&e-token={E_TOKEN}` (`eadmin/links.php:173`, `ecore/xml/default_install.xml:528`).
  **Not checked on the user's database.**

## 5. Fix options (none applied)

### Option A — take token presence and value from one consistent source, and validate the value in `logout_refused()` — *upstream-candidate*

- Change: `logout_refused()` reads the token from the same source as `logout_requested()`.
  That means parsing `$_SERVER['QUERY_STRING']` with `&amp;` normalised to `&`, falling back to `$_GET['e-token']`.
  It then **explicitly validates** the value with `e107::getSession()->checkFormToken($token)`.
  It refuses when the token is missing **or** invalid, and it could show a distinct message for an invalid token.
- Pros: logout works on servers where `$_GET` and QUERY_STRING disagree. The CSRF check no longer depends on `e_session::check()` having seen the same token (row 7 shows it currently may not). It is small, local and easy to upstream.
- Cons: it touches upstream-owned `class2.php`, which means a Lite divergence until upstream accepts it. It duplicates part of `attest()`'s logic. It needs a unit test for the `logout&amp;e-token` and `;`-separator cases.
- Security: **stronger than today.** A token is still required and is now validated in both code paths.
  **Do not** implement only the "read token from QUERY_STRING" half. On an affected server (row 7), a forged `e-token=anything` would then log the user out, turning a broken logout into a CSRF logout.

### Option B — no core change: fix the server and the Lite links — *LITE MODIFICATION (links) + ops*

- Change:
  - Correct the server (`arg_separator.input="&"`, `variables_order` with `G`, or the proxy/WAF rule found by check 3).
  - Sync `login_menu` logout links with upstream (`&amp;e-token='.defset('e_TOKEN')`).
    Since upstream already has this, it is an upstream sync, not a new divergence.
  - Optionally fix any legacy `links` row to `index.php?logout&e-token={E_TOKEN}`.
- Pros: no divergence in core. It fixes the actual environment and the Lite-only tokenless links.
- Cons: the latent core weakness stays. Any future host with the same misconfiguration hits it again, and row 7 (the token value not validated when `$_GET` lacks it) remains.
- Security: unchanged from today. The token stays required and validated wherever `$_GET` works.

### Option C — make `logout_requested()` also use `$_GET` (e.g. `array_key_exists('logout', $_GET)` plus the existing prefix check) — *upstream-candidate*

- Change: both helpers read `$_GET`, so they cannot disagree.
- Pros: tiny change. It never shows a misleading "no token" message when PHP simply failed to parse the query.
- Cons: on an affected server the logout link then does **nothing** (it is silently ignored and the user stays logged in without explanation). That is worse UX than today, it does not fix the user's problem, and it changes semantics for query strings like `?x&logout`.
- Security: neutral. Logout still happens only when `check()` has validated a `$_GET` token.

**Recommendation:** B now, because it is a pure ops/sync change with no security change. Then open A as an upstream issue/PR, including a test for row 7. Avoid a half-implemented A.

## 6. Evidence limits / assumptions

- **Verified by reading and running:** everything in sections 1–2, on a clean local install of `bb19679` with PHP 8.4.19 (built-in server), MariaDB and Chromium 1194.
- **Not verified:** the user's server configuration, web server (Apache or nginx, FPM), proxy/WAF/CDN, enabled `e_module` plugins, admin `links` rows, and whether the deployed files equal `bb19679`.
  The claim that the user's site hits case 5, 6 or a proxy variant is an **inference** from the matching symptom, pending section 3.
- Reproduction scripts were kept outside the repository (session scratchpad). Nothing in the repo was modified except this report.
