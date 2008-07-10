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

class UserInformationPage extends SpecialPage
{
	public function __construct ()
	{
		wfLoadExtensionMessages ('UserInformation');
		parent::__construct ('UserInformation', 'userinfo', false);
	}
		
	public function execute ($par)
	{
		global $auEnableCheckIP, $wgLang, $wgOut, $wgRequest, $wgUser;

		if( !$this->userCanExecute ($wgUser))
			return $this->displayRestrictionError ();
		
		$this->outputHeader ();
		
		$this->setHeaders ();
		
		$this->db = wfGetDB (DB_SLAVE);
		
		if ($wgRequest->getCheck ('setup'))
			return $this->setup ();
		
		if (empty ($par))
			return $this->displayRestrictionError ();
				
		list (/* $prefix */, $user_name) = explode (':', $par, 2);
		
		$wgOut->setPageTitle (wfMsg ('userinfo-viewing', $user_name));
		
		$user = $this->db->selectRow ('user', '*', array ('user_name' => $user_name), __METHOD__);
		
		// Status messages
		if (empty ($user->user_email))
			$user->user_email = wfMsg ('userinfo-noemailset');
		
		if (empty ($user->user_email_authenticated))
			$user->emailConfirmed = wfMsg ('userinfo-noemailconfirmed');
		else
			$user->emailConfirmed = wfMsg ('userinfo-hasemailconfirmed', $wgLang->timeanddate ($user->user_email_authenticated));
		
		$table  = Xml::openElement ('table');
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', array ('width' => '200px'), wfMsg ('username')) . Xml::element ('td', NULL, $user->user_name));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('userinfo-regdate')) .        Xml::element ('td', NULL, $wgLang->timeanddate ($user->user_registration)));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('youremail')) .                      Xml::element ('td', NULL, $user->user_email));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('userinfo-emailconfirmed')) . Xml::element ('td', NULL, $user->emailConfirmed));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('yourrealname')) .                   Xml::element ('td', NULL, $user->user_real_name));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('userrights-groupsmember')) .        Xml::tags ('td', NULL, self::getGroupsList($user->user_id)));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('userinfo-editcount')) .      Xml::element ('td', NULL, $wgLang->formatNum ($user->user_editcount)));
		$table .= Xml::tags ('tr', NULL, Xml::element ('td', NULL, wfMsg ('userinfo-lasttouch')) .      Xml::element ('td', NULL, $wgLang->timeanddate ($user->user_touched)));
		$table .= Xml::closeElement ('table');
		
		$wgOut->addHTML ($table);
		
		if ($auEnableCheckIP && $wgUser->isAllowed ('checkip'))
			$this->getKnownAddresses ();
	}
	
	private function getKnownAddresses ()
	{
		global $wgOut;
		
		$wgOut->addHTML (Xml::element ('h2', NULL, wfMsg ('userinfo-iplist')));
		
		#if (!$this->db->tableExists ('user_checkip'))
			/*return*/ $wgOut->addWikiText (wfMsg ('userinfo-nosetup', SpecialPage::getTitle()->getFullURL ('setup=1')));
		
		$res = $this->db->select ('user_checkip', array ('user_ip', 'cu_touched'), array ('user_id' => $user->user_id), __METHOD__);
		
		if ($this->db->numRows ($res) == 0)
			return $wgOut->addHTML (wfMsg ('userinfo-noipsfound'));
		
		$list  = Xml::openElement ('table', array ('style' => 'width: 100%; text-align: left;'));
		$list .= Xml::openElement ('tr');
		$list .= Xml::element ('th', array ('width' => '33%'), wfMsg ('userinfo-ipaddress'));
		$list .= Xml::element ('th', NULL, wfMsg ('userinfo-lastseen'));
		$list .= Xml::closeElement ('tr');
		while ($row = $this->db->fetchObject ($res))
		{
			$list .= Xml::openElement ('tr');
			$list .= Xml::element ('td', NULL, $row->user_ip);
			$list .= Xml::element ('td', NULL, $wgLang->timeanddate ($row->cu_touched));
			$list .= Xml::closeElement ('tr');
		}
		$list .= Xml::closeElement ('table');
		
		$wgOut->addHTML ($list);
	}
	
	private function setup ()
	{
		global $wgOut, $wgRequest;
		
		$wgOut->setPageTitle (wfMsg ('userinfo-setup'));
		
		if ($wgRequest->wasPosted ())
		{
			if ($this->db->tableExists ('user_checkip'))
				$wgOut->addHTML ('<p>' . wfMsg ('userinfo-setup-tableexists') . '</p>');
			else
			{
				$tableName = $this->db->tableName ('user_checkip');
				$sql = "CREATE TABLE $tableName (
				            cu_id int(7) unsigned NOT NULL auto_increment,
							user_id int(5) unsigned NOT NULL default '0',
							user_ip varchar(255) NOT NULL default '',
							user_ip_hex varchar(255) default NULL,
							user_xff varchar(255) NOT NULL default '',
							user_xff_hex varchar(255) default NULL,
							cu_touched varchar(14) NOT NULL default '',
							PRIMARY KEY (cu_id),
							UNIQUE KEY unique_location (user_id, user_ip_hex)
						);";
				$this->db->query ($sql, __METHOD__);
				$wgOut->addHTML ('<p>' . wfMsg ('userinfo-setup-tablecreated') . '</p>');
			}
			$wgOut->addReturnTo (SpecialPage::getTitle());
		}
		else
		{
			$wgOut->addHTML ('<p>' . wfMsg ('userinfo-setup-explain') . '</p>');
			$wgOut->addHTML ('<form method="post"><input type="submit" value="' . wfMsg ('userinfo-setup-button') . '" /></form>');
		}
	}
	
	/**
	 * Builds a comma-separated list of groups, with optional link to edit groups.
	 *
	 * @param $user_id The ID of the user for which to build the list.
	 * @param $with_edit Wether or not to include the edit groups link.
	 * @return String with HTML for the list.
	 */
	private static function getGroupsList ($user_id, $with_edit = true)
	{
		static $cache = array();
		
		$user = User::newFromId ($user_id);
		$groups = $user->getGroups();
		
		$list = array();
		
		foreach ($groups as $group)
		{
			if( !isset( $cache[$group] ) )
				$cache[$group] = User::makeGroupLinkHtml( $group, User::getGroupMember( $group ) );
			$list[] = $cache[$group];
		}
		
		if ($with_edit)
		{
			$form = ' [<a href="' . SpecialPage::getTitleFor ('AdvancedUserrights', Title::makeTitle (NS_USER, $user->getName()))->getLocalURL() . '">' . wfMsg ('userrights-editusergroup') . '</a>]';
			
			return implode (', ', $list) . $form;
		}
		else
			return implode (', ', $list);
	}
}
