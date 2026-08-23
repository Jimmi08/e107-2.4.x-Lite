<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc.
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL configuration - 'usersettings' module, legacy (GET) format
 *
 * Standalone override module: there is no counterpart under ecore/url/.
 * The key exists only here, so nothing in the upstream tree has to change
 * for the frontend admin-edit route to exist.
 *
 * Routes
 *   usersettings/edit/self          -> usersettings.php
 *   usersettings/edit/user  id=N    -> usersettings.php?id=N
 *
 * The id is always emitted as a NAMED parameter (?id=N), never as the bare
 * positional shape (?N). usersettings.php resolves a named id consistently
 * in both its save gate and its render gate; the positional shape is only
 * kept alive on the reading side for old links.
 */

if(!defined('e107_INIT')) { exit; }


class override_usersettings_url extends eUrlConfig
{

	public function config()
	{
		return array(

			'config' => array(
				'noSingleEntry' => true,                        // legacy script, not reachable through index.php
				'legacy'        => '{e_BASE}usersettings.php',  // entry point
				'format'        => 'get',                       // rules are ignored in this format
				'selfParse'     => true,                        // parsing is done by the entry script
				'selfCreate'    => true,                        // URLs are built by create() below
				'defaultRoute'  => 'edit/self',
				'errorRoute'    => '',
				'urlSuffix'     => '',
				'mapVars'       => array(),                     // deliberately empty - see note in create()
				'allowVars'     => array(),
			),

			'rules' => array() // no rules in 'get' format
		);
	}


	/**
	 * Build a URL for a usersettings route.
	 *
	 * Note on mapVars: it is left empty on purpose. eRouter::assemble() emits an
	 * E_USER_NOTICE for every mapVars key that is absent from $params, so a
	 * declared-but-unused mapping produces notices on every call.
	 *
	 * @param array|string $route  controller/action pair
	 * @param array        $params
	 * @param array        $options
	 * @return string
	 */
	public function create($route, $params = array(), $options = array())
	{
		if(is_string($route))
		{
			$route = explode('/', $route, 2);
		}

		if(!varset($route[1]))
		{
			$route[1] = 'self';
		}

		if($route[0] === 'edit' && $route[1] === 'user')
		{
			$id = isset($params['id']) ? intval($params['id']) : 0;

			if($id > 0)
			{
				return 'usersettings.php?id=' . $id;
			}
		}

		// edit/self, and any unrecognised route, resolve to the user's own settings
		return 'usersettings.php';
	}


	/**
	 * Parsing is handled by usersettings.php itself in this format.
	 */
	public function parse($pathInfo, $params = array(), $request = null, $router = null, $config = array())
	{
		return false;
	}


	/**
	 * Label, description and examples for the URL configuration page.
	 *
	 * The display keys MUST sit inside 'labels'. A flat array here is silently
	 * ignored and the admin page falls back to its own generic wording.
	 */
	public function admin()
	{
		return array(
			'labels' => array(
				'name'        => defined('LAN_EURL_CORE_USERSETTINGS') ? LAN_EURL_CORE_USERSETTINGS : 'User settings',
				'label'       => 'Default - usersettings.php',
				'description' => 'Legacy URLs. Own settings, plus frontend admin edit of another account via ?id=N.',
				'examples'    => array(
					'{SITEURL}usersettings.php',
					'{SITEURL}usersettings.php?id=5',
				),
			),
			'form'      => array(),
			'callbacks' => array(),
		);
	}

}
