
# WordPress Emergency Toolkit

*WordPress Emergency Toolkit* is a tool that can be used for a quick recovery of your WordPress site. If some plugin/theme conflict results in your site (or some of its functionality) becoming inaccessible, you can use this application to get it started quickly before the developer could resolve the underlying problem.

**Requirements:**
- PHP: 5.6+
- MySQL
- Apache, Nginx or similar webserver software

## How to use

Just upload the archive into the folder of your WordPress installation and extract it there. So, if your WordPress is located in http://example.com/folder , you can just access it by following the link -  http://example.com/folder/wp-toolkit

When there, you will be greeted with a login page. The default password is **jC5OxwVGXISk**, but you can easily change it by adding the following on the next line after PHP opening tag (<?php) in the api.php file:

```
define('TOOLKIT_PASS', 'new-password-here');
```

*Note that if you get the password wrong after 4 times, the script will get self-deleted.*

## Overview

Below you will find the overview of the functionality and menus of WordPress Emergency Toolkit.

### Main

At the top of the page, you will find two buttons:

**Delete WordPress Toolkit** will remove the folder of the WordPress Toolkit and the *wp-toolkit.zip* archive in the WordPress application root (should it be there). Make sure to *always* press it after your work in the script is finished. This script is designed for a quick restoration of an access to the site (or some parts of it), letting it remain on the server is inherently risky.

**Flush Temporary Files** will remove the database access details, information about plugins, themes, etc. cached by WordPress Toolkit.

Below that, you will be able to edit some basic WordPress information: 

**Site URL** and **Home** are the links to your WordPress site. **Site URL** is the link the visitors type in their address bar to access the site and **Home** is the location of the WordPress core.
**Title** is the main name of your WordPress site and **Description** is its subtitle (which can be its motto or a sentence about the site's purpose). 

[Screenshot](https://i.imgur.com/kuJJbNm.png)

### Plugins

Here you will find two columns: **Active Plugins** and **Inactive Plugins**. 

**Active Plugins** are the plugins that are set as enabled in the site's database. Press ***Disable*** to deactivate selected plugins and ***Remove*** to deactivate *and* delete them from your file system.
*Note that if the plugin is enabled, but no folder for it exists in the plugins directory, you will see the exclamation mark in front of its name.*

**Inactive Plugins** are the plugins which directories exist in the *plugins* directory, but are not enabled in the database. Press ***Enable*** to activate the selected plugins and ***Remove*** to delete them from your file system.

**Revert** button will undo all of the changes and set the plugins to the way they were when you first opened WordPress Toolkit on this domain or when you flushed temporary files.
**Undo** button will undo the last action made in regards to plugins (unless that action was *Revert* or *Undo*).

[Screenshot](https://imgur.com/6ZlrA5X)

### Themes

At the top of the page you will find the ***Template*** and ***Stylesheet*** rows. These are the names used in WordPress database to define a theme being used. Usually, if you use a theme, the same value should be set in both ***Template*** and ***Stylesheet***, the exception here being the child themes - in their case, the main theme is set in ***Template*** and the child theme is set in ***Stylesheet***.

Below, you will find the list of all available themes inside of the *themes* directory of WordPress. To set the selected theme as both Template and Stylesheet (which you should do in most cases), press ***Change Theme***. To set it as *just* Template, press ***Set as Template***. To set it as *just* Stylesheet, press ***Set as Stylesheet***. To remove the theme from your file system, press ***Remove***. 

Note that the ***Default Theme*** option is different from the other ones. There isn't actually a theme called 'Default Theme' in most cases, it's just an option to reupload a default theme for your version of WordPress. Press ***Reupload*** to download it to your *themes* directory or press ***Reupload and set as a theme*** to download it *and* set it as a theme at once.
 
**Revert** button will undo all of the changes and set the themes to the way they were when you first opened WordPress Toolkit on this domain or when you flushed temporary files.
**Undo** button will undo the last action made in regards to themes (unless that action was *Revert* or *Undo*).

[Screenshot](https://i.imgur.com/W6vFUgc.png)

### Users

In this menu you will see three sub-tabs:

**User List** will show you the list of all available WordPress users. You can type the name of the user you're looking in the *Search User* input field to locate it faster.
To edit a specific user, select it in the list.

After doing so, the user information will appear on the right. 
Here you will be able to update the user level of this user by choosing the desired level in the *Set User Level* drop-down and clicking *Change Level* button.
To change the password, type it in the *New Password here* input field and press *Reset*.
To remove this user, press the *Delete User* button.

[Screenshot](https://i.imgur.com/gTTVs5j.png)

**Create New User** is a menu that allows you to set up new users. The ***username*** and ***password*** fields are required, with the ability to set up a *nice name* and an *email address* for a user below. Make sure to select the needed user level in the drop-down below before clicking the ***Create*** button. 

[Screenshot](https://i.imgur.com/FbuXIKp.png)

***User Roles*** menu will show you an overview of all available user roles in this WordPress installation. Press *+* in front of the role name to expand the list of its privileges.   Pressing ***Restore Default Roles*** will result in default user roles being applied (it will overwrite any custom roles added by plugins, for example). 

[Screenshot](https://i.imgur.com/CbBHF8g.png)

### Files

At the top, you will be able to see several buttons:

***Reupload Core Files*** will replace the core files in your application root with the files from the default installation of the same version that you have. The old files will be put inside of the *wp-oldfiles* directory  in the WordPress root folder. 
If you reuploaded the files in such a way, the ***Remove Old Files*** button will appear. Press it to remove *wp-oldfiles* folder. 
If you have a *cache* directory inside of your *wp-content* folder, you will be able to remove it by pressing ***Remove Cache Folder*** button. ***Disable Cache Folder*** button will rename *cache* into *cache.dis*, therefore preventing the cache from being used. You can enable it back by pressing ***Enable Cache Folder***.
If you have a *w3tc-config* directory (the configuration of W3 Total Cache plugin) inside of your *wp-content* folder, you will be able to remove it by pressing ***Remove Total Cache Config*** button. ***Disable Total Cache Config*** button will rename *w3tc-config* into *w3tc-config.dis*, therefore preventing the cache from being used. You can enable it back by pressing ***Enable Total Cache Config***.

Below that, you will be able to see the columns for ***Plugins*** and ***Themes***. You can try reuploading them from the WordPress repository by pressing ***Reupload Plugins/Themes*** at the bottom. Note, by default the option to reupload plugins/themes of the same version as you currently have is selected. You can reupload the latest versions of them by checking the *Latest* checkbox. It's also possible to remove them by pressing ***Remove***.

[Screenshot](https://i.imgur.com/fztRxkC.png)
[Screenshot](https://i.imgur.com/t7Zire6.png)

## Disclaimer

The author bears no responsibility for any loss of data or any other adverse effects of using WordPress Emergency Toolkit. 

Make sure to back up the data before using this application.
