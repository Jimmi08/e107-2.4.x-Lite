 <?php

	/**
	 * @file
	 * LITE FEATURE: Flexpanel dashboard adminstyle for Lite admin.
	 * This file does not exist in upstream e107. Lite ships its own
	 * `dashboard` adminstyle (selected via $pref['adminstyle']) using
	 * a flexible drag-and-drop panel layout. Sync passes MUST preserve
	 * this file (Lite-only — no upstream equivalent).
	 */

	if (!defined('e107_INIT')) {
		exit;
	}

	// Get "Apply dashboard preferences to all administrators" setting.
	$adminPref = e107::getConfig()->get('adminpref', 0);
	$flepanelEnabled = true;

	// If not Main Admin and "Apply dashboard preferences to all administrators" is checked.
	if (!getperms('1') && $adminPref == 1) {
		$flepanelEnabled = false;
	}

	define('FLEXPANEL_ENABLED', $flepanelEnabled);
	//LITE-SKIP Lite removes the e107.org admin RSS feed entirely (no phone-home).
	//LITE-SKIP ADMINFEEDMORE dropped. See Lite #88.
	//LITE-SKIP define('ADMINFEEDMORE', 'https://e107.org/blog');

	// Dashboard uses infopanel's methods to avoid code duplication.
	// not used directly flexpanel - intention 
	// TODO: remove this DONE
	// e107_require_once(e_ADMIN . 'includes/infopanel.php');

	/* Notes
		core-flexpanel-layout is not used because layout is replaced with dashboard template 
	*/

	/**
	 * Class adminstyle_flexpanel.
	 */
	/* TODO remove extends infopanel - outdated code  DONE */
	class adminstyle_dashboard
	{

		public $positions	= array();
		protected $areas = array();
		protected $renderedPanels = array();
		static $userAdminPanelArray = array();
		static $fullAdminPanelArray = array();
		//static $fullPluginPanelArray = array(); not needed, personalization is not used for plugins
		static $fullAdminIcons = array();
		static $fullPluginIcons = array();

		/* from infopanel */
		static $adminPref = 0;
		static $user_pref = array();

		/**
		 * Constructor.
		 */
		public function __construct()
		{
			//	parent::__construct();

			$this->positions = (array) e107::getTemplate(false, 'dashboard', 'positions');
			$this->areas = array_keys($this->positions);

			self::$adminPref = e107::getConfig()->get('adminpref', 0);

			/* personalize icon list */

			if (self::$adminPref == 1) {
				self::$user_pref = e107::getPref();
			}
			// Get $user_pref.
			else {
				self::$user_pref = e107::getUser()->getPref();
			}

			$myE107 = varset(self::$user_pref['core-infopanel-mye107'], array());
			if (empty($myE107)) // Set default icons.
			{
				self::$user_pref['core-infopanel-mye107'] = e107::getNav()->getDefaultAdminPanelArray();
			}
			self::$userAdminPanelArray = self::$user_pref['core-infopanel-mye107'];


			/* full icon list */
			self::$fullAdminIcons =  e107::getNav()->adminLinks('core');
			self::$fullPluginIcons =  e107::getNav()->adminLinks('plugin');

			foreach (self::$fullAdminIcons as $key => $item) {
				$tmp[] = $key;
			}
			self::$fullAdminPanelArray = $tmp;


			/* flex is enabled only if each admin can have its own admin dashboard adminpref = false */
			if ($this->canEditLayout()) {
				e107::css('inline', '.draggable-panels .panel-heading { cursor: move; }');
				e107::js('core', 'core/admin.flexpanel.js', 'jquery', 4);

				if (varset($_GET['mode']) == 'customize') {
					e107::css('inline', '.layout-container { display: table; margin-left: auto; margin-right: auto; }');
					e107::css('inline', '.layout-container label.radio { float: left; padding: 0; width: 120px; margin: 7px; cursor: pointer; text-align: center; }');
					e107::css('inline', '.layout-container label.radio img { margin-left: auto; margin-right: auto; display: block; }');
					e107::css('inline', '.layout-container label.radio input { width: 100%; margin-left: auto; margin-right: auto; display: block; }');
					e107::css('inline', '.layout-container label.radio p { width: 100%; text-align: center; display: block; margin: 20px 0 0 0; }');
				}

				// Save posted Layout type.
				if (varset($_POST['e-flexpanel-layout']) && is_string($_POST['e-flexpanel-layout']) && array_key_exists($_POST['e-flexpanel-layout'], $this->getDefaultPositions())) {

					// If Layout has been changed, we clear previous arrangement in order to use defaults.
					if (varset(self::$user_pref['core-flexpanel-layout']) != $_POST['e-flexpanel-layout']) {
						$this->savePref('core-flexpanel-order', array());
					}

					$this->savePref('core-flexpanel-layout', $_POST['e-flexpanel-layout']);
				}
			}
		}

		/**
		 * Save preferences.
		 *
		 * @param $key
		 * @param $value
		 */
		public function savePref($key, $value)
		{
			// Get "Apply dashboard preferences to all administrators" setting.
			$adminPref = e107::getConfig()->get('adminpref', 0);

			// If "Apply dashboard preferences to all administrators" is checked.
			// Save as $pref.
			if ($adminPref == 1) {
				e107::getConfig()
					->setPosted($key, $value)
					->save();
			}
			// Save as $user_pref.
			else {
				e107::getUser()
					->getConfig()
					->set($key, $value)
					->save();
			}
		}

		/**
		 * @return bool
		 */
		public function canEditLayout()
		{
			if (!deftrue('ADMIN') || !FLEXPANEL_ENABLED) {
				return false;
			}

			return (self::$adminPref == 1) ? getperms('1') : true;
		}

		/**
		 * Merge the posted panel order into the stored one and save it.
		 *
		 * @param array $posted
		 * @return void
		 */
		public function saveOrder($posted)
		{
			if (!is_array($posted) || !$this->canEditLayout()) {
				return;
			}

			if (self::$adminPref == 1) {
				$stored = e107::getConfig()->get('core-flexpanel-order', array());
			} else {
				$stored = e107::getUser()->getConfig()->get('core-flexpanel-order', array());
			}

			if (!is_array($stored)) {
				$stored = array();
			}

			$changed = false;

			foreach ($posted as $id => $pos) {
				$id = (string) $id;

				if (!isset($this->renderedPanels[$id]) || !is_array($pos)) {
					continue;
				}

				$area = varset($pos['area'], '');

				if (!is_string($area) || !in_array($area, $this->areas, true)) {
					continue;
				}

				$stored[$id] = array(
					'area'   => $area,
					'weight' => (int) varset($pos['weight'], 0),
				);
				$changed = true;
			}

			if (!$changed) {
				return;
			}

			if (self::$adminPref == 1) {
				e107::getConfig()
					->setPosted('core-flexpanel-order', $stored)
					->save();
			} else {
				e107::getUser()
					->getConfig()
					->set('core-flexpanel-order', $stored)
					->save();
			}
		}

		/**
		 * @param string $id
		 * @param string $html
		 * @return void
		 */
		protected function addToPosition($id, $html)
		{
			$this->renderedPanels[$id] = true;

			$info = $this->getMenuPosition($id);

			if (!isset($this->positions[$info['area']][$info['weight']])) {
				$this->positions[$info['area']][$info['weight']] = '';
			}
			$this->positions[$info['area']][$info['weight']] .= $html;
		}


		/**
		 * Get selected area and position for a menu item. see flexpanel class
		 *
		 * @param $id
		 *  Menu ID.
		 * @return array
		 *  Contains menu area and weight.
		 */
		function getMenuPosition($id)
		{
			$order = varset(self::$user_pref['core-flexpanel-order'], array());
			$stored = (is_array($order) && isset($order[$id])) ? $order[$id] : null;

			if (is_array($stored) && isset($stored['area']) && in_array($stored['area'], $this->areas, true)) {
				return array(
					'area'   => $stored['area'],
					'weight' => (int) varset($stored['weight'], 0),
				);
			}

			$default = array(
				'area'   => 'menu-area-02',
				'weight' => 1000,
			);

			$positions = $this->getDefaultPositions();

			$layout = varset(self::$user_pref['core-flexpanel-layout'], 'default');

			if (!empty($positions[$layout][$id])) {
				return $positions[$layout][$id];
			}

			/*if (strpos($id, 'plug-infopanel-') === 0) // addon dashboards default to area 2.
			{
				$default = array(
					'area'   => 'menu-area-02',
					'weight' => 1000,
				);
			}
			*/
			return $default;
		}


		/**
		 * Get default menu positions. see flexpanel class
		 *
		 * @return array
		 */
		function getDefaultPositions()
		{
			return array(
				'default'           => array(
					'core-infopanel-mye107'         => array(
						'area'   => 'menu-area-07',
						'weight' => 0,
					),
					'core-infopanel-news'           => array(
						'area'   => 'menu-area-08',
						'weight' => 0,
					)
				),
			);
		}

		/**
		 * Displays e107.org feeds 
		 * @param array $options 
		 * @return null 
		 */
		public function core_infopanel_news($options = array())
		{
			//LITE-SKIP Lite removes the e107.org admin RSS feed entirely (no phone-home).
			//LITE-SKIP e-adminfeed panel + ADMINFEEDMORE dropped. See Lite #88.
			//LITE-SKIP $ns = e107::getRender();
			//LITE-SKIP
			//LITE-SKIP $dashboardUniqueId 	= varset($options['uniqueId'], time());
			//LITE-SKIP $dashboardStyle		= varset($options['style'], 'flexbox');
			//LITE-SKIP
			//LITE-SKIP $newsTabs = array();
			//LITE-SKIP $newsTabs['coreFeed'] = array('caption' => LAN_GENERAL, 'text' => "<div id='e-adminfeed' style='min-height:300px'></div><div class='right'><a rel='external' href='" . ADMINFEEDMORE . "'>" . LAN_MORE . "</a></div>");
			//LITE-SKIP $newsTabs['pluginFeed'] = array('caption' => LAN_PLUGIN, 'text' => "<div id='e-adminfeed-plugin'></div>");
			//LITE-SKIP $newsTabs['themeFeed'] = array('caption' => LAN_THEMES, 'text' => "<div id='e-adminfeed-theme'></div>");
			//LITE-SKIP
			//LITE-SKIP $code = "
			//LITE-SKIP jQuery(function($){
			//LITE-SKIP 	$('#e-adminfeed').load('" . e_ADMIN . "admin.php?mode=core&type=feed');
			//LITE-SKIP 	$('#e-adminfeed-plugin').load('" . e_ADMIN . "admin.php?mode=addons&type=plugin');
			//LITE-SKIP 	$('#e-adminfeed-theme').load('" . e_ADMIN . "admin.php?mode=addons&type=theme');	
			//LITE-SKIP });
			//LITE-SKIP ";
			//LITE-SKIP e107::js('inline', $code, 'jquery');
			//LITE-SKIP
			//LITE-SKIP $ns->setStyle($dashboardStyle);
			//LITE-SKIP $ns->setUniqueId($dashboardUniqueId);
			//LITE-SKIP
			//LITE-SKIP $coreInfoPanelNews = $ns->tablerender(LAN_LATEST_e107_NEWS, e107::getForm()->tabs($newsTabs, array('active' => 'coreFeed')), $dashboardUniqueId, true);
			//LITE-SKIP
			//LITE-SKIP return $coreInfoPanelNews;
		}

		/**
		 * Displays core icons
		 */
		function core_infopanel_icons($options = array())
		{

			$ns = e107::getRender();

			$dashboardUniqueId 	= varset($options['uniqueId'], time());
			$dashboardStyle		= varset($options['style'], 'flexbox');
			$dashboardLinks     = varset($options['links'], '');
			$dashboardCaption     = varset($options['caption'], '');

			$newarray = e107::getNav()->adminLinks($dashboardLinks);

			$adminPanel = "<div id='.$dashboardUniqueId.' >";

			foreach ($newarray as $key => $val) {
				if ($dashboardLinks == "plugins") {
					if ($tmp = e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div")) {
						$adminPanel .= $tmp;
					}
				} else {
					//if (in_array($key, self::$userAdminPanelArray))  // so confusing for not advanced main admin
					if (true) {
						if ($tmp = e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div")) {
							$adminPanel .= $tmp;
						}
					}
				}
			}
			$adminPanel .= "</div>";

			$ns->setStyle($dashboardStyle);
			$ns->setUniqueId($dashboardUniqueId);

			$coreInfoPanelAdmin = $ns->tablerender($dashboardCaption, $adminPanel, $dashboardUniqueId, true);

			return $coreInfoPanelAdmin;
		}

		function core_categories_icons($options = array())
		{
			$ns = e107::getRender();

			$admin_cat = e107::getNav()->adminCats();
			$dashboards = array();

			foreach ($admin_cat['id'] as $cat_key => $cat_id) {
				$text_rend = '';
				if ($cat_key != 6) {

					$testarray = $options['personalize'] ?  self::$userAdminPanelArray : self::$fullAdminPanelArray;
					foreach (self::$fullAdminIcons as $key => $funcinfo) {
						//$result = condition ? value1 : value2;
						if (in_array($key, $testarray)) {

							if ($funcinfo[4] == $cat_key) {
								$text_rend .= e107::getNav()->renderAdminButton($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'div');
							}
						} else {
							//icon is disabled
						}
					}
				} else {
					//nothing, all plugins are rendered with separated method
				}

				if ($text_rend) {
					$dashboards[] = array('caption' => $admin_cat['title'][$cat_key], 'text' => $text_rend, 'mode' => $cat_id);
				}
			}

			if (!empty($dashboards)) {
				$ns->setStyle('flexpanel');
				foreach ($dashboards as $val) {
					$id = $val['mode'];
					$id = str_replace('_', '-', $id); // TODO fix this if they solve #4940 different way
					$ns->setUniqueId($id);
					$inc = $ns->tablerender($val['caption'], $val['text'], $val['mode'], true);
					$this->addToPosition($id, $inc);
				}
			};
			return false;
		}





		/**
		 * Displays group icons
		 * group of plugins with the same e_dashboard method
		 * 
		 */

		function addon_group_icons($options = array())
		{

			$ns = e107::getRender();

			$dashboardUniqueId 	= varset($options['uniqueId'], time());
			$dashboardStyle		= varset($options['style'], 'flexbox');
			$dashboardKey     = varset($options['key'], '');
			$dashboardCaption     = varset($options['caption'], '');

			$fullarray = self::$fullPluginIcons; //all plugins

			if ($plugs = e107::getAddonConfig('e_dashboard', null, $dashboardKey)) {

				foreach ($plugs as $key => $plug) {
					//check if is key
					$newarray["p-" . $key] = $fullarray["p-" . $key];
				}
			}
			$adminPanel = "<div id='.$dashboardUniqueId.' >";

			foreach ($newarray as $key => $val) {
				if ($tmp = e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div")) {

					$adminPanel .= $tmp;
				}
			}
			$adminPanel .= "</div>";

			$ns->setStyle($dashboardStyle);
			$ns->setUniqueId($dashboardUniqueId);

			$coreInfoPanelAdmin = $ns->tablerender($dashboardCaption, $adminPanel, $dashboardUniqueId, true);

			return $coreInfoPanelAdmin;
		}

		/*****************************************/
		/* multi dashboard for e_dashboard in tabs, chart is hardcoded in parent
		/*****************************************/

		public function addon_single_panel($options = array())
		{

			$ns = e107::getRender();

			// THIS IS CORRECT APPROACH FOR NOW
			// --------------------- Plugin Addon Dashboards ---------------------- eg. e107_plugin/user/e_dashboard.php
			// each plugin renders its own panel - chart method is needed 
			$dashboards = $this->getAddonDashboards($options);

			if (!empty($dashboards)) {
				$ns->setStyle('flexpanel');
				foreach ($dashboards as $val) {
					$id = $val['mode'];
					$id = str_replace('_', '-', $id); // TODO fix this if they solve #4940 different way
					$ns->setUniqueId($id);
					$inc = $ns->tablerender($val['caption'], $val['text'], $val['mode'], true);
					$this->addToPosition($id, $inc);
				}
			}

			return false;
		}

		function getAddonDashboards($options = array())
		{

			$arr = array();

			$dashboardKey     = varset($options['key'], 'chart');

			if ($plugs = e107::getAddonConfig('e_dashboard', null, $dashboardKey)) {

				foreach ($plugs as $plug => $val) {
					$adg = e107::getAddon($plug, 'e_dashboard');

					if (!empty($adg->chartCaption)) {
						$cap = $adg->chartCaption;
					} else {
						$cap = defset('LAN_PLUGIN_' . strtoupper($plug) . '_NAME', ucfirst($plug));
					}

					foreach ($val as $k => $item) {


						if (!empty($item)) {
							//	$var[] = $item;
							$renderMode = 'plug-infopanel-' . $plug . "-" . intval($k);



							if (!isset($item['text'])) {

								foreach ($item as $key => $v) // make sure the ids are unique.
								{
									$newkey = eHelper::dasherize($plug . '-' . $k . '-' . $key);
									$item[$newkey] = $v;
									unset($item[$key]);
								}

							
								$t = e107::getForm()->tabs($item);

								//	$text .= $ns->tablerender($cap, $t, $renderMode, true);
								$arr[] = array('caption' => $cap, 'text' => $t, 'mode' => $renderMode);
							} else {
								//	$text .= $ns->tablerender($item['caption'], $item['text'], $renderMode, true);
								$arr[] = array('caption' => $item['caption'], 'text' => $item['text'], 'mode' => $renderMode);
							}
						}
					}
				}
			}


			return $arr;
		}

		function render_infopanel_icons()
		{

			$frm = e107::getForm();
			$user_pref = self::$user_pref;

			$text = "<div style='padding-left:20px'>";


			$myE107 = varset($user_pref['core-infopanel-mye107'], array());
			if (empty($myE107)) // Set default icons.
			{
				$defArray = array(
					0  => 'e-administrator',
					1  => 'e-cpage',
					2  => 'e-frontpage',
					3  => 'e-mailout',
					4  => 'e-image',
					5  => 'e-menus',
					6  => 'e-meta',
					7  => 'e-newspost',
					8  => 'e-plugin',
					9  => 'e-prefs',
					10 => 'e-links',
					11 => 'e-theme',
					12 => 'e-userclass2',
					13 => 'e-users',
					14 => 'e-wmessage'
				);
				$user_pref['core-infopanel-mye107'] = $defArray;
			}


			foreach (self::$fullAdminIcons as $key => $icon) {
				if (getperms($icon['perms'])) {
					$checked = (varset($user_pref['core-infopanel-mye107']) && in_array($key, $user_pref['core-infopanel-mye107'])) ? true : false;
					$text .= "<div class='left f-left list field-spacer form-inline' style='display:block;height:24px;width:200px;'>
		                        " . $icon['icon'] . ' ' . $frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked) . "</div>";
				}
			}


			$text .= "</div><div class='clear'>&nbsp;</div>";

			$text .= "<div style='padding-left:20px'>";
			foreach (self::$fullPluginIcons as $key => $icon) {
				if (getperms($icon['perms'])) {
					$checked = (in_array('p-' . $key, $user_pref['core-infopanel-mye107'])) ? true : false;
					$text .= "<div class='left f-left list field-spacer form-inline' style='display:block;height:24px;width:200px;'>
		                         " . $icon['icon'] . $frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked) . "</div>";
				}
			}

			$text .= "</div><div class='clear'>&nbsp;</div>";
			return $text;
		}

		function render_infopanel_options($render = false)
		{
			$frm = e107::getForm();
			$mes = e107::getMessage();
			$ns = e107::getRender();

			if ($render == false) {
				return "";
			}

			$text2 = $ns->tablerender(LAN_PERSONALIZE_ICONS, $this->render_infopanel_icons(), 'personalize', true);
			$text2 .= "<div class='clear'>&nbsp;</div>";
			//	$text2 .= $ns->tablerender(LAN_PERSONALIZE_MENUS, $this->render_infopanel_menu_options(), 'personalize', true);
			$text2 .= "<div class='clear'>&nbsp;</div>";
			$text2 .= "<div id='button' class='buttons-bar center'>";
			$text2 .= $frm->admin_button('submit-mye107', LAN_SAVE, 'create');
			$text2 .= "</div>";

			return $mes->render() . $text2;
		}


		/**
		 * Render contents.
		 */
		public function render()
		{

			$mes = e107::getMessage();

			$frm = e107::getForm();

			if (e_AJAX_REQUEST && isset($_POST['core-flexpanel-order'])) {
				if ($this->canEditLayout()) {
					$this->buildPanels();
					$this->saveOrder($_POST['core-flexpanel-order']);
				}
				exit;
			}

			if (varset($_GET['mode']) == 'customize') {
				echo $frm->open('infopanel', 'post', e_SELF);
				//echo $ns->tablerender(LAN_DASHBOARD_LAYOUT, $this->renderLayoutPicker(), 'personalize', true);
				echo '<div class="clear">&nbsp;</div>';
				echo $this->render_infopanel_options(true);
				echo $frm->close();
				return;
			}

			$this->buildPanels();

			/*
			// --------------------- User Selected Menus ----------------------
			if (varset($user_pref['core-infopanel-menus']))
			{
				$ns->setStyle('flexpanel');
				foreach ($user_pref['core-infopanel-menus'] as $val)
				{
					// Custom menu.
					if (is_numeric($val))
					{
						$menu = e107::getDb()->retrieve('page', 'menu_name', 'page_id = ' . (int) $val);
						$id = 'cmenu-' . $menu;
						$inc = e107::getMenu()->renderMenu($val, null, null, true);
					}
					else
					{
						$id = $frm->name2id($val);
						$inc = $tp->parseTemplate("{PLUGIN=$val|TRUE}");
					}
					$info = $this->getMenuPosition($id);
					if (!isset($this->positions[$info['area']][$info['weight']]))
					{
						$this->positions[$info['area']][$info['weight']] = '';
					}
					$this->positions[$info['area']][$info['weight']] .= $inc;
				}
			}
			*/

			// Sorting panels.
			foreach ($this->positions as $key => $value) {
				ksort($this->positions[$key]);
			}

			$FLEXPANEL_LAYOUT = e107::getCoreTemplate("dashboard", 'layout');

			$template = varset($FLEXPANEL_LAYOUT);
			$template = str_replace('{MESSAGES}', $mes->render(), $template);

			foreach ($this->positions as $key => $value) {
				$token = '{' . strtoupper(str_replace('-', '_', $key)) . '}';
				$template = str_replace($token, implode("\n", $value), $template);
			}

			echo $template;
		}

		/**
		 * Collect the panels visible to the current admin into their positions.
		 */
		protected function buildPanels()
		{
			/* LOGIC CHANGE SET EVERYTHING IN TEMPLATE *************************************
			/* TODO FIXME template name - for now trying to avoid to use admin_template   */

			$supported_panels = (array) e107::getTemplate(false, 'dashboard', 'panels');

			foreach ($supported_panels as $key => $panel) {


				$params  = $panel;
				$perm = varset($params['perm'], false);
				$multi = varset($params['multi'], false);

				if (!getperms($perm)) continue;


				$params['uniqueId'] 	= $key;
				$params['style'] 		= varset($params['style'], 'flexpanel');
				//	$params['options'] 		= $panel ;

				//e107::callMethod("adminstyle_dashboard", $panel_type, $params);  this is not working for multi dashboard 
				$method_name = varset($panel['method_name'], $key);
				$method_name = str_replace('-', '_', $method_name); // TODO fix this if they solve #4940 different way

				$text = '';
				if (method_exists('adminstyle_dashboard', $method_name)) {

					$text = $this->$method_name($params);
				}

				if ($text)  //or test multi par
				{
					$this->addToPosition($key, $text);
				}
			}

			/*  END OF CHANGE **************************************************************/
		}
	}
