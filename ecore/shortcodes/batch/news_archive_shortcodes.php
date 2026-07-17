<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/news_archives.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
// include_once(e_HANDLER.'shortcode_handler.php');
// $news_archive_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);


class news_archive_shortcodes extends e_shortcode
{

	function sc_archive_bullet()
	{
		$bullet = '';
		if(defined('BULLET'))
		{
			$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
		}
		elseif(file_exists(THEME.'images/bullet2.gif'))
		{
			$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
		}
		return $bullet;
	
	}

	
	function sc_archive_link()
	{
		// LITE MODIFICATION: news archive link uses e107::url('news','item')
		// instead of upstream's e107::getUrl()->create('news/view/item').
		// Lite's news does NOT use the core eRouter, so the upstream call
		// would break routing. This is a permanent Lite divergence — the
		// rest of upstream #5785 (a23080bb9: category escaping guard, 'defs'
		// mode) is kept as-is.
		$url = e107::url('news', 'item', $this->var);
		$title = e107::getParser()->toHTML($this->var['news_title'], TRUE, 'TITLE');

		return "<a href='".$url."'>".$title."</a>";
	}

	
	function sc_archive_author()
	{
		return "<a href='".e_BASE."user.php?id.".$this->var['user_id']."'>".$this->var['user_name']."</a>";
	}
	

	function sc_archive_datestamp()
	{
		return e107::getParser()->toDate($this->var['news_datestamp'], 'short');
	}
	

	function sc_archive_category()
	{
		return !empty($this->var['category_name']) ? e107::getParser()->toHTML($this->var['category_name'], FALSE, 'defs') : '';
	}


}
		

