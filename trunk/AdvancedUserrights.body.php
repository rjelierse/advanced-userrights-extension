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

/**
 * Class for advanced user rights management to replace UserrightsPage. Adds the option to batch add users to groups, more intuitive user management, better user list.
 *
 * @ingroup SpecialPage
 * @todo Clean up
 */
class AdvancedUserrightsPage extends SpecialPage
{
	var $mAction;
	// Pager options
	var $mLimitsShown = array( 20, 50, 100, 250, 500 );
	var $mDefaultLimit = 50;
	var $mLimit;
	var $mDefaultSort = 'user_name';
	var $mSort;
	var $mDefaultOrder = 'asc';
	var $mOrder;
	var $mDisplayedRights = array ('block', 'checkip', 'delete', 'bigdelete', 'undelete', 'editinterface', 'import', 'importupload', 'import-wizard', 'move', 'protect', 'rollback', 'upload', 'reupload', 'userinfo', 'userrights');
		
	public function __construct ()
	{
		wfLoadExtensionMessages ('AdvancedUserrights');
		parent::__construct ('AdvancedUserrights', 'userrights');
	}
	
	/**
	 * Function that builds the page.
	 *
	 * @param mixed $par: string supplied as a subpage if any, else null
	 */
	function execute ($par)
	{
		global $IP, $wgOut, $wgPageName, $wgRequest, $wgScriptPath, $wgUser;
		
		if( !$this->userCanExecute ($wgUser))
			return $this->displayRestrictionError ();
		
		$wgOut->addLink (array ('rel' => 'stylesheet', 'href' => str_replace ($IP, $wgScriptPath, dirname (__FILE__) . '/AdvancedUserrights.css')));
		
		$this->outputHeader ();
		
		$this->setHeaders ();
		
		if (!empty ($par))
		{
			list (/* $prefix */, $user_name) = explode (':', $par, 2);
			
			return $this->userGroupManagementForm ($user_name);
		}
					
		if ($wgRequest->wasPosted())
		{
			$buttons = array ('batchAddGroup');
			
			foreach ($buttons as $button)
			{
				if ($wgRequest->getCheck ($button))
					$this->mAction = $button;
			}
		}
				
		switch ($this->mAction)
		{
			case 'batchAddGroup':
				$this->batchGroupForm();
				break;
			default:
				$this->userListForm ();
				break;
		}
	}
	
	/***********************************************[ FORMS ]*************************************************************************/
		
