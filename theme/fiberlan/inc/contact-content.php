<?php
/**
 * Starter blocks for the shared company contact details.
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;

function fiberlan_contact_blocks() {
	return <<<'BLOCKS'
<!-- wp:group {"className":"fl-contact-items","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group fl-contact-items">
<!-- wp:paragraph {"className":"fl-address"} -->
<p class="fl-address">ul. Wojskowa 8A lok. 1, 03-599 Warszawa</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"fl-phone"} -->
<p class="fl-phone"><a href="tel:+48662211200">+48 662 211 200</a></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"fl-phone fl-secondary-phone"} -->
<p class="fl-phone fl-secondary-phone"><a href="tel:+48696610440">+48 696 610 440</a></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"fl-email"} -->
<p class="fl-email"><a href="mailto:info@fiberlan.pl">info@fiberlan.pl</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
BLOCKS;
}

function fiberlan_contact_reference() {
	$contact_id = absint( get_option( 'fiberlan_contact_block_id' ) );
	if ( $contact_id && 'wp_block' === get_post_type( $contact_id ) && 'publish' === get_post_status( $contact_id ) ) {
		return sprintf( '<!-- wp:block {"ref":%d} /-->', $contact_id );
	}
	return fiberlan_contact_blocks();
}
