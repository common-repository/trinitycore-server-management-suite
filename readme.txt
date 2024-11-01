=== Trinity Management Suite ===

Contributors: kotori
Donate link: http://deimos.hopto.org/dev/trinitymanagementsuite/
Tags: server management, private, wow, trinity, trinitycore
Requires at least: 3.1
Tested up to: 3.4.1
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The TrinityCore Management Suite provides all the functions necessary to manage your TrinityCore server.


== Description ==

The TrinityCore Management Suite provides all the functions necessary to hook Wordpress into your TrinityCore private server.
This plugin provides the following functions:

* User registration.
* User management (add users, remove users, change passwords).
* Displays the connection status of your servers via a 'up/down' widget.
* Display listings of current players.
* Provides a listing of the active player's inventory.
* Multi-realm support NOT yet added, this will be implemented soon.


= Feedback =

* I am open for your suggestions and feedback - Thank you for using or trying out one of my plugins!
* Drop me a line [@kotori83](http://twitter.com/#!/kotori83) on Twitter


== Installation == 

1. Upload the entire `trinitymanagementsuite` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Tweak the plugin from the settings page.


== Frequently Asked Questions ==

= Is this plugin stable? =

Currently, NO, I would not consider this plugin stable. However, please note that I am rapidly adding new features and stabilizing existing code. Working features
currently include:
* Active Player Listing
* Password Reset
* Realm Status

= Does this plugin work with newest WP version and also older versions? =

Yes, this plugin works fine with WordPress 3.3! For best results, this plugin should be used with the TwentyEleven theme.
However this is certainly not a requirements.

= How are new resources being added to the admin bar? =

Just drop me a note on [my Twitter @kotori83](http://twitter.com/kotori83) or via my contact page and I'll add the link if it is useful for admins.

= Why, if I turn on email delivery upon registration, the password that is sent to the new user is different from what they initially setup? =

This is currently a known issue. Due to the way Wordpress functions, it attempts to generate a password AFTER the registration process, we've tweaked this
to not occur at all, and to not send an email. I suggest you keep it this way to prevent breaking the plugin. This will also help prevent confusion when your
users receive a password that will not function.

= Can I provide a new translation? =

Yes, It will be very gratefully received. 

= Can I update a translation? =

Yes, It will be very gratefully received. 


== Screenshots ==

1. This screen shot gives you a view of the Realm Status widget that is built into this plugin. This widget provides you with an 
incredibly easy to use Realm tracker. You can add as many as you like to your sidebars, each monitoring their own realm.


== Changelog ==

= 0.2 =

* Fixed registration page.
* Switched captcha lib from Securimage to reCaptcha.
* Fixed a few SQL injection issues.
 
= 0.1 =

* Initial release. Lots of new features being added constantly.
* Added support for SecurImage Captchas on registration form.
* Added support for password creation upon registration into the TrinityCore database.


== Upgrade Notice ==

= 0.2 =

Nothing new to report.

= 0.1 =

Just released into the wild.


== Additional Info ==

**Idea Behind / Philosophy:** Just a little leightweight plugin for all the TrinityCore server managers out there to make their daily admin life a bit easier. I'll try to add more plugin/theme support if it makes some sense. So stay tuned :).
 

== Credits ==

* [kotori](http://greenskin.hopto.org/) [@kotori83](http://twitter.com/#!/kotori83) for main plugin development.
* Also thanks to the TrinityCore team to building a sweet private server platform. [TrinityCore](http://www.trinitycore.org/).
