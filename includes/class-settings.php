<?php
/**
 * Settings resolution.
 *
 * The schema builders consume a plain nested array. Where that array comes from
 * is deliberately not their concern: ACF PRO is the editing UI, not the only
 * possible source of truth. Every save through ACF is mirrored into a standalone
 * option, so the frontend keeps emitting the last known-good values even if ACF
 * is later deactivated or removed.
 */

namespace Selectrum\OrganizationSchema;

defined( 'ABSPATH' ) || exit;

final class Settings {

	/**
	 * Option holding the mirror of the values last saved through ACF.
	 */
	const OPTION = 'selectrum_os_settings';

	/**
	 * Prefix carried by every field in this plugin's field group.
	 */
	const FIELD_PREFIX = 'selectrum_os_';

	/**
	 * Hook the mirror.
	 *
	 * Priority 20 so it runs after ACF has written the values at priority 10.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'acf/save_post', array( __CLASS__, 'mirror' ), 20 );
	}

	/**
	 * Resolve the settings array for the current request.
	 *
	 * @return array
	 */
	public static function all() {
		/**
		 * Filters the resolved schema settings.
		 *
		 * Lets a site supply or override values from code, with or without a
		 * settings page. Keys match the ACF field names.
		 *
		 * @param array $settings Resolved settings, keyed by field name.
		 */
		$settings = apply_filters( 'selectrum_os_settings', self::read() );

		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Whether a mirror of previously saved values exists.
	 *
	 * @return bool
	 */
	public static function has_mirror() {
		$mirrored = get_option( self::OPTION, array() );

		return is_array( $mirrored ) && ! empty( $mirrored );
	}

	/**
	 * Read the settings from the best available source.
	 *
	 * ACF is preferred while it is active, because it is the only source that
	 * reflects edits made in the current request.
	 *
	 * @return array
	 */
	private static function read() {
		if ( Plugin::has_acf_pro() && function_exists( 'get_fields' ) ) {
			// ACF options-page values are global and retrieved with the
			// "option" post ID.
			$fields = get_fields( 'option' );

			if ( is_array( $fields ) ) {
				return self::only_own_fields( $fields );
			}
		}

		$mirrored = get_option( self::OPTION, array() );

		return is_array( $mirrored ) ? $mirrored : array();
	}

	/**
	 * Mirror the saved ACF values into a standalone option.
	 *
	 * @param mixed $post_id ACF post ID being saved.
	 * @return void
	 */
	public static function mirror( $post_id ) {
		// ACF passes the options-page post ID, which is "options" by default.
		if ( 'options' !== $post_id && 'option' !== $post_id ) {
			return;
		}

		if ( ! function_exists( 'get_fields' ) ) {
			return;
		}

		$fields = get_fields( 'option' );

		if ( ! is_array( $fields ) ) {
			return;
		}

		$own = self::only_own_fields( $fields );

		// An empty set means the save came from some other options page, so the
		// existing mirror is more trustworthy than this result.
		if ( empty( $own ) ) {
			return;
		}

		// Autoloaded because the frontend reads it on every request whenever ACF
		// is absent.
		update_option( self::OPTION, $own, true );
	}

	/**
	 * Reduce a full options-page field set to this plugin's own fields.
	 *
	 * get_fields( 'option' ) returns every field from every options page on the
	 * site. Without this filter, an unrelated plugin's options field could land
	 * in the mirror, and a field named like one of ours could feed a stray value
	 * into the schema builders.
	 *
	 * @param array $fields Full options-page field set.
	 * @return array
	 */
	private static function only_own_fields( array $fields ) {
		$own = array();

		foreach ( $fields as $name => $value ) {
			if ( 0 === strpos( (string) $name, self::FIELD_PREFIX ) ) {
				$own[ $name ] = $value;
			}
		}

		return $own;
	}
}
