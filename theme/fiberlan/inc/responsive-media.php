<?php
/** Sizes and optimized sources for FIBERLAN photographs. */
defined( 'ABSPATH' ) || exit;

function fiberlan_photo_sizes( $class ) {
	$sizes = array(
		'fl-hero-figure'    => '(max-width: 52rem) 92vw, (max-width: 106rem) 45vw, 48rem',
		'fl-card-image'     => '(max-width: 37.5rem) 92vw, (max-width: 64rem) 45vw, (max-width: 106rem) 22vw, 23rem',
		'fl-section-image'  => '(max-width: 48rem) 92vw, (max-width: 106rem) 43vw, 45rem',
		'fl-project-image'  => '(max-width: 37.5rem) 32vw, (max-width: 64rem) 30vw, 22rem',
		'fl-subhero-visual' => '(max-width: 48rem) 92vw, 43vw',
	);
	return $sizes[ $class ] ?? '';
}

function fiberlan_webp_srcset( $filename ) {
	$files = array(
		'fiber-splicer-cleaver-client-photo.jpg',
		'fluke-networks-versiv-2-client-photo.jpg',
		'viavi-mpolx-client-photo.jpg',
	);
	if ( ! in_array( $filename, $files, true ) ) {
		return '';
	}
	$srcset = array();
	foreach ( array( 360, 640, 960, 1200 ) as $width ) {
		$path = 'assets/webp/' . pathinfo( $filename, PATHINFO_FILENAME ) . '-' . $width . '.webp';
		$srcset[] = get_theme_file_uri( $path ) . ' ' . $width . 'w';
	}
	return implode( ', ', $srcset );
}

add_filter( 'render_block_core/image', static function ( $content, $block ) {
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	$class = '';
	$sizes = '';
	foreach ( $classes as $candidate ) {
		$sizes = fiberlan_photo_sizes( $candidate );
		if ( $sizes ) {
			$class = $candidate;
			break;
		}
	}
	if ( ! $sizes ) {
		return $content;
	}

	$image = new WP_HTML_Tag_Processor( $content );
	if ( ! $image->next_tag( 'img' ) ) {
		return $content;
	}
	$hero = 'fl-hero-figure' === $class || 'fl-subhero-visual' === $class;
	$src = (string) $image->get_attribute( 'src' );
	$filename = wp_basename( (string) wp_parse_url( $src, PHP_URL_PATH ) );
	$id = absint( $block['attrs']['id'] ?? 0 );
	$original = get_option( 'fiberlan_starter_media', array() );
	$same_attachment = $id && isset( $original[ $filename ] ) && $id === (int) $original[ $filename ]
		&& $src === wp_get_attachment_image_url( $id, 'full' );
	$theme_original = $src && $src === get_theme_file_uri( 'assets/media/' . $filename );
	$webp = $same_attachment || $theme_original ? fiberlan_webp_srcset( $filename ) : '';

	if ( $id ) {
		$image->set_attribute( 'sizes', $sizes );
	}
	$image->set_attribute( 'loading', $hero ? 'eager' : 'lazy' );
	if ( $hero ) {
		$image->set_attribute( 'fetchpriority', 'high' );
	}
	$updated = $image->get_updated_html();
	if ( ! $webp ) {
		return $updated;
	}
	// Only the supplied photograph matches the traced silhouette. Library replacements use the normal frame.
	$cutout = 'fl-hero-figure' === $class && 'fiber-splicer-cleaver-client-photo.jpg' === $filename;
	$source = sprintf( '<picture%s><source type="image/webp" sizes="%s" srcset="%s">',
		$cutout ? ' class="fl-splicer-scene"' : '', esc_attr( $sizes ), esc_attr( $webp ) );
	return preg_replace_callback(
		'~<img\s[^>]+>~i',
		static function ( $m ) use ( $source ) { return $source . $m[0] . '</picture>'; },
		$updated,
		1
	);
}, 10, 2 );
