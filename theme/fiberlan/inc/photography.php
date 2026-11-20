<?php
/** Photographic artwork for the original, editable starter image blocks. */
defined( 'ABSPATH' ) || exit;

function fiberlan_scene_files() {
	return array(
		'service-splice'     => 'optical-fibres',
		'service-lan'        => 'patch-panel',
		'project-datacenter' => 'server-racks',
		'project-factory'    => 'production-line',
		'project-ship'       => 'shipyard',
	);
}

// New patterns use the photographs directly. Respect artwork supplied by a child theme.
add_filter( 'theme_file_uri', static function ( $url, $file ) {
	if ( 0 !== strpos( $file, 'assets/diagrams/' ) || $url !== get_template_directory_uri() . '/' . $file ) {
		return $url;
	}
	$files = fiberlan_scene_files();
	$key = basename( $file, '.svg' );
	return isset( $files[ $key ] ) && 'assets/diagrams/' . $key . '.svg' === $file
		? get_theme_file_uri( 'assets/scenes/' . $files[ $key ] . '.webp' ) : $url;
}, 10, 2 );

// Previously saved pages contain absolute URLs. Resolve only the five bundled images,
// keeping author-selected uploads, captions, alternative text and links intact.
function fiberlan_resolve_scene_images( $html ) {
	if ( false === strpos( $html, '/assets/diagrams/' ) ) {
		return $html;
	}
	$base = get_template_directory_uri() . '/assets/diagrams/';
	$files = fiberlan_scene_files();
	$tags = new WP_HTML_Tag_Processor( $html );
	while ( $tags->next_tag( 'img' ) ) {
		$src = (string) $tags->get_attribute( 'src' );
		$key = basename( $src, '.svg' );
		if ( isset( $files[ $key ] ) && $src === $base . $key . '.svg' ) {
			$tags->set_attribute( 'src', get_theme_file_uri( 'assets/diagrams/' . $key . '.svg' ) );
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

// Stock photographs illustrate the sectors; customer-uploaded project photos need no note.
function fiberlan_project_photo_note( $html ) {
	foreach ( array( 'server-racks', 'production-line', 'shipyard' ) as $scene ) {
		if ( false !== strpos( $html, '/assets/scenes/' . $scene . '.webp' ) ) {
			$english = function_exists( 'fl_request_language' ) && 'en' === fl_request_language();
			return $html . '<p class="fl-photo-note">' . ( $english ? 'Illustrative industry photographs.' : 'Fotografie branżowe mają charakter ilustracyjny.' ) . '</p>';
		}
	}
	return $html;
}
add_filter( 'render_block_fiberlan/projects', 'fiberlan_project_photo_note', 15 );
add_filter( 'render_block_core/group', static function ( $html, $block ) {
	return in_array( 'fl-project-grid', explode( ' ', $block['attrs']['className'] ?? '' ), true )
		? fiberlan_project_photo_note( $html ) : $html;
}, 15, 2 );
add_filter( 'render_block_core/image', static function ( $html, $block ) {
	return in_array( 'fl-case-illustration', explode( ' ', $block['attrs']['className'] ?? '' ), true )
		? fiberlan_project_photo_note( $html ) : $html;
}, 15, 2 );
