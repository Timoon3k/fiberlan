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

function fiberlan_splicer_details() {
	$english = function_exists( 'fl_request_language' )
		? 'en' === fl_request_language()
		: 'en' === substr( get_bloginfo( 'language' ), 0, 2 );
	$title = 'Fujikura FSM-62S';
	$description = $english
		? 'Core-alignment fusion splicer for single-mode and multimode fibres, used for precise connections during network installation and maintenance.'
		: 'Spawarka światłowodowa z centrowaniem do rdzenia. Służy do precyzyjnego łączenia włókien jednomodowych i wielomodowych podczas budowy i serwisowania sieci.';
	$id = wp_unique_id( 'fl-device-tooltip-' );
	return sprintf(
		'<button type="button" class="fl-device-trigger" aria-label="%1$s" aria-describedby="%2$s" aria-expanded="false">'
		. '<span id="%2$s" class="fl-device-tooltip" role="tooltip"><strong>%3$s</strong><span>%4$s</span></span></button>',
		esc_attr( $title ), esc_attr( $id ), esc_html( $title ), esc_html( $description )
	);
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
	// Earlier installations stored the original starter caption and ALT directly in the page.
	if ( $cutout ) {
		$updated = strtr( $updated, array(
			'alt="Spawarka światłowodowa z otwartą osłoną, obcinarką i akcesoriami."' => 'alt="Spawarka światłowodowa Fujikura FSM-62S z otwartą osłoną i włączonym ekranem."',
			'alt="Fibre optic fusion splicer with its cover open, a cleaver and accessories."' => 'alt="Fujikura FSM-62S fusion splicer with its cover open and display lit."',
		) );
		$updated = preg_replace(
			'~<figcaption\b[^>]*>\s*(?:Spawarka światłowodowa(?: z obcinarką)?|Fusion splicer and cleaver|Fibre optic fusion splicer)\s*</figcaption>~u',
			'',
			$updated
		);
	}
	$scene = $cutout ? 'fl-splicer-scene' : '';
	if ( in_array( $class, array( 'fl-section-image', 'fl-card-image' ), true ) && 'fluke-networks-versiv-2-client-photo.jpg' === $filename ) {
		$scene = 'fl-fluke-cutout';
	}
	if ( $cutout && 'none' === ( $block['attrs']['linkDestination'] ?? 'none' ) && false === strpos( $updated, 'wp-lightbox-container' ) ) {
		$end = strrpos( $updated, '</figure>' );
		if ( false !== $end ) {
			$updated = substr_replace( $updated, fiberlan_splicer_details(), $end, 0 );
		}
	}
	// Serve the original camera file in the hero so the controls and display retain their detail.
	$source = sprintf( '<picture%s%s><source type="%s" srcset="%s"%s>',
		$scene ? ' class="' . esc_attr( $scene ) . '"' : '',
		$cutout ? ' style="' . esc_attr( '--fl-table-photo:url(' . wp_json_encode( esc_url_raw( $src ), JSON_UNESCAPED_SLASHES ) . ')' ) . '"' : '',
		$cutout ? 'image/jpeg' : 'image/webp',
		$cutout ? esc_url( $src ) : esc_attr( $webp ),
		$cutout ? '' : ' sizes="' . esc_attr( $sizes ) . '"'
	);
	return preg_replace_callback(
		'~<img\s[^>]+>~i',
		static function ( $m ) use ( $source ) { return $source . $m[0] . '</picture>'; },
		$updated,
		1
	);
}, 10, 2 );


// Provide the full attachment to the gallery, rather than its thumbnail.
add_filter( 'render_block_core/image', static function ( $html, $block ) {
	$classes = preg_split( '/\s+/', $block['attrs']['className'] ?? '' );
	if ( in_array( 'fl-operator-logo', $classes, true ) ) {
		$tag = new WP_HTML_Tag_Processor( $html );
		if ( $tag->next_tag( 'img' ) ) {
			$tag->set_attribute( 'referrerpolicy', 'no-referrer' );
			$tag->set_attribute( 'loading', 'eager' );
			return $tag->get_updated_html();
		}
	}
	$id = absint( $block['attrs']['id'] ?? 0 );
	if ( ! $id ) {
		return $html;
	}
	$full = wp_get_attachment_image_url( $id, 'full' );
	if ( $full ) {
		$tag = new WP_HTML_Tag_Processor( $html );
		if ( $tag->next_tag( 'img' ) ) {
			$tag->set_attribute( 'data-fl-full', $full );
			return $tag->get_updated_html();
		}
	}
	return $html;
}, 30, 2 );
