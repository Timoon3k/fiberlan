<?php
/** Plain-text defaults shared by the canvas and the public render callbacks. */
defined( 'ABSPATH' ) || exit;

function fl_block_defaults( $name, $lang ) {
	$home = fl_text( $lang, 'Strona główna', 'Home' );
	switch ( $name ) {
		case 'contact':
			return fl_contact_labels( $lang ) + array( 'policyUrl' => fl_url( 'privacy', $lang ) );
		case 'projects':
			return array(
				'label' => fl_text( $lang, 'Kategorie realizacji', 'Project categories' ),
				'all'   => fl_text( $lang, 'Wszystkie', 'All projects' ),
				'empty' => fl_text( $lang, 'Brak realizacji w tej kategorii.', 'There are no projects in this category.' ),
			);
		case 'languages':
			return array(
				'label'      => fl_text( $lang, 'Język', 'Language' ),
				'suggestion' => fl_text( $lang, 'Wersja angielska', 'Switch to Polish' ),
				'plText'     => 'PL',
				'enText'     => 'EN',
			);
		case 'breadcrumbs':
			return array( 'label' => fl_text( $lang, 'Ścieżka nawigacji', 'Breadcrumbs' ), 'home' => $home );
		case 'service-links':
			return array( 'heading' => fl_text( $lang, 'Zakres usług', 'Explore our services' ) );
		case 'not-found':
			return array(
				'eyebrow' => '404',
				'heading' => fl_text( $lang, 'Nie znaleziono strony', 'Page not found' ),
				'body'    => fl_text( $lang, 'Sprawdź adres lub wróć na stronę główną FIBERLAN.', 'Check the address or return to the FIBERLAN homepage.' ),
				'button'  => $home,
				'url'     => fl_url( 'home', $lang ) ?: home_url( '/' ),
			);
		case 'copyright':
			return array(
				'company' => 'FIBERLAN SC',
				'rights'  => fl_text( $lang, 'Wszelkie prawa zastrzeżone.', 'All rights reserved.' ),
			);
		case 'consent':
			return array( 'label' => fl_text( $lang, 'Ustawienia cookies', 'Cookie settings' ) );
	}
	return array();
}

function fl_block_values( $name, $attrs, $lang ) {
	return array_merge( fl_block_defaults( $name, $lang ), array_filter( $attrs, 'is_string' ) );
}

function fl_consent_render( $attrs ) {
	$copy = fl_block_values( 'consent', $attrs, fl_request_language() );
	return '<button type="button" class="fl-consent-settings" hidden>' . esc_html( $copy['label'] ) . '</button>';
}

function fl_copyright_years() {
	$year = (int) wp_date( 'Y' );
	return $year > 2026 ? '2026–' . $year : '2026';
}

function fl_copyright_render( $attrs ) {
	$copy = fl_block_values( 'copyright', $attrs, fl_request_language() );
	return '<p class="fl-copyright">© ' . esc_html( fl_copyright_years() . ' ' . $copy['company'] . '. ' . $copy['rights'] ) . '</p>';
}

add_action( 'enqueue_block_editor_assets', static function () {
	$config = array();
	foreach ( array( 'pl', 'en' ) as $lang ) {
		foreach ( array( 'contact', 'projects', 'languages', 'breadcrumbs', 'service-links', 'not-found', 'consent', 'copyright' ) as $block ) {
			$config['copy'][ $lang ][ $block ] = fl_block_defaults( $block, $lang );
		}
	}
	$config['language'] = fl_page_lang( get_the_ID() );
	$config['copyrightYears'] = fl_copyright_years();
	$config['admin'] = admin_url();
	$config['projectParents'] = array( fl_page_id( 'projects', 'pl' ), fl_page_id( 'projects', 'en' ) );
	$config['newProjectNonce'] = wp_create_nonce( 'fl_create_project' );
	wp_add_inline_script( 'fiberlan-block-editor', 'window.fiberlanEditor = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
} );