	private function userListForm ($errorMessage = '')
	{
		global $wgLang, $wgOut, $wgRequest, $wgUser;
		
		// Create the toolbar for the userlist
		$toolbar  = Xml::openElement ('fieldset');
		$toolbar .= Xml::element ('legend', NULL, wfMsg ('advanceduserrights-withselected'));
		$toolbar .= Xml::submitButton (wfMsg ('advanceduserrights-groupadd'), array ('name' => 'batchAddGroup'));
		$toolbar .= Xml::closeElement ('fieldset');
		
		// Set the pagination settings
		$this->mOffset = $wgRequest->getInt ('offset', 0);
		$this->mLimit = $wgRequest->getInt ('limit', $wgUser->getIntOption ('rclimit', $this->mDefaultLimit));
		$this->mSort = $wgRequest->getVal ('sort', $this->mDefaultSort);
		$this->mOrder = strtoupper ($wgRequest->getVal ('order', $this->mDefaultOrder));
		
		// Prepare database query
		$dbr = wfGetDB (DB_SLAVE);
		
		$sqlFields = array(
			'user_name',
			'MAX(user_id) AS user_id',
			'COUNT(ug_group) AS numgroups',
			'MAX(ug_group) AS singlegroup',
			'user_registration',
			'user_editcount',
		);
		$sqlConds = array ('ipb_deleted IS NULL OR ipb_deleted = 0');
		$sqlOptions = array (
			'GROUP BY' => 'user_name',
			'LIMIT' => $this->mLimit,
			'OFFSET' => $this->mOffset,
			'ORDER BY' => "{$this->mSort} {$this->mOrder}",
		);
		list ($user,$user_groups,$ipblocks) = $dbr->tableNamesN('user','user_groups','ipblocks');
		$sqlTable = "$user LEFT JOIN $user_groups ON user_id=ug_user LEFT JOIN $ipblocks ON user_id=ipb_user AND ipb_auto=0";
		
		// Query database
		$result = $dbr->select ($sqlTable, $sqlFields, $sqlConds, __METHOD__, $sqlOptions);
		$nResults = $dbr->numRows ($result);
		if ($nResults == 0)
		{
			$wgOut->addHTML (wfMsg ('advanceduserrights-noresults'));
			return;
		}
		
		// Build pagination links
		// TODO: Create an efficient method for calculating the last page offset
		$this->navLinks['start'] = ($this->mOffset > 0) ? 'offset=0' : false;
		$this->navLinks['prev'] = ($this->mOffset > 0) ? ((($this->mOffset - $this->mLimit) < 0) ? 'offset=0' : 'offset=' . ($this->mOffset - $this->mLimit)) : false;
		$this->navLinks['next'] = ($this->mLimit == $nResults) ? 'offset=' . ($this->mOffset + $this->mLimit) : false;
		#$this->navLinks['end'] = ($this->mLimit > $nResults) ? 'dir=reverse' : false;
		$this->navLinks['end'] = false;
		$navbar = sprintf ('(%s | %s) %s',
		                   $this->buildNavLink ('start'),
						   $this->buildNavLink ('end'),
		                   wfMsg ('viewprevnext', $this->buildNavLink ('prev'),
						                          $this->buildNavLink ('next'),
						                          $this->buildLimitLink ()
						   )
		);
		
		// Build sortable headers
		$sortUsername = $this->buildSortLink ('user_name');
		$sortRegDate = $this->buildSortLink ('user_registration');
		$sortEditCount = $this->buildSortLink ('user_editcount');
		
		$table = Xml::openElement ('table', array ('cellpadding' => '0', 'cellspacing' => '0', 'class' => 'userrights'));
		
		$table .= '<tr>';
		$table .= Xml::tags ('th', array ('class' => 'colCheck'), '&nbsp;');
		$table .= Xml::tags ('th', array ('class' => 'colUsername'), Xml::element ('a', $sortUsername, wfMsg ('advanceduserrights-username')));
		$table .= Xml::element ('th', array ('class' => 'colGroups'), wfMsg ('advanceduserrights-groups'));
		$table .= Xml::tags ('th', array ('class' => 'colRegistered'), Xml::element ('a', $sortRegDate, wfMsg ('advanceduserrights-registrationdate')));
		$table .= Xml::tags ('th', array ('class' => 'colEdits'),  Xml::element ('a', $sortEditCount, wfMsg ('advanceduserrights-editcount')));
		$table .= Xml::tags ('th', array ('class' => 'colOptions'), '&nbsp;');
		$table .= '</tr>';
/*		
		$numRows = min ($nResults, $this->mLimit);
		for ($i = 0; $i < $numRows; $i++)
		{
			if ($this->mIsReverse)
				$dbr->seek ($numRows - $i);
			$row = $dbr->fetchObject ($result);
*/
		while ($row = $dbr->fetchObject ($result))
		{
			$userPage = Title::makeTitle (NS_USER, $row->user_name);
			$userPageLink = $wgUser->getSkin()->makeLinkObj ($userPage, htmlspecialchars ($userPage->getText()));
		
			if ($row->numgroups > 1)
			{
				$list = array();
				foreach (self::getGroups ($row->user_id) as $group)
					$list[] = self::buildGroupLink( $group );
				
				$groups = implode( ', ', $list );
			}
			elseif ($row->numgroups == 1)
				$groups = self::buildGroupLink ($row->singlegroup);
			else
				$groups = '';
			
			$userRegistration = $wgLang->timeanddate ($row->user_registration);
			$userInfoLink = SpecialPage::getTitleFor ('UserInformation', $userPage)->getLocalURL();
			
			$table .= '<tr>';
			$table .= Xml::tags ('td', array ('class' => 'colCheck'), Xml::check ('userChecked[' . $row->user_id . ']', false));
			$table .= Xml::tags ('td', array ('class' => 'colUsername'), $userPageLink);
			$table .= Xml::tags ('td', array ('class' => 'colGroups'), $groups);
			$table .= Xml::element ('td', array ('class' => 'colRegistered'), $userRegistration);
			$table .= Xml::element ('td', array ('class' => 'colEdits'), intval ($row->user_editcount));
			$table .= Xml::tags ('td', array ('class' => 'colOptions'), Xml::tags ('a', array ('href' => SpecialPage::getTitleFor ('UserInformation', $userPage)->getLocalURL()), Xml::element('img', array ('alt' => wfMsg ('userinformation'), 'title' => wfMsg ('userinformation'), 'src' => self::getImagePath('user-information.png'))))
			             . '&nbsp;|&nbsp;' . Xml::tags ('a', array ('href' => SpecialPage::getTitle ($userPage)->getLocalURL()), Xml::element('img', array ('alt' => wfMsg ('advanceduserrights-groupchange'), 'title' => wfMsg ('userrights-editusergroup'), 'src' => self::getImagePath('group-management.png')))));
			$table .= '</tr>';
		}

		$table .= Xml::closeElement ('table');
		
		if (!empty ($errorMessage)) $wgOut->addWikiText ('{{messagebox | type = serious | text = ' . wfMsg ($errorMessage) . '}}');
		#$wgOut->addHTML ($toolbar);
		$wgOut->addHTML ($navbar);
		$wgOut->addHTML ($table);
		$wgOut->addHTML ($navbar);
	}
	
