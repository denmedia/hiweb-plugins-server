=== hiWeb Plugins Server ===
Contributors: Den Media
Donate link:
Tags: admin plugins, admin server, admin client, admin repository, easy server, easy download, easy plugin, easy admin, free, free plugin, free admin, free server, admin, automatic download, automatic plugin, plugins, list plugins, list, share plugin, sharing plugin, share server, sharing server, repository, server, develop, scripts, download, multisite, multi site, sites, manager
Requires at least: 4.1
Tested up to: 4.3
Stable tag: 4.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

If you are creating multiple sites, and every time you need to download the same plug-ins, including paid versions - then this plugin is for you!

== Description ==

If you are creating multiple sites on WordPress, and every time you need to download the same plug-ins, including paid versions - then this plugin is for you!
This plugin allows you to organize storage of archived plugins current site.


= How to organize the archive server plug-ins for their other sites: =

= Setting up the client sites for download plugins from you'r own server =

 1. Go to `"Settings → hiWeb Plugins Server"` [(screenshot)](https://s.w.org/plugins/hiweb-plugins-server/screenshot-1.png?r=1502472)
 1.1. Enter the address of a site on WordPress, where you installed the plugin `"hiWeb Plugins Server"`, working in the "server" mod.
 1.2. Click on the `"Update"` button.
 1.3. If the server is running and you are connected to it, the left will see a message about the status of the connection.
 1. After a successful connection, you can go to the list of remote plugins, where you can download them on the current site. Go to `"Plugins → hiWeb Remote plugins"` [(screenshot)](https://s.w.org/plugins/hiweb-plugins-server/screenshot-2.png?r=1502472)


= Create a archive server =

 1. To start the server, go to `"Settings → hiWeb Plugins Server"` and click on the button `"Start Local Server"`. [(screenshot)](https://s.w.org/plugins/hiweb-plugins-server/screenshot-3.png?r=1502472)
 1. After starting the server, go to "Plugins Server" in the admin panel. Here you can place on your server with the required plug-ins to client sites. [(screenshot)](https://s.w.org/plugins/hiweb-plugins-server/screenshot-4.png?r=1502472)
 1. Done! Now all the site with WordPress plugin "hiWeb Plugins Server" in "client" mode, connected to the server will be able to download featured plugins.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `hiweb-plugins-server` to the `/wp-content/plugins/` directory
1. Activate the plugins through the `hiWeb Plugins Server` menu in WordPress
1. If your website is designed to distribute plug-ins (server mode), use the following instructions:
   3.1. To start the server, go to `"Settings → hiWeb Plugins Server"` and click on the button `"Start Local Server"`.
   3.2. After starting the server, go to "Plugins Server" in the admin panel. Here you can place on your server with the required plug-ins to client sites.
   3.3. Done! Now all the site with WordPress plugin "hiWeb Plugins Server" in "client" mode, connected to the server will be able to download featured plugins. 
1. If your site is designed for plug-ins (client mode), use the following instructions:
   4.1. Go to `"Settings → hiWeb Plugins Server"`
   4.2. Enter the address of a site on WordPress, where you installed the plugin `"hiWeb Plugins Server"`, working in the "server" mod.
   4.3. Click on the `"Update"` button.
   4.4. If the server is running and you are connected to it, the left will see a message about the status of the connection.
   4.5. After a successful connection, you can go to the list of remote plugins, where you can download them on the current site. Go to `"Plugins → hiWeb Remote plugins"`

== Screenshots ==

1. Client Site settings
1. Client site plugins list for download to current site and activate them
1. Start server
1. Server plugins list for host them

== Changelog ==

= 2.1.0.2 =
Work on the bugs, Make Readme.txt file.

= 2.0.0.0 =
ReMake Plugins Server