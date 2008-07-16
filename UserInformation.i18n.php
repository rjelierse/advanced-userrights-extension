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

$messages = array ();

$messages['en'] = array (
	# User information
	'userinformation'            => 'User information',
	'userinfo-viewing'           => 'Viewing user information: $1', // $1 gets replaced by username.
	'userinfo-details'           => 'User details',
	'userinfo-lasttouch'         => 'Last modification:',
	'userinfo-regdate'           => 'Registration date:',
	'userinfo-emailconfirmed'    => 'Confirmed e-mail address:',
	'userinfo-noemailset'        => 'This user has no e-mail address set.',
	'userinfo-noemailconfirmed'  => 'This user has not yet confirmed it\'s e-mail address.',
	'userinfo-hasemailconfirmed' => 'This user has confirmed it\'s e-mail address at $1.', // $1 gets replaced by date of confirmation.
	'userinfo-editcount'         => 'Number of edits:',
	# IP-address listing
	'userinfo-iplist'     => 'List of known IP-addresses',
	'userinfo-noipsfound' => 'No IP-addresses were found for this user.',
	'userinfo-ipaddress'  => 'IP-address',
	'userinfo-lastseen'   => 'Last seen at',
	# IP-address setup
	'userinfo-setup'              => 'IP-address listing setup',
	'userinfo-nosetup'            => 'The database has not yet been prepared for using checkip, please complete the <span class="plainlinks">[$1 setup]</span>.', // $1 gets replaced by the link to the setup-page.
	'userinfo-setup-explain'      => 'A table must be added to the database before IP-address logging can work.',
	'userinfo-setup-button'       => 'Create table',
	'userinfo-setup-tableexists'  => 'The table already exists.',
	'userinfo-setup-tablecreated' => 'The table was successfully created.',
	# Default userinfo group
	'group-userinfo'        => 'User information',
	'group-userinfo-member' => 'User information',
	'grouppage-userinfo'    => 'Project:User information',
	# Default checkip group
	'group-checkip'        => 'Check IP',
	'group-checkip-member' => 'Check IP',
	'grouppage-checkip'    => 'Project:Check IP',
);