	/**
	 * For for batch adding users to groups.
	 *
	 * @todo Check for existing group memberships when adding user to a selected group.
	 */
	private function batchGroupForm ()
	{
		$selection = $wgRequest->getArray ('userChecked');
		if (count ($selection) < 1)
		{
			$wgOut->addWikiText ('{{messagebox | type = serious | text = ' . wfMsg ('advanceduserrights-errornoselection') . '}}');
			$this->userListForm ();
			return;
		}
		
		if ($wgRequest->getCheck ('wpSubmit'))
		{
			$groups = $wgRequest->getArray ('groupChecked');
			
			foreach (array_keys ($selection) as $user)
				foreach ($groups as $group)
					self::addUserToGroup ($user, $group);
			
			$wgOut->addReturnTo (SpecialPage::getTitle());
			return;
		}
		
		$form  = Xml::openElement ('form', array ('method' => 'post', 'action' => SpecialPage::getTitle()->getLocalURL()));
		$form .= Xml::hidden ('title', SpecialPage::getTitle()->getText());
		
		$users = array ();
		foreach (array_keys ($selection) as $user)
		{
			$users[] = User::whoIs ($user);
			$form .= Xml::hidden ("userChecked[$user]", '1');
		}
		
		$userlist = implode (', ', $users);
		
		$form .= Xml::element ('p', NULL, wfMsg ('advanceduserrights-groupaddwhich', $userlist));
		$form .= Xml::openElement ('p');
		
		foreach (User::getAllGroups() as $group)
			$form .= Xml::checkLabel(User::getGroupName ($group), 'groupChecked[]', 'group_' . $group, false, array ('value' => $group)) . '<br />';
		
		$form .= Xml::closeElement ('p');
		$form .= Xml::hidden ('batchAddGroup', 'batchAddGroup');
		$form .= Xml::submitButton (wfMsg ('advanceduserrights-submit'), array ('name' => 'wpSubmit'));
		$form .= Xml::closeElement ('form');
		
		$wgOut->addHTML ($form);
	}
	
