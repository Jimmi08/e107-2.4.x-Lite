<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2016 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }


if(USER_AREA)
{

    $tp = e107::getParser();
    $sql = e107::getDb();

	$rows = $sql->createQueryBuilder()->select('*')->from('rss')
		->where('rss_class', 0)
		->where('rss_limit', '>', 0)
		->orderBy('rss_name')
		->fetchAll();

	foreach($rows as $row)
	{
		// LITE MODIFICATION: check rss_topicid (not rss_url) for the wildcard.
		// The wildcard '*' is only ever stored in rss_topicid; rss_url is the plain
		// feed key and never contains '*', so the upstream test on rss_url never
		// filters anything. Reported upstream as e107inc/e107 #5872.
		if(strpos($row['rss_topicid'], "*") === false) // Wildcard topic_id's should not be listed
		{
			$name = $tp->toHTML($row['rss_name'], TRUE, 'no_hook, emotes_off');
			$title = htmlspecialchars(SITENAME, ENT_QUOTES, 'utf-8')." ".htmlspecialchars($name, ENT_QUOTES, 'utf-8');

			e107::link([
			    'rel'   => 'alternate',
			    'type'  => 'application/rss+xml',
			    'title' => $title,
			    'href'  => e107::url('rss','rss', $row, array('mode'=>'full'))
			]);

			e107::link([
			    'rel'   => 'alternate',
			    'type'  => 'application/atom+xml',
			    'title' => $title,
			    'href'  => e107::url('rss','atom', $row, array('mode'=>'full'))
			]);

		}
	}

	unset($name, $title);
}
