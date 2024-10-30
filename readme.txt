=== CF7 Facebook Contactor ===
Contributors: theselby
Author URL: https://chatlive.ro/
Tags: cf7, contact form 7, Contact Form 7 Integrations, contact forms, Facebook profile information
Requires at least: 3.6
Tested up to: 4.9.6
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let the user retrieve email and name from the signed-in Facebook account, for faster contact.

== Description ==

This plugin is a bridge between your [WordPress](https://wordpress.org/) [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) forms and [Facebook profile](https://www.facebook.com/).

When a visitor presses the Facebook button, the plugin will attempt to retrieve all the info it can from a valid & connected Facebook profile.

= How to Use this Plugin =

*In Facebook*  
* Create a facebook App. See plugin for more info 

*In WordPress Admin*  
* Create or Edit the Contact Form 7 form where you want to integrate the Facebook button.  
* On the "Facebook contactor" tab, simply enable or disable this plugin.
* If enabled, in the HTML elements, add as instructed the [facebook] and [hidden your-fb] elements, also use them as you want in the Mail form.

= Important Notes = 

* You must pay very careful attention to your Facebook AppId. This plugin will have unpredictable results if the data is wrong.

== Installation ==

1. Upload `cf7-facebook-contactor` to the `/wp-content/plugins/` directory, OR `Site Admin > Plugins > New > Search > CF7 Facebook Contactor > Install`.  
2. Activate the plugin through the 'Plugins' screen in WordPress.  
3. Use the `Admin Panel > Contact form 7 > Facebook` screen to connect to `Facebook App` by entering the AppId and AppSecret.
Enjoy!

== Frequently Asked Questions ==

= Why fields get auto-completed with 'undefined' values? = 
It may happen that the user did not granted access to the app, thus the fields are undefined.


== Changelog ==
= 1.0 (26/01/2018) =
* Initial version.

