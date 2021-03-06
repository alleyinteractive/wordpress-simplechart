<?php
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	if ( ! defined( 'SIMPLECHART_UNIT_TESTS_RUNNING' ) ) {
		define( 'SIMPLECHART_UNIT_TESTS_RUNNING', true );
	}
	require dirname( dirname( __FILE__ ) ) . '/simplechart.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
