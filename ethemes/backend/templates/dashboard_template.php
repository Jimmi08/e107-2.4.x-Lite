<?php
/*
+ ----------------------------------------------------------------------------+
|
| e107 website system
| Copyright (C) 2008-2016 e107 Inc (e107.org)
| Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
|
| Default layout for "flexpanel" admin dashboard style.
+ ----------------------------------------------------------------------------+
*/

$DASHBOARD_TEMPLATE['layout'] = '
<style> .row-flex .panel {
    height: auto;
}
 
.draggable-panels .panel-default {
    box-shadow: 0 1rem 3rem var(--panel-shadow-color) !important; 
    border-color: var(--panel-border-color);
	background: var(--panel-background-color); 
}

.draggable-panels .panel-heading {
    background: var(--panel-heading-bg); 
}

.draggable-panels .panel-heading   h3.panel-title {
		color: var(--panel-heading-color);
}	

.draggable-panels .panel-default  .core-mainpanel-block a {
    border-color: var(--panel-icon-border);
    color: var(--panel-icon-color);
    background: var(--panel-icon-bg);
	text-shadow: none;
	font-size: 13px;
}

 
.draggable-panels .panel-default  .core-mainpanel-block a:hover {
    border-color: var(--panel-icon-hover-border-color);
    color: var(--panel-icon-hover-color);;
    background: var(--panel-icon-hover-bg);
}


</style>
<div class="row">
	 
	<div class="col-md-12  admin-right-panel">
		<div class="sidebar-toggle">
			<a href="#" title="' . ADLAN_185 . '" data-toggle-sidebar="true">&nbsp;</a>
		</div>

		<div>
			<div class="row">
				<div class="col-sm-12 col-xs-12">
					{MESSAGES}
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12 col-xs-12">
					<div class="draggable-panels" id="menu-area-02">
						{MENU_AREA_02}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-6 col-xs-12">
					<div class="draggable-panels" id="menu-area-03">
						{MENU_AREA_03}
					</div>
				</div>

				<div class="col-sm-6 col-xs-12">
					<div class="draggable-panels" id="menu-area-04">
						{MENU_AREA_04}
					</div>
				</div>

			</div>
 
			<div class="row">
				<div class="col-sm-4 col-xs-12">
					<div class="draggable-panels" id="menu-area-05">
						{MENU_AREA_05}
					</div>
				</div>

				<div class="col-sm-4 col-xs-12">
					<div class="draggable-panels" id="menu-area-06">
						{MENU_AREA_06}
					</div>
				</div>

				<div class="col-sm-4 col-xs-12">
					<div class="draggable-panels" id="menu-area-07">
						{MENU_AREA_07}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-8 col-xs-12">
					<div class="draggable-panels" id="menu-area-08">
						{MENU_AREA_08}
					</div>
				</div>

				<div class="col-sm-4 col-xs-12">
					<div class="draggable-panels" id="menu-area-09">
						{MENU_AREA_09}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12 col-xs-12">
					<div class="draggable-panels" id="menu-area-10">
						{MENU_AREA_10}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';


//$DASHBOARD_TEMPLATE['positions']['menu-area-01'] = array();  // Sidebar. use admin_template with shortcodes 
$DASHBOARD_TEMPLATE['positions']['menu-area-02'] = array();  	// full top row
$DASHBOARD_TEMPLATE['positions']['menu-area-03'] = array();  	// Content left 1/2
$DASHBOARD_TEMPLATE['positions']['menu-area-04'] = array();		// Content ritgh 1/2
$DASHBOARD_TEMPLATE['positions']['menu-area-05'] = array();  	// Content left 1/3
$DASHBOARD_TEMPLATE['positions']['menu-area-06'] = array();		// Content center 1/3
$DASHBOARD_TEMPLATE['positions']['menu-area-07'] = array();	 	// Content right 1/3
$DASHBOARD_TEMPLATE['positions']['menu-area-08'] = array();	 	// Content left 2/3
$DASHBOARD_TEMPLATE['positions']['menu-area-09'] = array();	 	// Content right 1/3
$DASHBOARD_TEMPLATE['positions']['menu-area-10'] = array();	 	// full bottom row


/* Naming convention for key name  - in progress

1. core / plug / theme / addons = source
	plug - other addon than e_dashbord (f.e. comments), custom content
	addons - always e_dashboard 

2. infopanel / single / icons / multi
	infopanel = custom text/content, for core+plug
	singlepanel = one dashboard for all related plugins - for addons only
	multi = separated dashboards for each plugin - for addons only

3.  icons - icons are rendered
	tabs = tabs are rendered 
	key for infopanel

4.  key unique value if the same methods is used for more dashboards

*/

$caption = e107::getParser()->lanVars('LAN_CONTROL_PANEL', htmlspecialchars(ucwords((string) defset('USERNAME', '')), ENT_QUOTES, 'UTF-8', false));

if(getperms('1')) {
	//icons by admin categories  
	$DASHBOARD_TEMPLATE['panels']['core-multi-icons-category'] = array(
		'method_name' => 'core_categories_icons', 'links' => 'core', 'style' => 'flexpanel', 'caption' => 'flexpanel',  'multi' => true,  
		'perm' => '1', 'personalize' => true
	);
}
else {

	//all icons like before not not limited numbers - core version
	// --------------------- Personalized Panel -----------------------
	$DASHBOARD_TEMPLATE['panels']['core-infopanel-mye107'] = array('method_name' => 'core_infopanel_icons', 'links' => 'core', 'caption' => $caption, 'perm' => '');
}

//only plugins icons, personalization off - not possible
$DASHBOARD_TEMPLATE['panels']['core-infopanel-icons-plugins'] =  array(
	'method_name' => 'core_infopanel_icons', 'links' => 'plugin', 'style' => 'flexpanel', 'caption' => $caption . ' - Plugins', 'perm' => ''
);

/*
//ALTERNATIVE - all admin icons in one panel
//full admin icons, personalize still possible
$DASHBOARD_TEMPLATE['panels']['core-infopanel-icons-admin'] = array(
	'method_name' => 'core_infopanel_icons', 'links' => 'core', 'style' => 'flexpanel', 'caption' => 'flexpanel', 'perm'=> '0',
	'caption' => 'Admin'
);
*/
 
/* e107 news change: only for main admin */
$DASHBOARD_TEMPLATE['panels']['core-infopanel-news'] = array('perm' => '0');

/* comments for approval  */
$DASHBOARD_TEMPLATE['panels']['plug-infopanel-comments'] = array('method_name' => 'core_infopanel_comments',  'caption' => LAN_LATEST_COMMENTS, 'perm' => '');


/* core version renders only chart let plugin to decide how they want the dashboard to look */
$DASHBOARD_TEMPLATE['panels']['addons-single-info-chart'] = array('method_name' => 'addon_single_panel', 'key'=>'chart', 'perm' => '4');  //legacy
$DASHBOARD_TEMPLATE['panels']['addons-single-info-panel'] = array('method_name' => 'addon_single_panel', 'key'=>'panel', 'perm' => '');

/* custom panel for grouped plugin's icons */
$DASHBOARD_TEMPLATE['panels']['addons-single-icons-modules'] =  array(
	'method_name' => 'addon_group_icons', 'key' => 'modules_panel', 'style' => 'flexpanel',
	'caption' => SITENAME . " modules", 'perm' => ''
);
