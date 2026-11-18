<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', static function () {
	add_post_type_support( 'page', 'excerpt' );
	foreach ( array( 'fl_sector' => 'Kategorie realizacji', 'fl_industry' => 'Branże' ) as $key => $label ) {
		register_taxonomy( $key, 'page', array( 'label' => $label, 'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => false ) );
	}
} );

function fl_projects_render( $attrs ) {
	$parent = get_queried_object_id();
	$lang = fl_page_lang( $parent );
	$terms = get_terms( array( 'taxonomy' => 'fl_sector', 'hide_empty' => false ) );
	$filter = isset( $_GET['sector'] ) && is_string( $_GET['sector'] ) ? sanitize_title( wp_unslash( $_GET['sector'] ) ) : '';
	$url = get_permalink( $parent );
	$html = '<nav class="fl-filters" aria-label="' . esc_attr( $attrs['label'] ?? 'Kategorie' ) . '">';
	$html .= '<a href="' . esc_url( $url . '#portfolio' ) . '"' . ( '' === $filter ? ' aria-current="true"' : '' ) . '>' . esc_html( $attrs['all'] ?? 'Wszystkie' ) . '</a>';
	foreach ( $terms as $term ) {
		if ( 0 !== strpos( $term->slug, $lang . '-' ) ) {
			continue;
		}
		$html .= '<a href="' . esc_url( add_query_arg( 'sector', $term->slug, $url ) . '#portfolio' ) . '"' . ( $filter === $term->slug ? ' aria-current="true"' : '' ) . '>' . esc_html( $term->name ) . '</a>';
	}
	$args = array( 'post_type' => 'page', 'post_parent' => $parent, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ) );
	if ( $filter ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'fl_sector', 'field' => 'slug', 'terms' => $filter ) );
	}
	$projects = get_posts( $args );
	$html .= '</nav><div class="fl-case-grid">';
	foreach ( $projects as $project ) {
		$image = get_the_post_thumbnail( $project, 'medium_large' );
		if ( ! $image ) {
			foreach ( parse_blocks( $project->post_content ) as $block ) {
				if ( 'core/image' === $block['blockName'] ) {
					$image = render_block( $block );
					break;
				}
			}
		}
		$html .= '<article><a href="' . esc_url( get_permalink( $project ) ) . '">' . $image . '<h3>' . esc_html( get_the_title( $project ) ) . '</h3></a><p>' . esc_html( $project->post_excerpt ) . '</p></article>';
	}
	if ( ! $projects ) {
		$html .= '<p>' . esc_html( $attrs['empty'] ?? 'Brak realizacji w tej kategorii.' ) . '</p>';
	}
	return '<div id="portfolio">' . $html . '</div></div>';
}
