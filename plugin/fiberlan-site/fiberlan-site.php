<?php
/**
 * Plugin Name: FIBERLAN Core
 * Text Domain: fiberlan-core
 * Description: Powiązania językowe, katalog realizacji i zapytania ofertowe FIBERLAN.
 * Version: 4.6.0
 * Author: Tomasz Majewski
 * Author URI: https://majewskitomasz.pl
 * Requires at least: 6.6
 * Requires PHP: 7.4
 */
defined( 'ABSPATH' ) || exit;
define( 'FL_SITE_DIR', __DIR__ );
define( 'FL_SITE_VERSION', '4.6.0' );
require_once __DIR__ . '/inc/languages.php';
require_once __DIR__ . '/inc/projects.php';
require_once __DIR__ . '/inc/contact.php';
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/install.php';
require_once __DIR__ . '/inc/layout-refresh.php';
require_once __DIR__ . '/inc/navigation.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/schema.php';
require_once __DIR__ . '/inc/marketing.php';
require_once __DIR__ . '/inc/block-copy.php';

add_action( 'init', static function () {
	wp_register_style( 'fiberlan-block-editor', plugins_url( 'assets/editor.css', __FILE__ ), array(), FL_SITE_VERSION );
	wp_register_script( 'fiberlan-block-editor', plugins_url( 'assets/editor.js', __FILE__ ), array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-core-data', 'wp-html-entities', 'wp-rich-text', 'wp-data', 'wp-hooks', 'wp-url' ), FL_SITE_VERSION, true );
	foreach ( array( 'contact' => 'fl_contact_render', 'projects' => 'fl_projects_render', 'consent' => 'fl_consent_render', 'copyright' => 'fl_copyright_render', 'languages' => 'fl_languages_render' ) as $name => $render ) {
		register_block_type( __DIR__ . '/blocks/' . $name, array( 'render_callback' => $render ) );
	}
	foreach ( array( 'breadcrumbs', 'service-links', 'not-found' ) as $name ) {
		register_block_type(
			__DIR__ . '/blocks/' . $name,
			array( 'render_callback' => 'fl_' . str_replace( '-', '_', $name ) . '_render' )
		);
	}
} );

add_action( 'admin_init', static function () {
	register_setting( 'reading', 'fl_cookieyes_id', array( 'sanitize_callback' => static function ( $value ) {
		return is_string( $value ) && preg_match( '/^[a-f0-9]{24}$/iD', trim( $value ) ) ? strtolower( trim( $value ) ) : '';
	} ) );
	add_settings_field( 'fl_cookieyes_id', 'CookieYes – Site ID', static function () {
		printf( '<input type="text" name="fl_cookieyes_id" value="%s" maxlength="24" pattern="[a-fA-F0-9]{24}"><p class="description">Opcjonalny identyfikator klienta z adresu skryptu CookieYes. Pozostaw puste, jeśli baner wstawia już wtyczka lub inna integracja.</p>', esc_attr( get_option( 'fl_cookieyes_id', '' ) ) );
	}, 'reading' );

	register_setting( 'reading', 'fl_legal_approved', array( 'type' => 'boolean', 'sanitize_callback' => static function ( $value ) {
		return in_array( $value, array( '1', 1, true ), true );
	} ) );
	add_settings_field( 'fl_legal_approved', 'Dokumenty prywatności', static function () {
		printf( '<label><input type="checkbox" name="fl_legal_approved" value="1"%s> Administrator danych zatwierdził obie wersje polityk do indeksowania.</label>', checked( (bool) get_option( 'fl_legal_approved' ), true, false ) );
	}, 'reading' );
} );

add_action( 'wp_head', static function () {
	$id = get_option( 'fl_cookieyes_id', '' );
	if ( $id && ! defined( 'CKY_PLUGIN_VERSION' ) && ! defined( 'CLI_PLUGIN_VERSION' ) && ! defined( 'CLI_VERSION' ) && preg_match( '/^[a-f0-9]{24}$/D', $id ) ) {
		printf( '<script id="cookieyes" src="%s"></script>' . "\n", esc_url( 'https://cdn-cookieyes.com/client_data/' . $id . '/script.js' ) );
	}
}, 1 );