	private function userGroupManagementForm ($userName)
	{
		global $wgOut, $wgRequest, $wgUser;
		
		$user = User::newFromName ($userName);
		$availableGroups = User::getAllGroups ();
		$groups = is_array ($user->getGroups ()) ? $user->getGroups () : array ();
		
		if ($wgRequest->wasPosted ())
		{
			$groupChecked = $wgRequest->getArray ('groupChecked');
			
			$addedGroups = false;
			$removedGroups = false;
			foreach ($availableGroups as $group)
			{
				if (in_array ($group, $groups) && !in_array ($group, $groupChecked))
				{
					$user->removeGroup ($group);
					$removedGroups = true;
				}
				if (!in_array ($group, $groups) && in_array ($group, $groupChecked))
				{
					$user->addGroup ($group);
					$addedGroups = true;
				}
			}
			
			if ($removedGroups || $addedGroups)
			{
				$log = new LogPage ('rights');
				
				$log->addEntry ('rights', $user->getUserPage (), $wgRequest->getVal ('wpReason'), array (implode (', ', $groups), implode (', ', $groupChecked)));
			}
			
			$wgOut->addHTML (wfMsg ('advanceduserrights-groupchanged', $userName));
			$wgOut->addReturnTo (SpecialPage::getTitle ());
			
			return;
		}
		
		$wgOut->setPageTitle (wfMsg ('advanceduserrights-groupchangefor', $userName));
		
		$userPage = Title::makeTitle (NS_USER, $userName);
		$formAction = SpecialPage::getTitle ($userPage)->getLocalURL();
		
		$form  = Xml::openElement ('form', array ('method' => 'post', 'action' => $formAction));
		$form .= Xml::element ('p', NULL, wfMsg ('advanceduserrights-groupchange-explained', wfMsg ('group-bureaucrat'), wfMsg ('group-sysop')));
		$form .= Xml::openElement ('p');
		// Include the user group, but disable any changes to it.
		$permissions = array_intersect (User::getGroupPermissions (array ('user')), $this->mDisplayedRights);
		$form .= Xml::check ('', true, array ('disabled' => 'disabled')) . '&nbsp;' . User::getGroupName ('user') . '&nbsp;<span class="wikid-userrights-explain">' . implode (', ', $permissions) . '</span><br />';
		// Now do all the changable groups.
		sort ($availableGroups);
		foreach ($availableGroups as $group)
		{
			$groupName = User::getGroupName ($group);
			$permissions = array_intersect (User::getGroupPermissions (array ($group)), $this->mDisplayedRights);
			
			if (!empty ($permissions))
				$form .= Xml::check ("groupChecked[]", in_array ($group, $groups), array ('id' => "group_$group", 'value' => $group)) . '&nbsp;' . Xml::tags ('label', array('for' => "group_$group"), $groupName) . '&nbsp;<span class="wikid-userrights-explain">' . implode (', ', $permissions) . '</span>';
			else
				$form .= Xml::check ("groupChecked[]", in_array ($group, $groups), array ('id' => "group_$group", 'value' => $group)) . '&nbsp;' . Xml::tags ('label', array('for' => "group_$group"), $groupName);
			$form .= Xml::element ('br');
		}
		$form .= Xml::closeElement ('p');
		$form .= Xml::tags ('p', NULL, Xml::inputLabel (wfMsg ('userrights-reason'), 'wpReason', 'wpReason', 60));
		$form .= Xml::submitButton (wfMsg ('advanceduserrights-submit'), array ('name' => 'wpSubmit'));
		$form .= Xml::closeElement ('form');
		
		if ($wgUser->isAllowed ('checkip'))
		{
		}
		
		$wgOut->addHTML ($form);
	}
	
	/************************************************[ UTILITY FUNCTIONS ]************************************************************/
	/**
	 * Get a list of groups the user is a member of
	 *
	 * @param $user_id The ID of the user to get the list for
	 * @return An array with the groups
	 */
	private static function getGroups ($user_id)
	{
		$user = User::newFromId ($user_id);
		return $user->getGroups();
	}
	
	/**
	 * Format a link to a group description page
	 *
	 * @param string $group
	 * @return string
	 */	
	private static function buildGroupLink( $group ) {
		static $cache = array();
		if( !isset( $cache[$group] ) )
			$cache[$group] = User::makeGroupLinkHtml( $group, User::getGroupMember( $group ) );
		return $cache[$group];
	}
	
