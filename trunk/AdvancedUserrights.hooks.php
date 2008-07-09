<?php
/* 
 * Advanced User Rights Management extension for MediaWiki
 * Copyright (C) 2008  Raymond Jelierse
 *	
 * This extension is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *	
 * This extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *	
 * You should have received a copy of the GNU General Public License along
 * with this extension; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (!defined ('MEDIAWIKI'))
	die ();

function userrights_onSkinTemplateNavUrls (&$nav_urls)
{
	global $wgUser, $wgTitle;
		
	if (!$wgUser->isAllowed ('userrights'))
		return true;
	
	if ($wgTitle->getNamespace() == NS_USER || $wgTitle->getNamespace() == NS_USER_TALK)
	{
		wfLoadExtensionMessages ('AdvancedUserrights');
		
		$page = SpecialPage::getTitleFor('AdvancedUserrights', $wgTitle->getPrefixedText());
		$nav_urls['userrights-editusergroup'] = array (
			'href' => $page->getLocalURL()
		);
	}
	else
		$nav_urls['userrights-editusergroup'] = false;
	
	return true;
}