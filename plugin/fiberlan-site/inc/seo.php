<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/seo-copy.php';

function fl_seo_defaults( $id ) {
	$entries = fl_seo_catalogue();
	$entry = $entries[ get_post_meta( $id, '_fl_page_key', true ) ] ?? null;
	$index = 'en' === fl_page_lang( $id ) ? 2 : 0;
	if ( $entry ) {
		return array( 'title' => $entry[ $index ], 'description' => $entry[ $index + 1 ] );
	}
	$post = get_post( $id );
	$excerpt = $post->post_excerpt ?: excerpt_remove_blocks( $post->post_content );
	return array(
		'title'       => get_the_title( $id ) . ' | ' . get_bloginfo( 'name' ),
		'description' => wp_html_excerpt( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $excerpt ) ) ) ), 155, '…' ),
	);
}

// Native Gutenberg sidebar reads and writes existing SEO meta via WordPress REST.
add_action( 'init', static function () {
	foreach ( array( 'rank_math_title', 'rank_math_description' ) as $key ) {
		register_post_meta( 'page', $key, array(
			'single' => true,
			'type' => 'string',
			'default' => '',
			'show_in_rest' => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback' => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', (int) $post_id );
			},
		) );
	}
} );

/** One set of editable titles and descriptions shared with Rank Math. */
function fl_seo_meta( $id, $key ) {
	$saved = get_post_meta( $id, 'rank_math_' . $key, true );
	if ( is_string( $saved ) && '' !== trim( $saved ) ) {
		if ( class_exists( '\RankMath\Helper' ) ) {
			$saved = \RankMath\Helper::replace_vars( $saved, get_post( $id ) );
		}
		// Keep simple Rank Math tokens portable when the SEO plugin is disabled.
		$saved = strtr( $saved, array( '%title%' => get_the_title( $id ), '%sitename%' => get_bloginfo( 'name' ), '%sep%' => '|' ) );
		$saved = trim( preg_replace( '/%[a-z_]+%/i', '', wp_strip_all_tags( $saved ) ) );
	}
	return $saved ?: fl_seo_defaults( $id )[ $key ];
}

function fl_rank_math_ready() {
	return function_exists( 'rank_math' ) && isset( rank_math()->head );
}

add_action( 'add_meta_boxes_page', static function ( $post ) {
	// Avoid the huge bottom SEO metabox in Gutenberg; the sidebar edits these fields.
	if ( $post instanceof WP_Post && use_block_editor_for_post( $post ) ) {
		return;
	}
	if ( class_exists( '\RankMath\Helper' ) && ! \RankMath\Helper::is_invalid_registration() ) {
		return;
	}
	add_meta_box( 'fl-page-seo', 'FIBERLAN – SEO', static function ( $post ) {
		wp_nonce_field( 'fl_save_seo', 'fl_seo_nonce' );
		$defaults = fl_seo_defaults( $post->ID );
		foreach ( array( 'title' => 'Tytuł SEO', 'description' => 'Opis meta' ) as $key => $label ) {
			$name = 'rank_math_' . $key;
			printf(
				'<p><label for="%1$s">%2$s</label><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s" placeholder="%4$s"></p>',
				esc_attr( $name ), esc_html( $label ), esc_attr( get_post_meta( $post->ID, $name, true ) ), esc_attr( $defaults[ $key ] )
			);
		}
		echo '<p>Puste pola korzystają z opisu domyślnego. Zapisane wartości odczyta również Rank Math.</p>';
	}, 'page', 'normal', 'default' );
} );

add_action( 'save_post_page', static function ( $id ) {
	$nonce = fl_post_string( 'fl_seo_nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'fl_save_seo' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) ) {
		return;
	}
	foreach ( array( 'rank_math_title', 'rank_math_description' ) as $name ) {
		$value = sanitize_text_field( fl_post_string( $name ) );
		if ( '' !== $value ) {
			update_post_meta( $id, $name, wp_slash( $value ) );
		} else {
			delete_post_meta( $id, $name );
		}
	}
} );

