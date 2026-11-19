<?php
/**
 * Title: Stopka FIBERLAN
 * Slug: fiberlan/footer
 * Inserter: no
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"anchor":"kontakt","className":"fl-footer","layout":{"type":"default"}} -->
<div id="kontakt" class="wp-block-group fl-footer">
	<!-- wp:group {"className":"fl-footer-main fl-shell","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
	<div class="wp-block-group fl-footer-main fl-shell">
		<!-- wp:group {"className":"fl-footer-brand","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-footer-brand">
			<!-- wp:site-logo {"width":144} /-->
			<!-- wp:site-title {"level":0,"className":"fl-footer-title"} /-->
			<!-- wp:paragraph -->
			<p>Instalacje i pomiary sieci światłowodowych.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"fl-footer-contact","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-footer-contact">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Kontakt</h2>
			<!-- /wp:heading -->
			<?php echo fiberlan_contact_reference(); ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"className":"fl-footer-base fl-shell","layout":{"type":"default"}} -->
	<div class="wp-block-group fl-footer-base fl-shell">
		<?php echo fiberlan_policy_links( 'pl' ); ?>
		<!-- wp:paragraph {"className":"fl-back-top"} -->
		<p class="fl-back-top"><a href="#fl-page-top">Wróć na górę</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
