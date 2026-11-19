<?php
/**
 * Title: FIBERLAN header (EN)
 * Slug: fiberlan/header-en
 * Inserter: no
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"fl-header","layout":{"type":"default"}} -->
<div class="wp-block-group fl-header">
<!-- wp:group {"className":"fl-contactbar","layout":{"type":"default"}} -->
<div class="wp-block-group fl-contactbar">
<!-- wp:group {"className":"fl-shell","layout":{"type":"default"}} -->
<div class="wp-block-group fl-shell"><?php echo fiberlan_contact_reference(); ?></div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"fl-nav-row fl-shell","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group fl-nav-row fl-shell">
<!-- wp:group {"className":"fl-brand","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group fl-brand">
<!-- wp:site-logo {"width":166} /-->
<!-- wp:site-title {"level":0} /-->
</div>
<!-- /wp:group -->
<!-- wp:navigation {"ariaLabel":"Main navigation","className":"fl-nav","overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"}} -->
<?php if ( function_exists( 'fl_page_id' ) ) :
	foreach ( array( 'home' => 'Home', 'about' => 'About', 'services' => 'Services', 'equipment' => 'Equipment', 'projects' => 'Projects', 'contact' => 'Contact' ) as $key => $label ) {
		$id = fl_page_id( $key, 'en' );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			echo '<!-- wp:navigation-link ' . serialize_block_attributes( array( 'label' => $label, 'kind' => 'post-type', 'type' => 'page', 'id' => $id, 'url' => get_permalink( $id ) ) ) . " /-->\n";
		}
	}
endif; ?>
<!-- /wp:navigation -->
<?php if ( function_exists( 'fl_custom_block' ) ) { echo fl_custom_block( 'languages', array( 'label' => 'Language' ) ); } ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
