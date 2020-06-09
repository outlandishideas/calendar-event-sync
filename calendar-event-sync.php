<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the
 * plugin admin area. This file also includes all of the dependencies used by
 * the plugin, registers the activation and deactivation functions, and defines
 * a function that starts the plugin.
 *
 * @link              https://github.com/outlandishideas/calendar-event-sync
 * @since             0.1.0
 * @package           tutsplus_namespace_demo
 *
 * @wordpress-plugin
 * Plugin Name:       Calendar Event Sync
 * Plugin URI:        https://github.com/outlandishideas/calendar-event-sync
 * Description:       WordPress plugin to sync events from your calendar to WordPress
 * Version:           0.1.0
 * Author:            Outlandish Cooperative
 * Author URI:        https://outlandish.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


use Outlandish\CalendarEventSync\CalendarEventSyncPlugin;

if(getenv('APP_ENV') !== 'testing') {

    if (class_exists("WP_CLI")) {

        $requiredConstants = [
            'GOOGLE_CALENDAR_CLIENT_ID',
            'GOOGLE_CALENDAR_PROJECT_ID',
            'GOOGLE_CALENDAR_CLIENT_SECRET',
            'GOOGLE_CALENDAR_ID',
        ];

        foreach ($requiredConstants as $name) {
            if (!defined($name)) {
                return;
            }
        }


        $plugin = new CalendarEventSyncPlugin(
            GOOGLE_CALENDAR_CLIENT_ID,
            GOOGLE_CALENDAR_CLIENT_SECRET,
            GOOGLE_CALENDAR_PROJECT_ID,
            GOOGLE_CALENDAR_ID
        );

        $plugin->init();
    }
}