	/**
	 * Creates an array to be used with the Xml-functions, for sortable headers.
	 *
	 * @param $sortField The field for which to create the sort-link.
	 * @returns Array: An array with the href and class information.
	 */
	private function buildSortLink ($sortField)
	{
		global $wgScript, $wgUser;
		$link = $this->getTitle()->getLocalURL('sort=' . $sortField);
		
		if ($this->mSort == $sortField)
		{
			// If we are already sorting by this field, flip the direction
			$sortOrder = ($this->mOrder == 'DESC') ? 'ASC' : 'DESC';
			// And include it in the link, which will not be necessary if we are not yet sorting by this field, since we will then get the default direction.
			$link .= '&order=' . strtolower ($sortOrder);
			$linkClass = ($this->mOrder == 'DESC') ? 'sortDesc' : 'sortAsc';
		}
		else
			$linkClass = 'sortNone';
		
		if ($this->mOffset != 0)
			$link .= '&offset=' . $this->mOffset;
		
		if ($this->mLimit != $wgUser->getIntOption ('rclimit', $this->mDefaultLimit))
			$link .= '&limit=' . $this->mLimit;
		
		return array ('href' => $link, 'class' => $linkClass);
	}
	
	/**
	 * Creates the XHTML for a given navigation link.
	 *
	 * @param $navAction The navigational action for which to create the link.
	 * @param $offset Create an offset link.
	 * @returns A string of XHTML with the link.
	 */
	private function buildNavLink ($navAction = '', $limit = false)
	{
		global $wgScript, $wgUser;
		
		// Return just the label on a disabled link
		if (!empty ($navAction) && $this->navLinks[$navAction] === false)
			return wfMsg ("nav-$navAction", $this->mLimit);

		$query = array ();
		if ($this->mSort != $this->mDefaultSort)
			$query[] = 'sort=' . $this->mSort;
		if ($this->mOrder != $this->mDefaultOrder)
			$query[] = 'order=' . strtolower ($this->mOrder);
		if (!$limit && ($this->mLimit != $wgUser->getIntOption ('rclimit', $this->mDefaultLimit)))
			$query[] = 'limit=' . $this->mLimit;
		elseif ($limit !== false)
			$query[] = 'limit=' . intval ($limit);
		if (!empty ($navAction))
			$query[] = $this->navLinks[$navAction];
		else
			$query[] = 'offset=' . $this->mOffset;
		$link = $this->getTitle()->getLocalURL(implode ('&', $query));
		
		if (!empty ($navAction))
			return Xml::element ('a', array ('href' => $link, 'rel' => $navAction), wfMsg ("nav-$navAction", $this->mLimit));
		else
			return Xml::element ('a', array ('href' => $link), $limit);
	}
	
	private function buildLimitLink ()
	{
		$limits = array ();
		foreach ($this->mLimitsShown as $limit)
			$limits[] = $this->buildNavLink ('', $limit);
		
		return implode ('&nbsp;|&nbsp;', $limits);
	}
	
	/** 
	 * Adds a specified user to a specified group.
	 *
	 * @todo Check for existing membership.
	 * @param $user_id The ID of the user.
	 * @param $group_id The ID of the group.
	 */
	private static function addUserToGroup ($user_id, $group_id)
	{
		$user = User::newFromId ($user_id);
		$group = User::getGroupName ($group_id);
		
		$user->addGroup ($group_id);
	}
	
	/**
	 * Gets the path to an image based on user preferences and falls back to default.
	 *
	 * @param $imageName The name of the image to load
	 * @returns The path to the image
	 */
	private static function getImagePath ($imageName)
	{
		global $IP, $wgDefaultSkin, $wgOut, $wgScriptPath, $wgStyleDirectory, $wgStylePath, $wgUser;
		
		// Check user style first
		$searchPath = $wgStyleDirectory . '/' . (!empty ($wgUser->getSkin()->stylename) ? $wgUser->getSkin()->stylename : $wgDefaultSkin) . '/images/' . $imageName;
		if (file_exists ($searchPath))
			return $wgStylePath . '/' . $wgUser->getSkin()->stylename . '/images/' . $imageName;
		
		// The check the common style path
		$searchPath = $wgStyleDirectory . '/common/images/' . $imageName;
		if (file_exists ($searchPath))
			return $wgStylePath . '/common/images/' . $imageName;
		
		// Fall back to default images supplied
		$searchPath = dirname (__FILE__) . '/images/' . $imageName;
		$imagePath = str_replace ($IP, $wgScriptPath, $searchPath);
		if (file_exists ($searchPath))
			return $imagePath;
	}
}
?>