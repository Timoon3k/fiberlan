<?php
defined( 'ABSPATH' ) || exit;

function fl_campaign_args() {
	$query = array();
	foreach ( $_GET as $name => $value ) {
		if ( is_string( $value ) && preg_match( '/^(utm_[a-z0-9_]+|gclid)$/iD', $name ) ) {
			$query[ $name ] = wp_html_excerpt( sanitize_text_field( wp_unslash( $value ) ), 256 );
		}
	}
	return $query;
}

add_action( 'wp_enqueue_scripts', static function () {
	if ( is_page() || is_404() ) {
		wp_enqueue_script( 'fiberlan-site', plugins_url( 'assets/site.js', FL_SITE_DIR . '/fiberlan-site.php' ), array(), FL_SITE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
	wp_register_script( 'fiberlan-lead', plugins_url( 'assets/lead.js', FL_SITE_DIR . '/fiberlan-site.php' ), array(), FL_SITE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
} );

add_action( 'init', static function () {
	if ( ! function_exists( 'fiberlan_contact_reference' ) ) {
		return;
	}
	foreach ( array( 'pl', 'en' ) as $lang ) {
		$title = fl_text( $lang, 'Spawanie światłowodów dla firm', 'Fibre optic splicing for businesses' );
		$lead = fl_text( $lang, 'Spawamy włókna jednomodowe i wielomodowe, w tym kable wstęgowe Ribbon. Pomagamy dobrać zakres instalacji i pomiarów odbiorowych.', 'We splice single-mode, multimode and ribbon fibre cables. We can help you define the installation scope and acceptance testing requirements.' );
		$body = fl_hero( $lang, 'FIBERLAN SC', $title, $lead );
		$body .= fl_section(
			fl_heading( fl_text( $lang, 'Omów zakres prac', 'Discuss the scope of work' ) )
			. fl_paragraph( fl_text( $lang, 'Podaj lokalizację obiektu, liczbę włókien i przewidywany termin. Wrócimy z pytaniami lub propozycją kolejnych kroków.', 'Share the site location, fibre count and estimated schedule. We will follow up with any questions or proposed next steps.' ) )
			. fl_button( fl_text( $lang, 'Zapytaj o wycenę', 'Request a quote' ), '#formularz' )
		);
		$body .= fl_section(
			fl_group( 'fl-contact-sidebar', fiberlan_contact_reference() )
			. fl_group( 'fl-form-panel', fl_heading( fl_text( $lang, 'Zapytanie ofertowe', 'Project enquiry' ) ) . fl_custom_block( 'contact', fl_contact_labels( $lang ) ) ),
			'fl-contact-layout'
		);
		register_block_pattern( 'fiberlan/landing-' . $lang, array(
			'title'      => fl_text( $lang, 'Strona kampanii – spawanie światłowodów (PL)', 'Campaign page – fibre optic splicing (EN)' ),
			'categories' => array( 'fiberlan' ),
			'content'    => $body,
		) );
	}
}, 20 );
