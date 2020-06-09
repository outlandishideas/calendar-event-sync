<?php
require '/code/vendor/autoload.php';
require '/wp-phpunit/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function () {
    require dirname( __FILE__ ) . '/../calendar-event-sync.php';
} );


if ( ! defined( 'WP_CLI_ROOT' ) ) {
    define( 'WP_CLI_ROOT', '/code/vendor/wp-cli/wp-cli' );
}

include WP_CLI_ROOT . '/php/utils.php';
include WP_CLI_ROOT . '/php/dispatcher.php';
include WP_CLI_ROOT . '/php/class-wp-cli.php';
include WP_CLI_ROOT . '/php/class-wp-cli-command.php';

\WP_CLI\Utils\load_dependencies();

require '/wp-phpunit/includes/bootstrap.php';