<?php
/**
 * FIBERLAN block theme.
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/contact-content.php';
require_once __DIR__ . '/inc/footer.php';
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
	// Load the heading and navigation fonts before the first paint.
	foreach ( array( 'BRITANIC.woff2', 'Manrope.woff2' ) as $font ) {
		$resources[] = array(
			'href'        => get_theme_file_uri( 'assets/fonts/' . $font ),
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}
	return $resources;
} );

// A stable fragment target also works with headers customized in the Site Editor.
add_action( 'wp_body_open', static function () {
	echo '<div id="fl-page-top" tabindex="-1"></div>';
} );

// The floating control works independently of the active footer template part.
add_action( 'wp_footer', static function () {
	$lang = function_exists( 'fl_request_language' ) ? fl_request_language() : substr( get_locale(), 0, 2 );
	$label = 'en' === $lang ? 'Back to top' : 'Wróć na górę';
	printf( '<a class="fl-floating-top" href="#fl-page-top" aria-label="%s"></a>', esc_attr( $label ) );
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

// Core marks exact navigation links. Mark the section on its descendant pages too.
add_filter( 'render_block_core/navigation', static function ( $html, $block ) {
	if ( ! is_page() || ! in_array( 'fl-nav', preg_split( '/\s+/', $block['attrs']['className'] ?? '' ), true ) ) {
		return $html;
	}
	$parents = get_post_ancestors( get_queried_object_id() );
	$paths = array_map( 'get_permalink', $parents );
	if ( function_exists( 'fl_url' ) ) {
		$paths = array_diff( $paths, array( fl_url( 'home', 'en' ), fl_url( 'home', 'pl' ) ) );
	}
	if ( ! $paths ) {
		return $html;
	}
	$nav = new WP_HTML_Tag_Processor( $html );
	while ( $nav->next_tag( array( 'tag_name' => 'A', 'class_name' => 'wp-block-navigation-item__content' ) ) ) {
		if ( in_array( $nav->get_attribute( 'href' ), $paths, true ) ) {
			$nav->set_attribute( 'aria-current', 'location' );
		}
	}
	return $nav->get_updated_html();
}, 10, 2 );
