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
		if ( 'pl_PL' !== get_locale() || ( function_exists( 'fl_page_lang' ) && 'en' === fl_page_lang( get_queried_object_id() ) ) ) {
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

// Match the responsive image request to the width of its Gutenberg layout.
add_filter(
	'render_block_core/image',
	static function ( $html, $block ) {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		$hero    = in_array( 'fl-hero-figure', $classes, true );
		if ( ! $hero && ! in_array( 'fl-card-image', $classes, true ) ) {
			return $html;
		}
		$image = new WP_HTML_Tag_Processor( $html );
		if ( ! $image->next_tag( 'img' ) ) {
			return $html;
		}
		if ( $hero ) {
			$image->set_attribute( 'sizes', '(max-width: 52rem) 92vw, (max-width: 106rem) 45vw, 48rem' );
			$image->set_attribute( 'fetchpriority', 'high' );
			$image->set_attribute( 'loading', 'eager' );
		} else {
			$image->set_attribute( 'sizes', '(max-width: 37.5rem) 92vw, (max-width: 64rem) 45vw, (max-width: 106rem) 22vw, 23rem' );
			$image->set_attribute( 'loading', 'lazy' );
		}
		return $image->get_updated_html();
	},
	10,
	2
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
