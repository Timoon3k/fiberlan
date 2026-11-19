<?php
defined( 'ABSPATH' ) || exit;

function fl_request_language() {
	if ( is_page() ) {
		return fl_page_lang( get_queried_object_id() );
	}
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	$base = untrailingslashit( (string) wp_parse_url( home_url( '/en/' ), PHP_URL_PATH ) );
	return $path === $base || 0 === strpos( (string) $path, $base . '/' ) ? 'en' : 'pl';
}

function fl_breadcrumbs( $id ) {
	$lang = fl_page_lang( $id );
	$home = fl_page_id( 'home', $lang );
	if ( $id === $home || ! $home ) {
		return array();
	}
	$items = array(
		array( 'name' => fl_text( $lang, 'Strona główna', 'Home' ), 'url' => get_permalink( $home ) ),
	);
	$parents = array_reverse( get_post_ancestors( $id ) );
	foreach ( array_merge( $parents, array( $id ) ) as $page ) {
		if ( $page !== $home && 'publish' === get_post_status( $page ) ) {
			$items[] = array( 'name' => get_the_title( $page ), 'url' => get_permalink( $page ) );
		}
	}
	return count( $items ) > 1 ? $items : array();
}

function fl_breadcrumbs_render() {
	if ( ! is_page() ) {
		return '';
	}
	$id = get_queried_object_id();
	$items = fl_breadcrumbs( $id );
	if ( ! $items ) {
		return '';
	}
	$lang = fl_page_lang( $id );
	$label = fl_text( $lang, 'Ścieżka nawigacji', 'Breadcrumbs' );
	$last = count( $items ) - 1;
	$html = '<div class="fl-crumb-bar"><nav class="fl-shell fl-crumbs" aria-label="' . esc_attr( $label ) . '"><ol role="list">';
	foreach ( $items as $index => $item ) {
		$name = esc_html( $item['name'] );
		$html .= $index === $last ? '<li aria-current="page">' . $name . '</li>' : '<li><a href="' . esc_url( $item['url'] ) . '">' . $name . '</a></li>';
	}
	return $html . '</ol></nav></div>';
}

function fl_service_sections( $id ) {
	if ( 'services' !== get_post_meta( $id, '_fl_page_key', true ) ) {
		return array();
	}
	$items = array();
	foreach ( parse_blocks( get_post_field( 'post_content', $id ) ) as $section ) {
		$anchor = $section['attrs']['anchor'] ?? '';
		if ( ! $anchor || 'core/group' !== $section['blockName'] ) {
			continue;
		}
		// The first heading is the editable title of the service section.
		$markup = serialize_block( $section );
		if ( preg_match( '~<h2\b[^>]*>(.*?)</h2>~is', $markup, $matches ) ) {
			$items[] = array( 'name' => wp_strip_all_tags( $matches[1] ), 'url' => get_permalink( $id ) . '#' . rawurlencode( $anchor ) );
		}
	}
	return $items;
}

function fl_service_links_render() {
	if ( ! is_page() ) {
		return '';
	}
	$id = get_queried_object_id();
	$items = fl_service_sections( $id );
	if ( ! $items ) {
		return '';
	}
	$lang = fl_page_lang( $id );
	$html = '<aside class="fl-section fl-service-index"><div class="fl-shell fl-reading"><h2>' . esc_html( fl_text( $lang, 'Zakres usług', 'Explore our services' ) ) . '</h2><ul>';
	foreach ( $items as $item ) {
		$html .= '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a></li>';
	}
	return $html . '</ul></div></aside>';
}

function fl_not_found_render() {
	$lang = fl_request_language();
	$title = fl_text( $lang, 'Nie znaleziono strony', 'Page not found' );
	$body = fl_text( $lang, 'Sprawdź adres lub wróć na stronę główną FIBERLAN.', 'Check the address or return to the FIBERLAN homepage.' );
	$url = fl_url( 'home', $lang ) ?: home_url( '/' );
	return do_blocks( fl_hero( $lang, '404', $title, $body ) . fl_section( fl_button( fl_text( $lang, 'Strona główna', 'Home' ), $url ) ) );
}

add_filter( '404_template_hierarchy', static function ( $templates ) {
	if ( 'en' === fl_request_language() ) {
		array_unshift( $templates, '404-en.php' );
	}
	return $templates;
} );

add_action( 'post_updated', static function ( $id, $after, $before ) {
	if ( 'page' !== $after->post_type || 'publish' !== $before->post_status || 'publish' !== $after->post_status ) {
		return;
	}
	if ( $before->post_name === $after->post_name && (int) $before->post_parent === (int) $after->post_parent ) {
		return;
	}
	$old = get_page_uri( $before );
	$new = get_page_uri( $after );
	if ( ! $old || ! $new || $old === $new ) {
		return;
	}
	$redirects = get_option( 'fl_path_redirects', array() );
	$redirects[ $old ] = $id;
	foreach ( get_pages( array( 'child_of' => $id, 'post_status' => 'publish' ) ) as $child ) {
		$tail = substr( get_page_uri( $child ), strlen( $new ) );
		$redirects[ $old . $tail ] = $child->ID;
	}
	update_option( 'fl_path_redirects', $redirects, false );
}, 10, 3 );

add_action( 'template_redirect', static function () {
	if ( ! is_404() || ! in_array( $_SERVER['REQUEST_METHOD'] ?? '', array( 'GET', 'HEAD' ), true ) ) {
		return;
	}
	$prefix = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( 0 !== strpos( $path, $prefix ) ) {
		return;
	}
	$old = trim( substr( $path, strlen( $prefix ) ), '/' );
	$ids = get_option( 'fl_path_redirects', array() );
	$id = $ids[ $old ] ?? 0;
	if ( $id && 'publish' === get_post_status( $id ) && $old !== get_page_uri( $id ) ) {
		wp_safe_redirect( add_query_arg( fl_campaign_args(), get_permalink( $id ) ), 301 );
		exit;
	}
}, 1 );
