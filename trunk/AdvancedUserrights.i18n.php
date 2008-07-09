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
	# User rights
	'advanceduserrights'         => 'Advanced user rights management',
	'advanceduserrights-desc'    => 'Advanced user rights management to replace the default user rights manager in MediaWiki',
	'advanceduserrights-submit'  => 'Save group memberships',
	'advanceduserrights-groupchange-explained' => 'Check each group you want this user to be a member of. Unchecking a currently checked group will remove the group membership. Behind every group the rights that a group has are listed.',
	// Note: $1 gets replaced by 'group-bureaucrat' and $2 by 'group-sysop'.
	'advanceduserrights-groupchanged' => 'Succesfully stored group memberships for user $1',
	# Toolbar
	'advanceduserrights-withselected' => 'With selected...',
	# Actions
	'advanceduserrights-groupadd'       => 'Add to group',
	'advanceduserrights-groupchange'    => 'Change group memberships',
	'advanceduserrights-groupchangefor' => 'Change group memberships for $1', // $1 gets replaced by username.
	'advanceduserrights-groupremove'    => 'Remove from group',
	'advanceduserrights-blockuser'      => 'Block user',
	# Table headers
	'advanceduserrights-username'         => 'Username',
	'advanceduserrights-groups'           => 'Groups',
	'advanceduserrights-registrationdate' => 'Registration date',
	'advanceduserrights-editcount'        => '# of edits',
	# Error messages
	'advanceduserrights-errornoselection' => 'No users selected! You have to select at least one user.',
	'advanceduserrights-errorselectone'   => 'Please select a single user.',
	# Navigation
	'nav-start' => 'first',
	'nav-prev'  => 'previous $1', // $1 gets replaced by the number of items per page.
	'nav-next'  => 'next $1', // $1 gets replaced by the number of items per page.
	'nav-end'   => 'last',
);
?>