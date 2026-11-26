<?php
/**
 * Locally licensed Suisse Intl. Supports WordPress Font Library and original
 * webfont filenames; no commercial binaries are distributed with this theme.
 */
defined( 'ABSPATH' ) || exit;

function fiberlan_suisse_normalize( $name ) {
	return strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', (string) $name ) );
}

function fiberlan_suisse_family_matches( $name ) {
	$name = fiberlan_suisse_normalize( $name );
	return 0 === strpos( $name, 'suisseintl' )
		&& false === strpos( $name, 'condensed' ) && false === strpos( $name, 'mono' );
}

function fiberlan_suisse_filename_weight( $name ) {
	$name = fiberlan_suisse_normalize( pathinfo( $name, PATHINFO_FILENAME ) );
	if ( ! fiberlan_suisse_family_matches( $name ) || preg_match( '/italic|oblique|trial|demo/', $name ) ) {
		return 0;
	}
	// Check longer names first: SemiBold must not be mistaken for Bold.
	foreach ( array( 'semibold' => 600, 'demibold' => 600, 'medium' => 500, 'regular' => 400, 'normal' => 400, 'book' => 450, 'black' => 900, 'bold' => 700, 'light' => 300 ) as $label => $weight ) {
		if ( false !== strpos( $name, $label ) ) {
			return $weight;
		}
	}
	return 0;
}

function fiberlan_suisse_format( $path ) {
	if ( ! is_file( $path ) || ! is_readable( $path ) || filesize( $path ) < 48 ) {
		return '';
	}
	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$magic = @file_get_contents( $path, false, null, 0, 4 );
	if ( 'woff2' === $ext && 'wOF2' === $magic ) {
		return 'woff2';
	}
	if ( 'woff' === $ext && 'wOFF' === $magic ) {
		return 'woff';
	}
	// Only existing Font Library installations may use these formats.
	if ( 'otf' === $ext && 'OTTO' === $magic ) {
		return 'opentype';
	}
	if ( 'ttf' === $ext && "\x00\x01\x00\x00" === $magic ) {
		return 'truetype';
	}
	return '';
}

function fiberlan_suisse_roots() {
	$uploads = wp_upload_dir( null, false );
	$roots = array();
	if ( empty( $uploads['error'] ) ) {
		foreach ( array( 'fiberlan-fonts', 'fonts' ) as $dir ) {
			$roots[] = array(
				'path' => trailingslashit( $uploads['basedir'] ) . $dir,
				'url'  => trailingslashit( $uploads['baseurl'] ) . $dir,
				'label' => 'uploads/' . $dir,
			);
		}
	}
	if ( function_exists( 'wp_get_font_dir' ) ) {
		$dir = wp_get_font_dir();
		if ( empty( $dir['error'] ) && ! empty( $dir['basedir'] ) && ! empty( $dir['baseurl'] ) ) {
			$roots[] = array( 'path' => $dir['basedir'], 'url' => $dir['baseurl'], 'label' => 'Biblioteka fontów WordPress' );
		}
	}
	return $roots;
}

/** Resolve a registered URL only within the site's configured font directories. */
function fiberlan_suisse_local_face( $url, $roots ) {
	if ( ! is_string( $url ) || ! $url ) {
		return null;
	}
	$source = wp_parse_url( $url );
	if ( ! is_array( $source ) || empty( $source['host'] ) || empty( $source['path'] ) ) {
		return null;
	}
	foreach ( $roots as $root ) {
		$base = wp_parse_url( $root['url'] );
		if ( empty( $base['host'] ) || strtolower( $base['host'] ) !== strtolower( $source['host'] ) ) {
			continue;
		}
		$prefix = trailingslashit( rawurldecode( $base['path'] ?? '' ) );
		$path = rawurldecode( $source['path'] );
		if ( 0 !== strpos( $path, $prefix ) ) {
			continue;
		}
		$relative = substr( $path, strlen( $prefix ) );
		if ( ! $relative || preg_match( '~(?:^|/)\.\.?(?:/|$)|[\\\\\x00]~', $relative ) ) {
			continue;
		}
		$root_path = realpath( $root['path'] );
		$file = realpath( trailingslashit( $root['path'] ) . $relative );
		if ( ! $root_path || ! $file || 0 !== strpos( $file, trailingslashit( $root_path ) ) ) {
			continue;
		}
		$format = fiberlan_suisse_format( $file );
		if ( $format ) {
			return array(
				'url' => trailingslashit( $root['url'] ) . implode( '/', array_map( 'rawurlencode', explode( '/', $relative ) ) ),
				'format' => $format, 'file' => wp_basename( $file ), 'source' => $root['label'],
			);
		}
	}
	return null;
}

