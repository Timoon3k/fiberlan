<?php
/**
 * Optional licensed Suisse Intl webfonts, stored outside the theme so a theme
 * update cannot remove them. No commercial font binaries are distributed.
 */
defined( 'ABSPATH' ) || exit;

function fiberlan_suisse_font_files() {
	return array(
		400 => 'suisse-intl-regular.woff2',
		500 => 'suisse-intl-medium.woff2',
		600 => 'suisse-intl-semibold.woff2',
		700 => 'suisse-intl-bold.woff2',
	);
}

function fiberlan_suisse_fonts() {
	$uploads = wp_upload_dir( null, false );
	$available = array();
	if ( ! empty( $uploads['error'] ) ) {
		return $available;
	}
	foreach ( fiberlan_suisse_font_files() as $weight => $name ) {
		$path = trailingslashit( $uploads['basedir'] ) . 'fiberlan-fonts/' . $name;
		if ( ! is_readable( $path ) || ! is_file( $path ) || filesize( $path ) < 48 ) {
			continue;
		}
		// Reject files that are not WOFF2 containers.
		$handle = @fopen( $path, 'rb' );
		if ( ! $handle ) {
			continue;
		}
		$magic = fread( $handle, 4 );
		fclose( $handle );
		if ( 'wOF2' !== $magic ) {
			continue;
		}
		$available[ $weight ] = trailingslashit( $uploads['baseurl'] ) . 'fiberlan-fonts/' . $name;
	}
	return $available;
}

add_action( 'enqueue_block_assets', static function () {
	$fonts = fiberlan_suisse_fonts();
	// Do not mix an installed partial family with fallback text. Require the
	// regular and a heading weight; use the nearest available real weight.
	if ( ! isset( $fonts[400] ) || ( ! isset( $fonts[600] ) && ! isset( $fonts[700] ) ) ) {
		return;
	}
	$css = '';
	foreach ( $fonts as $weight => $url ) {
		$css .= sprintf( "@font-face{font-family:'Suisse Intl';font-style:normal;font-weight:%d;font-display:swap;src:url(%s) format('woff2');}\n",
			(int) $weight, wp_json_encode( esc_url_raw( $url ), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) );
	}
	wp_register_style( 'fiberlan-suisse-local', false, array(), null );
	wp_enqueue_style( 'fiberlan-suisse-local' );
	wp_add_inline_style( 'fiberlan-suisse-local', $css );
}, 20 );

add_action( 'admin_notices', static function () {
	$screen = get_current_screen();
	if ( ! $screen || 'toplevel_page_fiberlan-manager' !== $screen->id || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fonts = fiberlan_suisse_fonts();
	$ready = isset( $fonts[400] ) && ( isset( $fonts[600] ) || isset( $fonts[700] ) );
	if ( $ready ) {
		echo '<div class="notice notice-success"><p><strong>FIBERLAN:</strong> Suisse Intl aktywny z lokalnych plików WOFF2. Dostępne grubości: ' . esc_html( implode( ', ', array_keys( $fonts ) ) ) . '.</p></div>';
		return;
	}
	echo '<div class="notice notice-info"><p><strong>FIBERLAN — Suisse Intl:</strong> typografia i skala są przygotowane, ale font komercyjny wymaga licencjonowanych plików. Do tego czasu działa Manrope. Wgraj oryginalne WOFF2 do katalogu <code>wp-content/uploads/fiberlan-fonts/</code>. Wymagany Regular 400 i SemiBold 600 lub Bold 700. Opcjonalnie Medium 500. Nazwy plików: <code>suisse-intl-regular.woff2</code>, <code>suisse-intl-medium.woff2</code>, <code>suisse-intl-semibold.woff2</code>, <code>suisse-intl-bold.woff2</code>. <a href="https://www.swisstypefaces.com/fonts/suisse/" target="_blank" rel="noopener noreferrer">Swiss Typefaces — licencja i pliki</a>.</p></div>';
} );
