<?php
/**
 * Optional installation of the editable home page on a new site.
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Preserve any existing static front page. Reuse only records created here.
 *
 * @return int|WP_Error Static home page ID or installation error.
 */
function fiberlan_install_starter() {
	$current_id = (int) get_option( 'page_on_front' );
	$home_id    = (int) get_option( 'fiberlan_starter_home_id' );

	if ( 'page' === get_option( 'show_on_front' ) && $current_id && $current_id !== $home_id ) {
		return new WP_Error( 'existing_home', 'Witryna ma już ustawioną inną stronę główną. Instalator zachował jej treść i ustawienia.' );
	}

	$media = fiberlan_starter_media();
	if ( is_wp_error( $media ) ) {
		return $media;
	}

	$registry = WP_Block_Patterns_Registry::get_instance();
	$sections = array( 'home-hero', 'home-experience', 'home-services', 'home-projects', 'home-contact' );
	$content  = array();

	foreach ( $sections as $section ) {
		$pattern = $registry->get_registered( 'fiberlan/' . $section );
		if ( empty( $pattern['content'] ) ) {
			return new WP_Error( 'missing_pattern', 'Brakuje wzorca motywu: ' . $section );
		}
		$content[] = $pattern['content'];
	}

	$contact_id = (int) get_option( 'fiberlan_contact_block_id' );
	if ( 'wp_block' !== get_post_type( $contact_id ) || 'publish' !== get_post_status( $contact_id ) ) {
		$contact_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'wp_block',
					'post_status'  => 'publish',
					'post_title'   => 'FIBERLAN – dane kontaktowe',
					'post_content' => fiberlan_contact_blocks(),
				)
			),
			true
		);
		if ( is_wp_error( $contact_id ) ) {
			return $contact_id;
		}
		update_option( 'fiberlan_contact_block_id', $contact_id );
	}

	if ( ! $home_id || 'page' !== get_post_type( $home_id ) ) {
		$home_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Strona główna',
					'post_content' => implode( "\n\n", $content ),
				)
			),
			true
		);
		if ( is_wp_error( $home_id ) ) {
			return $home_id;
		}
		update_option( 'fiberlan_starter_home_id', $home_id );
	}
	if ( 'publish' !== get_post_status( $home_id ) ) {
		return new WP_Error( 'unpublished_home', 'Przywróć lub opublikuj wcześniej utworzoną stronę główną, aby ponownie jej użyć.' );
	}
	update_option( 'page_on_front', $home_id );
	update_option( 'show_on_front', 'page' );
	return $home_id;
}

add_action(
	'admin_menu',
	static function () {
		add_theme_page( 'FIBERLAN', 'FIBERLAN', 'manage_options', 'fiberlan-starter', 'fiberlan_starter_screen' );
	}
);

function fiberlan_starter_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>FIBERLAN — instalacja strony głównej</h1>
		<p>Na nowej witrynie instalator utworzy stronę z bloków, ustawi ją jako stronę główną i doda synchronizowany wzorzec danych kontaktowych. Możesz później edytować je w WordPressie.</p>
		<?php if ( isset( $_GET['installed'] ) ) : ?>
			<div class="notice notice-success"><p>Strona główna jest gotowa do edycji.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fiberlan_install_starter">
			<?php wp_nonce_field( 'fiberlan_install' ); ?>
			<?php submit_button( 'Przygotuj stronę główną' ); ?>
		</form>
	</div>
	<?php
}

add_action(
	'admin_post_fiberlan_install_starter',
	static function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Brak uprawnień.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'fiberlan_install' );
		$result = fiberlan_install_starter();
		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), 'FIBERLAN', array( 'back_link' => true ) );
		}
		wp_safe_redirect( admin_url( 'themes.php?page=fiberlan-starter&installed=1' ) );
		exit;
	}
);
