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

/**
 * Extension setup
 */
$wgExtensionFunctions[] = 'efAdvancedUserrightsSetup';
$wgExtensionFunctions[] = 'efUserInformationSetup';
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

/**
 * Sets up the extension for Special:AdvancedUserrights
 */
function efAdvancedUserrightsSetup ()
{
	global $wgExtensionMessagesFiles, $wgSpecialPages, $wgSpecialPageGroups, $wgAutoloadClasses, $wgHooks;
	
	$wgExtensionMessagesFiles['AdvancedUserrights'] = dirname (__FILE__) . '/AdvancedUserrights.i18n.php';
	$wgSpecialPages['AdvancedUserrights'] = 'AdvancedUserrightsPage';
	$wgSpecialPageGroups['AdvancedUserrights'] = 'permissions';
	$wgAutoloadClasses['AdvancedUserrightsPage'] = dirname (__FILE__) . '/AdvancedUserrights.body.php';
	$wgHooks['SkinTemplateNavUrls'][] = array ('efSkinTemplateNavUrls', 'AdvancedUserrights', 'userrights', 'userrights-editusergroup');
}

/**
 * Sets up the extension for Special:UserInformation
 */
function efUserInformationSetup ()
{
	global $auEnableCheckIP, $wgAvailableRights, $wgGroupPermissiosn, $wgExtensionMessagesFiles, $wgSpecialPages, $wgSpecialPageGroups, $wgAutoloadClasses, $wgHooks;
	
	$wgAvailableRights[] = 'userinfo';
	$wgGroupPermissions['userinfo']['userinfo'] = true;
	$wgExtensionMessagesFiles['UserInformation'] = dirname (__FILE__) . '/UserInformation.i18n.php';
	$wgSpecialPages['UserInformation'] = 'UserInformationPage';
	$wgSpecialPageGroups['UserInformation'] = 'users';
	$wgAutoloadClasses['UserInformationPage'] = dirname (__FILE__) . '/UserInformation.body.php';
	$wgHooks['SkinTemplateNavUrls'][] = array ('efSkinTemplateNavUrls', 'UserInformation', 'userinfo', 'userinfo-details');
	
	if ($auEnableCheckIP)
	{
		$wgAvailableRights[] = 'checkip';
		$wgGroupPermissions['checkip']['checkip'] = true;
		$wgGroupPermissions['checkip']['userinfo'] = true;
		$wgHooks['AddNewAccount'][] = 'efUpdateCheckIPTable';
		$wgHooks['AutoAuthenticate'][] = 'efUpdateCheckIPTable';
	}
}

// Debug information since we are developing
#$wgShowExceptionDetails = true;
#$wgShowSQLErrors = true;