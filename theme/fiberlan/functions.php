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
		add_editor_style( 'style.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'fiberlan', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
	}
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'fiberlan', array( 'label' => 'FIBERLAN' ) );
	}
);

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
