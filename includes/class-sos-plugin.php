<?php
defined( 'ABSPATH' ) || exit;

final class SOS_Plugin {

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'register_yoast_schema_filters' ) );
		add_action( 'acf/init', array( __CLASS__, 'register_acf' ) );
		add_action( 'admin_notices', array( __CLASS__, 'dependency_notice' ) );
		add_action( 'wp_head', 'sos_output_organization_schema', 20 );
	}

	public static function load_textdomain() {
		load_plugin_textdomain(
			'selectrum-organization-schema',
			false,
			dirname( plugin_basename( SOS_PLUGIN_FILE ) ) . '/languages'
		);
	}

	public static function has_acf_pro() {
		return function_exists( 'acf_add_options_page' )
			&& function_exists( 'acf_add_local_field_group' )
			&& class_exists( 'acf_pro' );
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
				'menu_slug'  => 'organization-schema-settings',
				'capability' => 'manage_options',
				'redirect'   => false,
				'position'   => 80,
				'icon_url'   => 'dashicons-networking',
				'autoload'   => true,
			)
		);

		$file = SOS_PLUGIN_DIR . 'acf-field-group.json';

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

	public static function dependency_notice() {
		if ( self::has_acf_pro() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__(
				'Selectrum Organization Schema requires Advanced Custom Fields PRO. Install and activate ACF PRO to register the schema settings page.',
				'selectrum-organization-schema'
			)
		);
	}
}
