<?php
/*
Plugin Name: Scheduled Post Guardian
Description: Watches over scheduled posts, so that no funny business interferes with their mission
Version: 1.1.4
Tested up to: 6.4
License: GPLv2+
Plugin URI: https://github.com/markjaquith/scheduled-post-guardian
Author: Mark Jaquith
Author URI: https://coveredweb.com/
Requires at least: 5.2
Requires PHP: 7.2

==========================================================================

Copyright 2014–2021  Mark Jaquith

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace CWS\ScheduledPostGuardian;

defined('WPINC') or die;

class Plugin {
	public static $instance;
	const OPTION = 'scheduled_post_guardian_next_run';
	const DELAY = 3; // Default is to check the future posts once every 3 minutes.
	const STRETCH = 15; // But this increases to 15 minutes if there were no scheduled posts waiting.

	protected function __construct() {
		self::$instance = $this;
	}

	public static function boot() {
		$plugin = new static;

		// This uses 'shutdown' instead of WP Cron, because some people having future post issues
		// are having them because WP Cron is not working correctly. This will allow this posts to publish
		// even if WP Cron is broken.
		\add_action('shutdown', [$plugin, 'shutdown']);

		// Run the check when edit.php is loaded.
		\add_filter('scheduled_post_guardian_run', [$plugin, 'run_on_edit_dot_php']);
	}

	public function shutdown() {
		global $wpdb;

		$next_run = \get_option(self::OPTION, false);
		$delay = \apply_filters('scheduled_post_guardian_delay_minutes', self::DELAY);

		// Should we run the check?
		$run = $next_run === false || $next_run < time();

		// Note, we hook in here to force-run for edit.php wp-admin requests.
		$run = \apply_filters('scheduled_post_guardian_run', $run);

		if (!$run) {
			return;
		}

		// Immediately bump the next_run forward (do this before any other queries to minimize race conditions).
		\update_option(self::OPTION, time() + $delay * 60);

		// You could use this filter to limit the query to a specific post type, I suppose.
		$where = \apply_filters('scheduled_post_guardian_future_post_query_where', " `post_status`='future' ");

		// Grab the scheduled posts from the database.
		$scheduled_posts = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->posts}` WHERE $where");

		if (count($scheduled_posts)) {
			// check_and_publish_future_post() will publish or fix their schedule, as appropriate.
			foreach ($scheduled_posts as $post_id) {
				\check_and_publish_future_post($post_id);
			}
		} else {
			// If there are no scheduled posts, we can probably delay the next check a bit.
			$stretch = \apply_filters('scheduled_post_guardian_stretch_delay_minutes', self::STRETCH);
			\update_option(self::OPTION, time() + $stretch * 60);
		}
	}

	public function run_on_edit_dot_php($run) {
		if (!$run && \is_admin() && function_exists('get_current_screen')) {
			$screen = \get_current_screen();

			if ($screen && 'edit' === $screen->base) {
				return true;
			}
		}

		return $run;
	}
}

Plugin::boot();
