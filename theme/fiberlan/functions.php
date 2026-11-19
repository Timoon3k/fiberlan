<?php
/**
 * FIBERLAN block theme.
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/contact-content.php';
require_once __DIR__ . '/inc/media.php';
require_once __DIR__ . '/inc/starter.php';
require_once __DIR__ . '/inc/responsive-media.php';

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/site.css' );
		if ( is_child_theme() ) {
			add_editor_style( 'style.css' );
		}
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$version = wp_get_theme( get_template() )->get( 'Version' );
		wp_enqueue_style( 'fiberlan', get_theme_file_uri( 'assets/site.css' ), array(), $version );
		if ( is_child_theme() ) {
			wp_enqueue_style( 'fiberlan-child', get_stylesheet_uri(), array( 'fiberlan' ), wp_get_theme()->get( 'Version' ) );
		}
		wp_enqueue_script( 'fiberlan-view', get_theme_file_uri( 'assets/view.js' ), array(), $version, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'fiberlan', array( 'label' => 'FIBERLAN' ) );
		register_block_style( 'core/image', array( 'name' => 'fiberlan-full', 'label' => 'Pełny kadr' ) );
		register_block_style( 'core/image', array( 'name' => 'fiberlan-wide', 'label' => 'Kadr panoramiczny' ) );
		register_block_style( 'core/gallery', array( 'name' => 'fiberlan-gallery', 'label' => 'Galeria realizacji' ) );
	}
);

add_filter( 'wp_preload_resources', static function ( $resources ) {
	// The display face is used by the first heading on both language versions.
	$resources[] = array(
		'href'        => get_theme_file_uri( 'assets/fonts/BRITANIC.woff2' ),
		'as'          => 'font',
		'type'        => 'font/woff2',
		'crossorigin' => 'anonymous',
	);
	return $resources;
} );

// A stable fragment target also works with headers customized in the Site Editor.
add_action( 'wp_body_open', static function () {
	echo '<div id="fl-page-top" tabindex="-1"></div>';
} );

// Keep the public keyboard controls in Polish on hosts missing the core language pack.
add_action(
	'wp',
	static function () {
		if ( 'pl_PL' !== get_locale() || ( function_exists( 'fl_request_language' ) && 'en' === fl_request_language() ) ) {
			return;
		}
		add_filter(
			'gettext_default',
			static function ( $translation, $text ) {
				static $fallback = array(
					'Skip to content' => 'Przejdź do treści',
					'Open menu'       => 'Otwórz menu',
					'Close menu'      => 'Zamknij menu',
				);
				return $translation === $text ? ( $fallback[ $text ] ?? $translation ) : $translation;
			},
			10,
			2
		);
	}
);

function fiberlan_policy_links( $lang ) {
	if ( ! function_exists( 'fl_url' ) || ! fl_page_id( 'privacy', $lang ) ) {
		return '';
	}
	$labels = 'pl' === $lang ? array( 'Polityka prywatności', 'Polityka cookies', 'Ustawienia zgód' ) : array( 'Privacy policy', 'Cookie policy', 'Consent settings' );
	$links  = array();
	foreach ( array( 'privacy', 'cookies' ) as $index => $key ) {
		$links[] = '<a href="' . esc_url( fl_url( $key, $lang ) ) . '">' . esc_html( $labels[ $index ] ) . '</a>';
	}
	$html = '<!-- wp:paragraph {"className":"fl-policy-links"} --><p class="fl-policy-links">' . implode( ' · ', $links ) . '</p><!-- /wp:paragraph -->';
	return $html . '<!-- wp:html --><button type="button" class="fl-consent-settings" hidden>' . esc_html( $labels[2] ) . '</button><!-- /wp:html -->';
}
