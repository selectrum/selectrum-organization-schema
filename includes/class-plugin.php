<?php
/**
 * Plugin bootstrap: options page, ACF registration, Yoast integration.
 */

namespace Selectrum\OrganizationSchema;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	public static function init() {
		Settings::init();

		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'register_yoast_schema_filters' ) );
		add_action( 'acf/init', array( __CLASS__, 'register_acf' ) );
		add_action( 'admin_notices', array( __CLASS__, 'dependency_notice' ) );
		add_action(
			'after_plugin_row_' . plugin_basename( SELECTRUM_OS_PLUGIN_FILE ),
			array( __CLASS__, 'plugin_row_notice' ),
			10,
			3
		);
		add_action( 'wp_head', __NAMESPACE__ . '\output_schema', 20 );
	}

	/**
	 * Refuse activation when ACF PRO is missing.
	 *
	 * Runs on the activation hook only, so a site that loses ACF PRO after the
	 * fact stays active and keeps serving its mirrored values. Reactivating it
	 * in that state is what this blocks.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( self::has_acf_pro() ) {
			return;
		}

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// active_plugins is written before this hook fires, so the plugin has to
		// be taken back out explicitly. Silent, because its own deactivation
		// hooks were never meant to run.
		deactivate_plugins( plugin_basename( SELECTRUM_OS_PLUGIN_FILE ), true );

		wp_die(
			sprintf(
				'<h1>%1$s</h1><p>%2$s</p>',
				esc_html__( 'Advanced Custom Fields PRO is required', 'selectrum-organization-schema' ),
				esc_html__( 'Selectrum Organization Schema was not activated because Advanced Custom Fields PRO is not active on this site. Install and activate ACF PRO, then activate this plugin again.', 'selectrum-organization-schema' )
			),
			esc_html__( 'Plugin activation error', 'selectrum-organization-schema' ),
			array(
				'back_link' => true,
			)
		);
	}

	public static function load_textdomain() {
		load_plugin_textdomain(
			'selectrum-organization-schema',
			false,
			dirname( plugin_basename( SELECTRUM_OS_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Whether ACF PRO is available.
	 *
	 * Detected through the two functions this plugin actually calls rather than
	 * a version marker. acf_add_options_page() is PRO-only, so its presence is
	 * both the PRO check and the capability check, and unlike an internal class
	 * or constant name it cannot go stale across ACF major versions.
	 *
	 * @return bool
	 */
	public static function has_acf_pro() {
		return function_exists( 'acf_add_options_page' )
			&& function_exists( 'acf_add_local_field_group' );
	}

	public static function has_yoast_seo() {
		return defined( 'WPSEO_VERSION' )
			|| class_exists( 'WPSEO_Options' )
			|| class_exists( 'Yoast\WP\SEO\Main' );
	}

	/**
	 * Replace only Yoast's Organization piece, leaving the rest of its graph.
	 *
	 * Yoast owns the page-level entities (WebSite, WebPage, Article, Person,
	 * BreadcrumbList) whenever it is active, so suppressing its whole graph
	 * would delete far more than this plugin puts back.
	 *
	 * Because this plugin emits its Organization under the same
	 * "#organization" identifier Yoast uses, Yoast's own publisher and author
	 * references keep resolving without any rewriting.
	 *
	 * @return void
	 */
	public static function register_yoast_schema_filters() {
		if ( ! self::has_yoast_seo() ) {
			return;
		}

		add_filter( 'wpseo_schema_graph_pieces', array( __CLASS__, 'disable_yoast_organization_schema_piece' ), 11, 2 );
	}

	/**
	 * Drop Yoast's Organization piece from its schema graph.
	 *
	 * Matched on the class short name rather than a fully-qualified name,
	 * because Yoast has moved these generators between namespaces across major
	 * versions.
	 *
	 * @param mixed $pieces  Yoast graph pieces.
	 * @param mixed $context Yoast meta tags context.
	 * @return mixed
	 */
	public static function disable_yoast_organization_schema_piece( $pieces, $context ) {
		if ( ! is_array( $pieces ) ) {
			return $pieces;
		}

		return array_values(
			array_filter(
				$pieces,
				static function ( $piece ) {
					if ( ! is_object( $piece ) ) {
						return true;
					}

					$class = explode( '\\', get_class( $piece ) );

					return 'Organization' !== end( $class );
				}
			)
		);
	}

	public static function register_acf() {
		if ( ! self::has_acf_pro() ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Organization Schema', 'selectrum-organization-schema' ),
				'menu_title' => __( 'Organization Schema', 'selectrum-organization-schema' ),
				'menu_slug'  => 'selectrum-os-schema',
				'capability' => 'manage_options',
				'redirect'   => false,
				'position'   => 80,
				'icon_url'   => 'dashicons-networking',
				'autoload'   => true,
			)
		);

		$file = SELECTRUM_OS_PLUGIN_DIR . 'acf-field-group.json';

		if ( ! is_readable( $file ) ) {
			return;
		}

		$groups = json_decode( file_get_contents( $file ), true );

		if ( ! is_array( $groups ) ) {
			return;
		}

		foreach ( $groups as $group ) {
			if ( is_array( $group ) ) {
				acf_add_local_field_group( $group );
			}
		}
	}

	/**
	 * Screen-wide dependency warning.
	 *
	 * @return void
	 */
	public static function dependency_notice() {
		if ( self::has_acf_pro() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// The Plugins screen gets a row-level notice attached to this plugin
		// instead, which is more precise than a screen-wide banner.
		$screen = get_current_screen();

		if ( $screen && 'plugins' === $screen->id ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html( self::dependency_message() )
		);
	}

	/**
	 * Dependency warning attached to this plugin's row on the Plugins screen.
	 *
	 * @param string $plugin_file Plugin file, relative to the plugins directory.
	 * @param array  $plugin_data Plugin header data.
	 * @param string $status      Current list-table status filter.
	 * @return void
	 */
	public static function plugin_row_notice( $plugin_file, $plugin_data, $status ) {
		if ( self::has_acf_pro() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Matches the Plugins table, which carries an extra column once
		// auto-updates are available.
		$columns = 4;

		if ( function_exists( '_get_list_table' ) ) {
			$table = _get_list_table( 'WP_Plugins_List_Table' );

			if ( $table ) {
				$columns = $table->get_column_count();
			}
		}

		printf(
			'<tr class="plugin-update-tr active"><td colspan="%1$d" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>%2$s</p></div></td></tr>',
			(int) $columns,
			esc_html( self::dependency_message() )
		);
	}

	/**
	 * Build the dependency warning text.
	 *
	 * @return string
	 */
	private static function dependency_message() {
		$message = __(
			'Selectrum Organization Schema requires Advanced Custom Fields PRO. The Organization Schema settings page is unavailable until ACF PRO is installed and activated.',
			'selectrum-organization-schema'
		);

		if ( Settings::has_mirror() ) {
			$message .= ' ' . __(
				'The values saved while ACF PRO was active are still being output on the frontend.',
				'selectrum-organization-schema'
			);
		}

		return $message;
	}
}