add_filter( 'pre_get_document_title', static function ( $title ) {
	if ( fl_rank_math_ready() ) {
		return $title;
	}
	if ( is_404() ) {
		return esc_html( fl_text( fl_request_language(), 'Nie znaleziono strony', 'Page not found' ) . ' | ' . get_bloginfo( 'name' ) );
	}
	return is_page() ? esc_html( fl_seo_meta( get_queried_object_id(), 'title' ) ) : $title;
} );

function fl_seo_image( $id ) {
	$image = get_post_thumbnail_id( $id );
	if ( ! $image ) {
		$assets = get_option( 'fiberlan_starter_media', array() );
		$image = $assets['fiber-splicer-cleaver-client-photo.jpg'] ?? 0;
	}
	return $image ? wp_get_attachment_image_src( $image, 'full' ) : false;
}

add_action( 'wp_head', static function () {
	if ( ! is_page() || fl_rank_math_ready() ) {
		return;
	}
	$id = get_queried_object_id();
	$title = fl_seo_meta( $id, 'title' );
	$description = fl_seo_meta( $id, 'description' );
	$locale = 'en' === fl_page_lang( $id ) ? 'en_GB' : 'pl_PL';
	$meta = array(
		'description' => $description,
		'og:type' => 'website',
		'og:title' => $title,
		'og:description' => $description,
		'og:url' => get_permalink( $id ),
		'og:site_name' => get_bloginfo( 'name' ),
		'og:locale' => $locale,
		'twitter:card' => 'summary_large_image',
		'twitter:title' => $title,
		'twitter:description' => $description,
	);
	if ( count( fl_alternates( $id ) ) === 2 ) {
		$meta['og:locale:alternate'] = 'pl_PL' === $locale ? 'en_GB' : 'pl_PL';
	}
	$image = fl_seo_image( $id );
	if ( $image ) {
		$meta['og:image'] = $image[0];
		$meta['og:image:width'] = $image[1];
		$meta['og:image:height'] = $image[2];
		$meta['twitter:image'] = $image[0];
	}
	foreach ( $meta as $property => $value ) {
		if ( '' !== $value ) {
			printf( '<meta %s="%s" content="%s">' . "\n", 0 === strpos( $property, 'og:' ) ? 'property' : 'name', esc_attr( $property ), esc_attr( $value ) );
		}
	}
}, 5 );

foreach ( array( 'title', 'description' ) as $key ) {
	add_filter( 'rank_math/frontend/' . $key, static function ( $value ) use ( $key ) {
		if ( 'title' === $key && is_404() ) {
			return fl_text( fl_request_language(), 'Nie znaleziono strony', 'Page not found' ) . ' | ' . get_bloginfo( 'name' );
		}
		return is_page() ? fl_seo_meta( get_queried_object_id(), $key ) : $value;
	} );
}
foreach ( array( 'facebook', 'twitter' ) as $network ) {
	add_filter( 'rank_math/opengraph/' . $network . '/image', static function ( $image ) {
		if ( ! is_page() || get_post_meta( get_queried_object_id(), 'rank_math_facebook_image', true ) || get_post_meta( get_queried_object_id(), 'rank_math_twitter_image', true ) ) {
			return $image;
		}
		$fallback = fl_seo_image( get_queried_object_id() );
		return $image ?: ( $fallback[0] ?? '' );
	} );
}
add_filter( 'rank_math/opengraph/facebook/og_locale', static function ( $locale ) {
	return is_page() && 'en' === fl_page_lang( get_queried_object_id() ) ? 'en_GB' : $locale;
} );

function fl_policy_draft( $id ) {
	return ! get_option( 'fl_legal_approved' ) && in_array( get_post_meta( $id, '_fl_page_key', true ), array( 'privacy', 'cookies' ), true );
}

add_filter( 'rank_math/frontend/robots', static function ( $robots ) {
	if ( is_404() || ! get_option( 'blog_public' ) || fl_policy_draft( get_queried_object_id() ) ) {
		$robots['index'] = 'noindex';
	}
	return $robots;
} );
add_filter( 'rank_math/sitemap/entry', static function ( $entry, $type, $object ) {
	return 'post' === $type && fl_policy_draft( $object->ID ) ? false : $entry;
}, 10, 3 );
add_action( 'update_option_fl_legal_approved', static function () {
	if ( class_exists( '\RankMath\Sitemap\Cache' ) ) {
		\RankMath\Sitemap\Cache::invalidate_storage();
	}
} );
