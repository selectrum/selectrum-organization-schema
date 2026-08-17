<?php
/**
 * Plugin Name: Selectrum Organization Schema
 * Description: Adds an ACF PRO options page for Organization and LocalBusiness schema and outputs connected JSON-LD on the frontend.
 * Version: 1.0.1
 * Author: Selectrum Communications
 * Text Domain: selectrum-organization-schema
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'SOS_VERSION', get_file_data( __FILE__, array( 'Version' => 'Version' ) )['Version'] );
define( 'SOS_PLUGIN_FILE', __FILE__ );
define( 'SOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once SOS_PLUGIN_DIR . 'lib/plugin-update-checker/plugin-update-checker.php';
require_once SOS_PLUGIN_DIR . 'includes/class-sos-plugin.php';
require_once SOS_PLUGIN_DIR . 'includes/schema-output.php';

$sos_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/selectrum/selectrum-organization-schema/',
	__FILE__,
	'selectrum-organization-schema'
);
$sos_update_checker->setBranch( 'main' );

SOS_Plugin::init();
