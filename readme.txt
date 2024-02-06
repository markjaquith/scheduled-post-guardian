=== Scheduled Post Guardian ===

Contributors: markjaquith
Donate link: http://txfx.net/wordpress-plugins/donate
Tags: future posts, scheduled posts, cron
Requires at least: 3.8
Tested up to: 6.4
Stable tag: 1.1.4

Watches over scheduled posts, and makes sure they don't miss their scheduled time

== Description ==

Some WordPress sites have problems with scheduled posts, where they don't get published at the right time. Instead they appear as "missed schedule". This plugin monitors scheduled posts, and makes sure that doesn't happen.

== Installation ==

1. Upload the `scheduled-post-guardian` folder to your `/wp-content/plugins/` directory
2. Activate the "Scheduled Post Guardian" plugin in your WordPress administration interface
3. You're done! The plugin has no UI and it'll start working right away.

== Frequently Asked Questions ==

= How does it work? =

On "shutdown" (after WP finishes a request), it checks a timer. If it has been 3 minutes since it last checked, it looks at all the scheduled posts, and runs them through a WordPress core function that either publishes them (if they should have gone out already), or re-schedules them, if their schedule has somehow gone away.

= My post missed its schedule anyway! =

Normally, the plugin checks every 3 minutes. So it's possible the post might go out a few minutes late. I hope this isn't the end of the world. It's a tradeoff between this and checking all the time. Additionally, if a check determines that there are currently no scheduled posts, it won't look again for 15 minutes. Most people schedule posts more than 15 minutes in the future.

= Can I change those times? =

Yes. There are filters: `scheduled_post_guardian_delay_minutes` and `scheduled_post_guardian_stretch_delay_minutes`. Return a different number than 3 and 15 for those if you like.

== Changelog ==

= 1.1.4 =

Bump WordPress supported version

= 1.1.0 =

* Cleaned up code, bumped PHP version requirement.

= 1.0.1 =

* Fix mistake in first version

= 1.0 =

* Initial version, commissioned by OffbeatBride.com
