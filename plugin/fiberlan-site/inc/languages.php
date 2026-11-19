<?php
defined( 'ABSPATH' ) || exit;

function fl_page_id( $key, $lang ) {
	$ids = get_option( 'fl_site_pages', array() );
	return (int) ( $ids[ $lang ][ $key ] ?? 0 );
}

function fl_url( $key, $lang ) {
	$id = fl_page_id( $key, $lang );
	if ( ! $id ) {
		return '';
	}
	$post = get_post( $id );
	if ( $post && ! empty( $GLOBALS['fl_building_site'] ) && 'draft' === $post->post_status ) {
		$post = clone $post;
		$post->post_status = 'publish';
	}
	return $post ? get_permalink( $post ) : '';
}

function fl_page_lang( $id ) {
	$en = fl_page_id( 'home', 'en' );
	return $id && $en && ( $id === $en || in_array( $en, get_post_ancestors( $id ), true ) ) ? 'en' : 'pl';
}

function fl_alternates( $id ) {
	$key = get_post_meta( $id, '_fl_page_key', true );
	$urls = array();
	foreach ( array( 'pl', 'en' ) as $lang ) {
		$other = fl_page_id( $key, $lang );
		// Editors can pair new case studies using the same slug under the two project pages.
		$post = $key ? null : get_post( $id );
		if ( $post && (int) $post->post_parent === fl_page_id( 'projects', fl_page_lang( $id ) ) && $post->post_parent ) {
			$parent = fl_page_id( 'projects', $lang );
			$match = $parent ? get_page_by_path( get_page_uri( $parent ) . '/' . $post->post_name ) : null;
			$other = $match ? $match->ID : 0;
		}
		if ( $other && 'publish' === get_post_status( $other ) ) {
			$urls[ $lang ] = get_permalink( $other );
		}
	}
	return $urls;
}

add_filter( 'language_attributes', static function ( $attributes ) {
	if ( ( is_page() || is_404() ) && 'en' === fl_request_language() ) {
		return preg_replace( '/\blang="[^"]*"/', 'lang="en-GB"', $attributes );
	}
	return $attributes;
} );

add_action( 'wp_head', static function () {
	if ( ! is_page() ) {
		return;
	}
	$urls = fl_alternates( get_queried_object_id() );
	foreach ( $urls as $lang => $url ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( $lang ), esc_url( $url ) );
	}
	if ( isset( $urls['pl'] ) ) {
		printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $urls['pl'] ) );
	}
} );

function fl_languages_render( $attrs ) {
	$id = get_queried_object_id();
	$urls = fl_alternates( $id );
	$lang = fl_request_language();
	foreach ( array( 'pl', 'en' ) as $code ) {
		if ( ! isset( $urls[ $code ] ) ) {
			$home = fl_page_id( 'home', $code );
			if ( $home && 'publish' === get_post_status( $home ) ) {
				$urls[ $code ] = get_permalink( $home );
			}
		}
	}
	if ( count( $urls ) < 2 ) {
		return '';
	}
	$other = 'pl' === $lang ? 'en' : 'pl';
	$copy = fl_block_values( 'languages', $attrs, $lang );
	$html = '<nav class="fl-languages" data-lang="' . $lang . '" aria-label="' . esc_attr( $copy['label'] ) . '">';
	foreach ( array( 'pl', 'en' ) as $code ) {
		$url = $urls[ $code ];
		$html .= sprintf( '<a data-language="%1$s" lang="%1$s" hreflang="%1$s" href="%2$s" %3$s>%4$s</a>', esc_attr( $code ), esc_url( $url ), $lang === $code ? 'aria-current="true"' : '', esc_html( $copy[ $code . 'Text' ] ) );
	}
	return $html . '<a class="fl-language-hint" hidden data-language="' . $other . '" href="' . esc_url( $urls[ $other ] ) . '" hreflang="' . $other . '">' . esc_html( $copy['suggestion'] ) . '</a></nav>';
}

// The linked English brand returns to the English home page.
function fl_english_ui( $html ) {
	if ( ( ! is_page() && ! is_404() ) || 'en' !== fl_request_language() ) {
		return $html;
	}
	$logo = new WP_HTML_Tag_Processor( $html );
	if ( $logo->next_tag( 'a' ) ) {
		$logo->set_attribute( 'href', fl_url( 'home', 'en' ) );
	}
	return $logo->get_updated_html();
}
add_filter( 'render_block_core/site-logo', 'fl_english_ui' );
add_filter( 'render_block_core/site-title', 'fl_english_ui' );

add_action( 'template_redirect', static function () {
	if ( ( is_page() || is_404() ) && 'en' === fl_request_language() ) {
		switch_to_locale( in_array( 'en_GB', get_available_languages(), true ) ? 'en_GB' : 'en_US' );
	}
}, 0 );
