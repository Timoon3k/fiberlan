<?php
/** Shared structure for both editable footer patterns. */
defined( 'ABSPATH' ) || exit;

function fiberlan_footer_list( $links ) {
	$list = '';
	foreach ( $links as $label => $url ) {
		if ( $url ) {
			$list .= '<!-- wp:list-item --><li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li><!-- /wp:list-item -->';
		}
	}
	return $list ? '<!-- wp:list --><ul class="wp-block-list">' . $list . '</ul><!-- /wp:list -->' : '';
}

function fiberlan_footer_legal( $lang ) {
	if ( ! function_exists( 'fl_url' ) ) {
		return;
	}
	$english = 'en' === $lang;
	$links = array(
		$english ? 'Privacy policy' : 'Polityka prywatności' => fl_url( 'privacy', $lang ),
		$english ? 'Cookie policy' : 'Polityka cookies' => fl_url( 'cookies', $lang ),
	);
	?>
<!-- wp:group {"className":"fl-footer-links fl-footer-legal","layout":{"type":"default"}} -->
<div class="wp-block-group fl-footer-links fl-footer-legal">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html( $english ? 'Legal' : 'Regulacje prawne' ); ?></h2>
	<!-- /wp:heading -->
	<?php echo fiberlan_footer_list( $links ); ?>
	<?php echo fl_custom_block( 'consent', fl_block_defaults( 'consent', $lang ) ); ?>
</div>
<!-- /wp:group -->
	<?php
}

function fiberlan_footer_pattern( $lang ) {
	$english = 'en' === $lang;
	$labels = $english
		? array( 'Home', 'About', 'Services', 'Equipment', 'Projects', 'Contact' )
		: array( 'Strona główna', 'O nas', 'Usługi', 'Sprzęt', 'Realizacje', 'Kontakt' );
	$links = array();
	$services = array();
	if ( function_exists( 'fl_url' ) ) {
		foreach ( array( 'home', 'about', 'services', 'equipment', 'projects', 'contact' ) as $index => $key ) {
			$id = fl_page_id( $key, $lang );
			$links[ $labels[ $index ] ] = $id && 'publish' === get_post_status( $id ) ? fl_url( $key, $lang ) : '';
		}
		$service_id = fl_page_id( 'services', $lang );
		if ( ! empty( $links[ $labels[2] ] ) ) {
			foreach ( fl_service_sections( $service_id ) as $service ) {
				if ( in_array( wp_parse_url( $service['url'], PHP_URL_FRAGMENT ), array( 'spawanie', 'instalacje', 'pomiary' ), true ) ) {
					$services[ html_entity_decode( $service['name'], ENT_QUOTES, 'UTF-8' ) ] = $service['url'];
				}
			}
		}
	}
	?>
<!-- wp:group {"anchor":"kontakt","className":"fl-footer","layout":{"type":"default"}} -->
<div id="kontakt" class="wp-block-group fl-footer">
	<!-- wp:group {"className":"fl-footer-main fl-footer-grid fl-shell","layout":{"type":"default"}} -->
	<div class="wp-block-group fl-footer-main fl-footer-grid fl-shell">
		<!-- wp:group {"className":"fl-footer-brand","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-footer-brand">
			<!-- wp:site-logo {"width":166} /-->
			<!-- wp:site-title {"level":0,"className":"fl-footer-title"} /-->
			<!-- wp:paragraph -->
			<p><?php echo esc_html( $english ? 'Fibre optic splicing, installation and testing in Poland and abroad.' : 'Spawanie, instalacje i pomiary światłowodów w Polsce i za granicą.' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<?php if ( array_filter( $links ) ) : ?>
		<!-- wp:group {"className":"fl-footer-links","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-footer-links">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html( $english ? 'Explore FIBERLAN' : 'Na skróty' ); ?></h2>
			<!-- /wp:heading -->
			<?php echo fiberlan_footer_list( $links ); ?>
		</div>
		<!-- /wp:group -->
		<?php endif; ?>
		<?php if ( $services ) : ?>
		<!-- wp:group {"className":"fl-footer-links","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-footer-links">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html( $english ? 'Services' : 'Usługi' ); ?></h2>
			<!-- /wp:heading -->
			<?php echo fiberlan_footer_list( $services ); ?>
		</div>
		<!-- /wp:group -->
		<?php endif; ?>
		<!-- wp:group {"className":"fl-footer-contact","layout":{"type":"default"}} -->
		<div class="wp-block-group fl-footer-contact">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html( $labels[5] ); ?></h2>
			<!-- /wp:heading -->
			<?php echo fiberlan_contact_reference(); ?>
		</div>
		<!-- /wp:group -->
		<?php fiberlan_footer_legal( $lang ); ?>
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"className":"fl-footer-credit fl-shell","layout":{"type":"default"}} -->
	<div class="wp-block-group fl-footer-credit fl-shell">
		<?php if ( function_exists( 'fl_copyright_render' ) ) : ?>
			<?php echo fl_custom_block( 'copyright', fl_block_defaults( 'copyright', $lang ) ); ?>
		<?php endif; ?>
		<!-- wp:paragraph -->
		<p><?php echo esc_html( $english ? 'Website design & development: Tomasz Majewski — ' : 'Projekt i realizacja: Tomasz Majewski — ' ); ?><a href="https://majewskitomasz.pl">majewskitomasz.pl</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
	<?php
}
