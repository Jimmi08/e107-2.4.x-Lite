<?php
/*
* e107 website system
*
* Copyright (c) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom FAQ install/uninstall/update routines
*
*/
e107::lan('rss', true);

class rss_setup
{
/*	
 	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}
*/
	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();

		// LITE MODIFICATION: seed the default "news" feed row only when the news
		// plugin is actually installed. Lite does not bundle news by default, so
		// seeding unconditionally (as upstream does) would create a feed row whose
		// rss_path/rss_url point at a plugin that may not exist. Guarding on
		// e107::isInstalled('news') keeps the seed correct on Lite while still
		// matching upstream behaviour whenever news IS present.
		if (e107::isInstalled('news'))
		{
			$insert = array(
				'rss_id'        => 0,
				'rss_name'      => RSS_NEWS,
				'rss_url'       => 'news',
				'rss_topicid'   => '',
				'rss_path'      => 'news',
				'rss_text'      => RSS_PLUGIN_LAN_7,
				'rss_datestamp' => time(),
				'rss_class'     => '0',
				'rss_limit'     => '9'
			);

			$status = ($sql->createQueryBuilder()->insert('rss')->valuesTyped($insert)->execute()) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			$mes->add(LAN_DEFAULT_TABLE_DATA.": rss", $status);
		}
	}
/*	
	function uninstall_options()
	{

	}


	function uninstall_post($var)
	{
		// print_a($var);
	}

	function upgrade_post($var)
	{
		// $sql = e107::getDb();
	}
*/	
}
