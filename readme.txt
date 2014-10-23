=== Uptime Robot ===
Contributors: scottnelle
Tags: uptime, monitoring, server
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 0.1.3
License: GPLv2 or later

A dashboard widget for monitoring your uptimerobot.com log messages

== Description ==

Uptime Robot uses the uptimerobot.com API to display log messages relating to a site's uptime. You must have a free uptimerobot.com account to get any use from this plugin.

== Installation ==

1. Upload Uptime Robot to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add your API Key under Settings > Uptime Robot

== Frequently Asked Questions ==

= Where do I get an account? =

http://uptimerobot.com/

= Where do I get an API Key? =

Log in to your uptimerobot.com account and go to My Settings.

= Which API Key do I use? =

You can use either your Main API Key to pull in all Monitors, or a Monitor-Specific API Key to pull in data for a single monitor.

== Screenshots ==

1. Dashboard Widget

== Changelog ==
= 0.1.3 =
* Updating dashboard widget with more subdued colors

= 0.1.2 =
* Deleting cache data when settings are updated

= 0.1.1 =
* Data now pulls via AJAX to keep the dashboard speedy
* Prevented failed calls from caching
* Removed some debug code

= 0.1.0 =
* Initial Release
