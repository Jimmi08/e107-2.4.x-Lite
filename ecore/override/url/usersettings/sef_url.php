<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc.
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL configuration - 'usersettings' module, SEF (path) format
 *
 * Standalone override module: there is no counterpart under ecore/url/.
 *
 * Routes
 *   /usersettings/          -> edit/self          (own settings)
 *   /usersettings/update    -> edit/self          (forced profile update)
 *   /usersettings/5         -> edit/user  id=5    (admin editing another account)
 *
 * Why 'id={id}' and not '{id}':
 *   eRouter installs legacyQuery as e_QUERY. A template containing '=' is
 *   additionally parse_str'd into $_GET (ehandlers/application.php, the
 *   setLegacyQstring path), so 'id={id}' populates $_GET['id'].
 *   usersettings.php resolves a named id identically in its save gate and its
 *   render gate; the bare positional shape (?5) is honoured by the save gate
 *   only. Emitting the named shape keeps the two halves in agreement.
 *
 * Why every rule declares legacyQuery:
 *   a rule whose legacyQuery resolves to null skips the block that defines
 *   e_QUERY. An explicit value - including the empty string for edit/self -
 *   keeps e_QUERY deterministic. Do NOT move legacyQuery to config level:
 *   a profile-wide template would then apply to rules that must not carry one.
 *
 * Why mapVars and matchValue are absent:
 *   mapVars emits an E_USER_NOTICE for every declared key missing from
 *   $params, and callers here pass 'id' directly. matchValue is omitted
 *   because 'edit/user' has exactly one rule - there is nothing to fall
 *   through to - so callers must not pass an empty id.
 */

if(!defined('e107_INIT')) { exit; }


class override_usersettings_sef_url extends eUrlConfig
{

	public function config()
	{
		return array(

			'config' => array(
				'legacy'       => '{e_BASE}usersettings.php', // entry point (no controller)
				'format'       => 'path',                    // use the rule set below
				'defaultRoute' => 'edit/self',
				'urlSuffix'    => '',
				'allowVars'    => false,                     // only route vars survive - whitelist by default
			),

			'rules' => array(

				// simple matches first - PERFORMANCE
				''       => array('edit/self', 'legacyQuery' => ''),
				'update' => array('edit/self', 'legacyQuery' => 'update'),

				// numeric id last - admin editing another account
				'<id:[\d]+>' => array('edit/user', 'legacyQuery' => 'id={id}'),
			)
		);
	}


	/**
	 * Label, description and examples for the URL configuration page.
	 *
	 * The display keys MUST sit inside 'labels'. A flat array here is silently
	 * ignored and the admin page falls back to its own generic wording.
	 *
	 * No 'generate' block: that wiring drives SEF slug generation from a
	 * database column, and this module routes on a numeric user id only.
	 */
	public function admin()
	{
		return array(
			'labels' => array(
				'name'        => defined('LAN_EURL_CORE_USERSETTINGS') ? LAN_EURL_CORE_USERSETTINGS : 'User settings',
				'label'       => 'Friendly - /usersettings/',
				'description' => 'SEF URLs. Own settings at /usersettings/, frontend admin edit of another account at /usersettings/{id}.',
				'examples'    => array(
					'{SITEURL}usersettings/',
					'{SITEURL}usersettings/5',
				),
			),
			'form'      => array(),
			'callbacks' => array(),
		);
	}

}
