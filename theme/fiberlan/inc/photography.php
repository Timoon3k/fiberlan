<?php
/** Replace only FIBERLAN starter artwork. User uploads keep their captions, links and URLs. */
defined( 'ABSPATH' ) || exit;

function fiberlan_sample_images() {
	return array(
		'assets/diagrams/service-splice.svg', 'assets/diagrams/service-lan.svg',
		'assets/diagrams/project-datacenter.svg', 'assets/diagrams/project-factory.svg', 'assets/diagrams/project-ship.svg',
		'assets/scenes/optical-fibres.webp', 'assets/scenes/patch-panel.webp',
		'assets/scenes/server-racks.webp', 'assets/scenes/production-line.webp', 'assets/scenes/shipyard.webp',
	);
}

add_filter( 'theme_file_uri', static function ( $url, $file ) {
	if ( in_array( $file, fiberlan_sample_images(), true ) && $url === get_template_directory_uri() . '/' . $file ) {
		return get_theme_file_uri( 'assets/diagrams/photo-placeholder.svg' );
	}
	return $url;
}, 10, 2 );

function fiberlan_scene_url( $src ) {
	$base = trailingslashit( (string) wp_parse_url( get_template_directory_uri(), PHP_URL_PATH ) );
	$path = (string) wp_parse_url( $src, PHP_URL_PATH );
	if ( 0 !== strpos( $path, $base . 'assets/' ) ) {
		return $src;
	}
	$host = wp_parse_url( $src, PHP_URL_HOST );
	if ( $host && ! in_array( $host, array( wp_parse_url( get_template_directory_uri(), PHP_URL_HOST ), 'fiberlan.pl', 'www.fiberlan.pl' ), true ) ) {
		return $src;
	}
	$file = substr( $path, strlen( $base ) );
	return in_array( $file, fiberlan_sample_images(), true ) ? get_theme_file_uri( $file ) : $src;
}

function fiberlan_resolve_scene_images( $html ) {
	if ( false === strpos( $html, '/assets/' ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	while ( $tags->next_tag( 'img' ) ) {
		$src = (string) $tags->get_attribute( 'src' );
		$new = fiberlan_scene_url( $src );
		if ( $new !== $src ) {
			$tags->set_attribute( 'src', $new );
			if ( $new === get_theme_file_uri( 'assets/diagrams/photo-placeholder.svg' ) ) {
				$tags->set_attribute( 'alt', '' );
			}
			$tags->remove_attribute( 'srcset' );
			$tags->remove_attribute( 'sizes' );
		}
	}
	return $tags->get_updated_html();
}
add_filter( 'render_block_core/image', 'fiberlan_resolve_scene_images' );
add_filter( 'render_block_fiberlan/projects', 'fiberlan_resolve_scene_images' );

add_filter( 'rest_prepare_page', static function ( $response, $post, $request ) {
	if ( 'edit' === $request['context'] && isset( $response->data['content']['raw'] ) ) {
		$response->data['content']['raw'] = fiberlan_resolve_scene_images( $response->data['content']['raw'] );
	}
	return $response;
}, 10, 3 );
