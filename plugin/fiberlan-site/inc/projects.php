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
	$attrs = fl_block_values( 'projects', $attrs, $lang );
	$terms = get_terms( array( 'taxonomy' => 'fl_sector', 'hide_empty' => false ) );
	$filter = isset( $_GET['sector'] ) && is_string( $_GET['sector'] ) ? sanitize_title( wp_unslash( $_GET['sector'] ) ) : '';
	$url = get_permalink( $parent );
	$html = '<nav class="fl-filters" aria-label="' . esc_attr( $attrs['label'] ) . '">';
	$html .= '<a href="' . esc_url( $url . '#portfolio' ) . '"' . ( '' === $filter ? ' aria-current="true"' : '' ) . '>' . esc_html( $attrs['all'] ) . '</a>';
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
			$image = fl_project_image( parse_blocks( $project->post_content ) );
		}
		$html .= '<article><a href="' . esc_url( get_permalink( $project ) ) . '">' . $image . '<h3>' . esc_html( get_the_title( $project ) ) . '</h3></a><p>' . esc_html( $project->post_excerpt ) . '</p></article>';
	}
	if ( ! $projects ) {
		$html .= '<p>' . esc_html( $attrs['empty'] ) . '</p>';
	}
	return '<div id="portfolio">' . $html . '</div></div>';
}

// Read the first real image even when the editor moves it into a Group or Gallery.
function fl_project_image( $blocks ) {
	foreach ( $blocks as $block ) {
		if ( 'core/image' === $block['blockName'] ) {
			$tag = new WP_HTML_Tag_Processor( $block['innerHTML'] );
			if ( $tag->next_tag( 'img' ) && $tag->get_attribute( 'src' ) ) {
				$alt = (string) $tag->get_attribute( 'alt' );
				$id = absint( $block['attrs']['id'] ?? 0 );
				$image = $id ? wp_get_attachment_image( $id, 'medium_large', false, array( 'alt' => $alt ) ) : '';
				return $image ?: sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( $tag->get_attribute( 'src' ) ), esc_attr( $alt ) );
			}
		}
		if ( $block['innerBlocks'] ) {
			$image = fl_project_image( $block['innerBlocks'] );
			if ( $image ) {
				return $image;
			}
		}
	}
	return '';
}

add_action( 'admin_post_fl_create_project', static function () {
	check_admin_referer( 'fl_create_project' );
	$parent = absint( $_POST['parent'] ?? 0 );
	if ( ! $parent || ! in_array( $parent, array( fl_page_id( 'projects', 'pl' ), fl_page_id( 'projects', 'en' ) ), true )
		|| ! current_user_can( get_post_type_object( 'page' )->cap->create_posts ) || ! current_user_can( 'edit_post', $parent ) ) {
		wp_die( 'Brak uprawnień do dodania realizacji w tym katalogu.', '', array( 'response' => 403 ) );
	}
	$lang = fl_page_lang( $parent );
	$templates = wp_get_theme()->get_page_templates( null, 'page' );
	$template = 'en' === $lang ? 'page-en' : 'page-fiberlan';
	$report = 'project-' . $lang;
	// Dedicated project templates and other themes supply the page title themselves.
	$inline_title = ! isset( $templates[ $report ] ) && isset( $templates[ $template ] );
	$body = ( $inline_title ? '<!-- wp:post-title {"level":1} /-->' : '' )
		. '<!-- wp:image --><figure class="wp-block-image"><img alt=""/></figure><!-- /wp:image -->'
		. '<!-- wp:paragraph ' . serialize_block_attributes( array( 'placeholder' => fl_text( $lang, 'Opisz zakres prac…', 'Describe the scope of work…' ) ) ) . ' --><p></p><!-- /wp:paragraph -->';
	$id = wp_insert_post( wp_slash( array(
		'post_type' => 'page', 'post_status' => 'draft', 'post_parent' => $parent,
		'post_content' => $inline_title ? fl_section( $body ) : $body,
		'page_template' => isset( $templates[ $report ] ) ? $report : ( $inline_title ? $template : 'default' ),
	) ), true );
	if ( is_wp_error( $id ) ) {
		wp_die( esc_html( $id->get_error_message() ), '', array( 'back_link' => true ) );
	}
	wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ), 303 );
	exit;
} );
