# Advanced Userrights for MediaWiki #
**Advanced Userrights** is an extension for [MediaWiki](http://www.mediawiki.org/).

It functions as a replacement for the Special:Userrights and Special:Listusers pages in a default MediaWiki installation. It also adds extra functionality, such as extended information on users.

## Features ##
  * Sortable userlist.
  * Intuitive interface for changing group memberships.
  * Information on every registered user.
  * Optional tracking of used IP-addresses per registered user.

## Installation ##
You can either download the latest release by using the link to your right, or you can download the latest source from the [SVN](http://code.google.com/p/advanced-userrights-extension/source/checkout)-repository.

The installation procedure is as follows:
  * Place the source in `extensions/AdvancedUserrights/` under your MediaWiki root directory.
  * If you want to add links to the added functionality on every user page, apply the patch from your MediaWiki root directory:
```
patch -p0 extensions/AdvancedUserrights/UserInformation.patch
```
  * Add the following line to your `LocalSettings.php`:
```
require_once "$IP/extensions/AdvancedUserrights/AdvancedUserrights.php";
```

## Configuration settings ##
| **Variable name** | **Variable type** | **Description** |
|:------------------|:------------------|:----------------|
| `$auEnableCheckIP` | _bool_            | Enables the tracking of used IP-addresses per registered user. This option is disabled by default to prevent possible violations with the privacy policy of the wiki. |

## User rights ##
| `userinfo` | Allows a user with that right set to view information about users. |
|:-----------|:-------------------------------------------------------------------|
| `checkip`  | Allows a user to view the used IP-addresses of every registered user on the user information page. |