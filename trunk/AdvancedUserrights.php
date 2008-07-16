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
	'version'        => '0.2.1 (2008-07-10)',
	'url'            => 'http://code.google.com/p/advanced-userrights-extension/',
	'descriptionmsg' => 'advanceduserrights-desc',
);

/**
 * Extension setup
 */
$wgExtensionMessagesFiles['AdvancedUserrights'] = dirname (__FILE__) . '/AdvancedUserrights.i18n.php';
$wgSpecialPages['AdvancedUserrights'] = 'AdvancedUserrightsPage';
$wgSpecialPageGroups['AdvancedUserrights'] = 'permissions';
$wgAutoloadClasses['AdvancedUserrightsPage'] = dirname (__FILE__) . '/AdvancedUserrights.body.php';
$wgHooks['SkinTemplateNavUrls'][] = array ('efSkinTemplateNavUrls', array ('AdvancedUserrights', 'userrights', 'userrights-editusergroup'));

$wgExtensionMessagesFiles['UserInformation'] = dirname (__FILE__) . '/UserInformation.i18n.php';
$wgSpecialPages['UserInformation'] = 'UserInformationPage';
$wgSpecialPageGroups['UserInformation'] = 'users';
$wgAutoloadClasses['UserInformationPage'] = dirname (__FILE__) . '/UserInformation.body.php';
$wgAvailableRights[] = 'userinfo';
$wgGroupPermissions['userinfo']['userinfo'] = true;
$wgAvailableRights[] = 'checkip';
$wgGroupPermissions['checkip']['checkip'] = true;
$wgGroupPermissions['checkip']['userinfo'] = true;
$wgHooks['AddNewAccount'][] = 'efUpdateCheckIPTable';
$wgHooks['AutoAuthenticate'][] = 'efUpdateCheckIPTable';
$wgHooks['SkinTemplateNavUrls'][] = array ('efSkinTemplateNavUrls', array ('UserInformation', 'userinfo', 'userinfo-details'));

require_once dirname (__FILE__) . '/AdvancedUserrights.hooks.php';

/**
 * Global configuration settings
 */
/**
 * Enables the logging of IP-addresses for registered users, to allow more effective blocking.
 *
 * Disabled by default to prevent possible privacy policy violations.
 */
$auEnableCheckIP = false;

// Debug information since we are developing
#$wgShowExceptionDetails = true;
#$wgShowSQLErrors = true;