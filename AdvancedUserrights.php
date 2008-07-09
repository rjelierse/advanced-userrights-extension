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

$wgExtensionCredits['specialpage'][] = array (
	'name'           => 'Advanced Userrights',
	'author'         => '[http://www.wikid.eu/index.php/User:Rjelierse Raymond&nbsp;Jelierse]',
	'version'        => '0.1.2 (2008-07-08)',
	'url'            => 'http://www.wikid.eu/index.php/WikID:Extensions',
	'descriptionmsg' => 'advanceduserrights-desc',
);

// Special:AdvancedUserrights
//
// Set up language
$wgExtensionMessagesFiles['AdvancedUserrights'] = dirname (__FILE__) . '/AdvancedUserrights.i18n.php';
// Set up special page
$wgSpecialPages['AdvancedUserrights'] = 'AdvancedUserrightsPage';
$wgSpecialPageGroups['AdvancedUserrights'] = 'permissions';
$wgAutoloadClasses['AdvancedUserrightsPage'] = dirname (__FILE__) . '/AdvancedUserrights.body.php';
// Set up hooks
$wgHooks['SkinTemplateNavUrls'][] = 'userrights_onSkinTemplateNavUrls';
// Include hook functions
require_once dirname (__FILE__) . '/AdvancedUserrights.hooks.php';

// Special:UserInformation
//
// View information about users
$wgAvailableRights[] = 'userinfo';
$wgGroupPermissions['userinfo']['userinfo'] = true;
// View IP-addresses of users
$wgAvailableRights[] = 'checkip';
$wgGroupPermissions['checkip']['checkip'] = true;
$wgGroupPermissions['checkip']['userinfo'] = true;
// Set up language
$wgExtensionMessagesFiles['UserInformation'] = dirname (__FILE__) . '/UserInformation.i18n.php';
// Set up special page
$wgSpecialPages['UserInformation'] = 'UserInformationPage';
$wgSpecialPageGroups['UserInformation'] = 'users';
$wgAutoloadClasses['UserInformationPage'] = dirname (__FILE__) . '/UserInformation.body.php';
// Set up hooks
$wgHooks['SkinTemplateNavUrls'][] = 'userinfo_onSkinTemplateNavUrls';
$wgHooks['AddNewAccount'][] = 'userinfo_UpdateCheckUserTable';
$wgHooks['AutoAuthenticate'][] = 'userinfo_UpdateCheckUserTable';
// Include hook functions
require_once dirname (__FILE__) . '/UserInformation.hooks.php';

// Debug information since we are developing
#$wgShowExceptionDetails = true;
#$wgShowSQLErrors = true;