/** Prefer metadata from the native Font Library over guesses from filenames. */
function fiberlan_suisse_fonts() {
	static $available = null;
	if ( null !== $available ) {
		return $available;
	}
	$available = array();
	$roots = fiberlan_suisse_roots();
	if ( post_type_exists( 'wp_font_face' ) ) {
		$faces = get_posts( array( 'post_type' => 'wp_font_face', 'post_status' => 'publish', 'numberposts' => -1, 'no_found_rows' => true, 'suppress_filters' => true ) );
		foreach ( $faces as $face ) {
			$data = json_decode( $face->post_content, true );
			if ( ! is_array( $data ) || ! fiberlan_suisse_family_matches( $data['fontFamily'] ?? '' )
				|| 'normal' !== strtolower( $data['fontStyle'] ?? 'normal' ) ) {
				continue;
			}
			$weight = (string) ( $data['fontWeight'] ?? '400' );
			if ( ! preg_match( '/^[1-9]\d{2}$/D', $weight ) ) {
				continue;
			}
			foreach ( (array) ( $data['src'] ?? array() ) as $src ) {
				$asset = fiberlan_suisse_local_face( $src, $roots );
				if ( $asset ) {
					$asset['source'] = 'Biblioteka fontów WordPress';
					$available[ (int) $weight ] = $asset;
					break;
				}
			}
		}
	}
	// Scan only font directories (and at most two subdirectory levels).
	// Never scan the full media library or load third-party URLs.
	foreach ( $roots as $root ) {
		$base = realpath( $root['path'] );
		if ( ! $base || ! is_dir( $base ) || ! is_readable( $base ) ) {
			continue;
		}
		$dirs = array( array( $base, 0 ) );
		$visited = 0;
		while ( $dirs && $visited < 1200 ) {
			list( $directory, $depth ) = array_shift( $dirs );
			foreach ( @scandir( $directory ) ?: array() as $file ) {
				if ( '.' === $file || '..' === $file || '.' === substr( $file, 0, 1 ) || ++$visited > 1200 ) {
					continue;
				}
				$path = $directory . '/' . $file;
				if ( is_link( $path ) ) {
					continue;
				}
				if ( is_dir( $path ) && $depth < 2 ) {
					$dirs[] = array( $path, $depth + 1 );
					continue;
				}
				$weight = fiberlan_suisse_filename_weight( $file );
				if ( ! $weight || isset( $available[ $weight ] ) || ! in_array( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ), array( 'woff2', 'woff' ), true ) ) {
					continue;
				}
				$relative = substr( $path, strlen( $base ) + 1 );
				$url = trailingslashit( $root['url'] ) . implode( '/', array_map( 'rawurlencode', explode( '/', $relative ) ) );
				$asset = fiberlan_suisse_local_face( $url, array( $root ) );
				if ( $asset ) {
					$available[ $weight ] = $asset;
				}
			}
		}
	}
	ksort( $available );
	return $available;
}

function fiberlan_suisse_css() {
	$fonts = fiberlan_suisse_fonts();
	if ( ! $fonts ) {
		return '';
	}
	$css = '';
	foreach ( $fonts as $weight => $asset ) {
		$css .= sprintf( "@font-face{font-family:'FIBERLAN Suisse Intl';font-style:normal;font-weight:%d;font-display:swap;src:url(%s) format('%s');}\n",
			(int) $weight, wp_json_encode( $asset['url'], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ), $asset['format'] );
	}
	// Explicit alias avoids conflicts with differing family names in the Font Library.
	$css .= ":root,.editor-styles-wrapper{--wp--preset--font-family--display:'FIBERLAN Suisse Intl','Manrope',Arial,sans-serif;--wp--preset--font-family--body:'FIBERLAN Suisse Intl','Manrope',Arial,sans-serif;}\n";
	$css .= ".wp-site-blocks,.editor-styles-wrapper{font-family:var(--wp--preset--font-family--body);}\n";
	return $css;
}

add_action( 'enqueue_block_assets', static function () {
	$css = fiberlan_suisse_css();
	if ( ! $css ) {
		return;
	}
	wp_register_style( 'fiberlan-suisse-local', false, array(), null );
	wp_enqueue_style( 'fiberlan-suisse-local' );
	wp_add_inline_style( 'fiberlan-suisse-local', $css );
}, 100 );

// For non-iframed editors and installations that load editor CSS separately.
add_filter( 'block_editor_settings_all', static function ( $settings ) {
	$css = fiberlan_suisse_css();
	if ( $css ) {
		$settings['styles'][] = array( 'css' => $css );
	}
	return $settings;
}, 100 );

add_action( 'admin_notices', static function () {
	$screen = get_current_screen();
	if ( ! $screen || 'toplevel_page_fiberlan-manager' !== $screen->id || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fonts = fiberlan_suisse_fonts();
	echo '<div class="notice ' . ( $fonts ? 'notice-success' : 'notice-warning' ) . '"><p><strong>FIBERLAN — Suisse Intl:</strong> ';
	echo $fonts ? 'Wykryto lokalne pliki; motyw przypisuje krój do treści i nagłówków.' : 'Nie wykryto żadnej obsługiwanej odmiany. Obecnie działa Manrope.';
	echo '</p>';
	if ( $fonts ) {
		echo '<ul>';
		foreach ( $fonts as $weight => $asset ) {
			printf( '<li>%s — %s (%s) · <a href="%s" target="_blank" rel="noopener noreferrer">Sprawdź URL</a></li>',
				esc_html( (string) $weight ), esc_html( $asset['file'] ), esc_html( $asset['source'] ), esc_url( $asset['url'] ) );
		}
		echo '</ul>';
		if ( ! isset( $fonts[400] ) || ( ! isset( $fonts[600] ) && ! isset( $fonts[700] ) ) ) {
			echo '<p>Brakuje Regular 400 lub SemiBold 600/Bold 700. Przeglądarka dobierze najbliższą dostępną grubość; zalecamy instalację tych odmian.</p>';
		}
	}
	echo '<p>Obsługiwane miejsca: Biblioteka fontów WordPress (Wygląd → Fonty / Edytor → Style → Typografia) oraz <code>wp-content/uploads/fiberlan-fonts/</code> (również podfoldery). Oryginalne nazwy WOFF2 są obsługiwane; nie trzeba ich zmieniać. Po instalacji wyczyść cache.</p></div>';
} );
