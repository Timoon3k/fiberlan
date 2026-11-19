<?php
/**
 * Title: Nagłówek FIBERLAN
 * Slug: fiberlan/header
 * Inserter: no
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"fl-header","layout":{"type":"default"}} -->
<div class="wp-block-group fl-header">
	<!-- wp:group {"className":"fl-contactbar","layout":{"type":"default"}} -->
	<div class="wp-block-group fl-contactbar">
		<!-- wp:group {"className":"fl-shell","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-shell">
			<?php echo fiberlan_contact_reference(); ?>
		</div>
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
		<!-- wp:navigation {"ariaLabel":"Menu główne","className":"fl-nav","overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"}} -->
			<!-- wp:home-link {"label":"Strona główna"} /-->
			<?php
			$destinations = array(
				array( 'about', 'o-nas', 'O nas', 'doswiadczenie' ),
				array( 'services', 'uslugi', 'Usługi', 'uslugi' ),
				array( 'equipment', 'sprzet', 'Sprzęt', 'sprzet' ),
				array( 'projects', 'realizacje', 'Realizacje', 'realizacje' ),
				array( 'contact', 'kontakt', 'Kontakt', 'kontakt' ),
			);
			foreach ( $destinations as list( $key, $slug, $label, $anchor ) ) {
				$id = function_exists( 'fl_page_id' ) ? fl_page_id( $key, 'pl' ) : 0;
				$page = $id ? get_post( $id ) : get_page_by_path( $slug );
				if ( $page && 'publish' === $page->post_status ) {
					$attributes = array(
						'label' => $label,
						'kind'  => 'post-type',
						'type'  => 'page',
						'id'    => $page->ID,
						'url'   => get_permalink( $page ),
					);
				} else {
					$attributes = array(
						'label' => $label,
						'kind'  => 'custom',
						'url'   => home_url( '/#' . $anchor ),
					);
				}
				echo '<!-- wp:navigation-link ' . serialize_block_attributes( $attributes ) . ' /-->' . "\n";
			}
			?>
		<!-- /wp:navigation -->
		<?php if ( function_exists( 'fl_custom_block' ) ) { echo fl_custom_block( 'languages', array( 'label' => 'Język' ) ); } ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
