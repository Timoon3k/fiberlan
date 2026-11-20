<?php
/**
 * Import the original client files into the Media Library for the starter site.
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;

function fiberlan_starter_media() {
	$files = array(
		'fiberlan-logo-original.jpg'                 => 'FIBERLAN SC',
		'fiber-splicer-cleaver-client-photo.jpg'     => 'Spawarka światłowodowa Fujikura FSM-62S z obcinarką i akcesoriami',
		'viavi-mpolx-client-photo.jpg'               => 'Dwa mierniki VIAVI SmartClass Fiber MPOLx z torbą transportową',
		'fluke-networks-versiv-2-client-photo.jpg'   => 'Zestaw pomiarowy Fluke Networks Versiv 2',
	);

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$ids = get_option( 'fiberlan_starter_media', array() );
	foreach ( $files as $filename => $description ) {
		if ( ! empty( $ids[ $filename ] ) && wp_attachment_is_image( $ids[ $filename ] ) ) {
			continue;
		}
		$tmp = wp_tempnam( $filename );
		if ( ! $tmp || ! copy( get_theme_file_path( 'assets/media/' . $filename ), $tmp ) ) {
			if ( $tmp ) {
				wp_delete_file( $tmp );
			}
			return new WP_Error( 'media_copy', 'Nie udało się skopiować pliku ' . $filename );
		}
		$id = media_handle_sideload( array( 'name' => $filename, 'tmp_name' => $tmp ), 0, $description );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			return $id;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $description );
		$ids[ $filename ] = $id;
		update_option( 'fiberlan_starter_media', $ids );
	}
	if ( ! get_theme_mod( 'custom_logo' ) && ! get_option( 'fiberlan_starter_logo_initialized' ) ) {
		update_option( 'site_logo', $ids['fiberlan-logo-original.jpg'] );
	}
	update_option( 'fiberlan_starter_logo_initialized', true );
	return $ids;
}

/** Return native editable image markup, with the original file as a pre-install fallback. */
function fiberlan_image_block( $filename, $alt, $class, $anchor = '', $caption = '' ) {
	$ids = get_option( 'fiberlan_starter_media', array() );
	$id  = (int) ( $ids[ $filename ] ?? 0 );
	$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : false;
	$attrs = array( 'sizeSlug' => 'full', 'linkDestination' => 'none', 'className' => $class );
	if ( $url ) {
		$attrs['id'] = $id;
	} else {
		$url = get_theme_file_uri( 'assets/media/' . $filename );
	}
	if ( $anchor ) {
		$attrs['anchor'] = $anchor;
	}
	return sprintf(
		'<!-- wp:image %s --><figure%s class="wp-block-image size-full %s"><img src="%s" alt="%s"%s/>%s</figure><!-- /wp:image -->',
		serialize_block_attributes( $attrs ),
		$anchor ? ' id="' . esc_attr( $anchor ) . '"' : '',
		esc_attr( $class ),
		esc_url( $url ),
		esc_attr( $alt ),
		isset( $attrs['id'] ) ? ' class="wp-image-' . $id . '"' : '',
		$caption ? '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption>' : ''
	);
}
