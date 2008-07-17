<?php
/* 
 * Advanced User Rights Management extension for MediaWiki
 * Copyright (C) 2008  Raymond Jelierse
 *	
 * This extension is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License.
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

function efSkinTemplateNavUrls ($args, &$nav_urls)
{
	global $wgUser, $wgTitle;
	// Stoopid hooks system only allows for one argument
	list ($specialPage, $permission, $name) = $args;
		
	if (!$wgUser->isAllowed ($permission))
		return true;
	
	if ($wgTitle->getNamespace() == NS_USER || $wgTitle->getNamespace() == NS_USER_TALK)
	{
		wfLoadExtensionMessages ($specialPage);
		
		$page = SpecialPage::getTitleFor($specialPage, $wgTitle->getText());
		$nav_urls[$name] = array (
			'href' => $page->getLocalURL()
		);
	}
	else
		$nav_urls[$name] = false;
	
	return true;
}

function efUpdateCheckIPTable (&$user, &$byEmail = false)
{
	global $auEnableCheckIP, $wgOut;
	
	if ($auEnableCheckIP === false)
	{
		$wgOut->debug ("CheckIP disabled. Returning.\n\n");
		return true;
	}
	
	// Get user ID
	$id = $user->getID ();
	// Return for anonymous users
	if ($id == 0)
	{
		$wgOut->debug ("User is not logged in, or something went wrong when initializing the user object.\n\n");
		return true;
	}
	// Get IP
	$ip = wfGetIP();
	
	// Check wether the current session has been stored, and the IP-address has not changed in the meantime.
	if (isset ($_SESSION['checkIPWasUpdated']) && ($_SESSION['checkIPWasUpdated'] == $ip))
	{
		$wgOut->debug ("User session was already stored with IP-address $ip\n\n");
		return true;
	}

	// Get XFF header
	$xff = wfGetForwardedFor();
	list($xff_ip,$trusted) = efGetClientIPfromXFF( $xff );
	// Our squid XFFs can flood this up sometimes
	$isSquidOnly = efXFFChainIsSquid( $xff );
	// Get agent
	$agent = wfGetAgent();
	// Get current timestamp
	$touched = wfTimestampNow ();
	
	$dbw = wfGetDB (DB_MASTER);
	$tableName = $dbw->tableName ('user_checkip');
	
	$dbRow = array (
		'user_id' => $id,
		'user_ip' => IP::sanitizeIP ($ip),
		'user_ip_hex' => $ip ? IP::toHex ($ip) : NULL,
		'user_xff' => !$isSquidOnly ? $xff : '',
		'user_xff_hex' => ($xff_ip && !$isSquidOnly) ? IP::toHex ($xff_ip) : NULL,
		'cu_touched' => $touched
	);
	$tableFields = implode (', ', array_keys ($dbRow));
	$tableValues = $dbw->makeList (array_values ($dbRow));
	
	$sql = "INSERT INTO $tableName ($tableFields) VALUES ($tableValues) ON DUPLICATE KEY UPDATE cu_touched = '$touched'";
	$wgOut->debug ("Running query: $sql\n\n");
	$dbw->query ($sql, __METHOD__);
	
	$wgOut->debug ("Stored user session with IP-address $ip\n\n");
	
	$_SESSION['checkIPWasUpdated'] = $ip;
	
	return true;
}

/**
 * Locates the client IP within a given XFF string
 * @param string $xff
 * @param string $address, the ip that sent this header (optional)
 * @return array( string, bool )
 */
function efGetClientIPfromXFF( $xff, $address=NULL ) {
	if( !$xff ) 
		return array(null, false);
	// Avoid annoyingly long xff hacks
	$xff = trim( substr( $xff, 0, 255 ) );
	$client = null;
	$trusted = true;
	// Check each IP, assuming they are separated by commas
	$ips = explode(',',$xff);
	foreach( $ips as $n => $ip ) {
		$ip = trim($ip);
		// If it is a valid IP, not a hash or such
		if( IP::isIPAddress($ip) ) {
			# The first IP should be the client.
			# Start only from the first public IP.
			if( is_null($client) ) {
				if( IP::isPublic($ip) )
					$client = $ip;
			# Check that all servers are trusted
			} else if( !wfIsTrustedProxy($ip) ) {
				$trusted = false;
				break;
			}
		}
	}
	// We still have to test if the IP that sent 
	// this header is trusted to confirm results
	if ( $client != $address && (!$address || !wfIsTrustedProxy($address)) )
		$trusted = false;
	
	return array( $client, $trusted );
}

function efXFFChainIsSquid( $xff ) {
	global $wgSquidServers, $wgSquidServersNoPurge;

	if ( !$xff ) 
		false;
	// Avoid annoyingly long xff hacks
	$xff = trim( substr( $xff, 0, 255 ) );
	$squidOnly = true;
	// Check each IP, assuming they are separated by commas
	$ips = explode(',',$xff);
	foreach( $ips as $n => $ip ) {
		$ip = trim($ip);
		// If it is a valid IP, not a hash or such
		if ( IP::isIPAddress($ip) ) {
			if ( $n==0 ) {
				// The first IP should be the client...
			} else if ( !in_array($ip,$wgSquidServers) && !in_array($ip,$wgSquidServersNoPurge) ) {
				$squidOnly = false;
				break;
			}
		}
	}
	
	return $squidOnly;
}