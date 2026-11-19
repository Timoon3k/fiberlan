<?php
defined( 'ABSPATH' ) || exit;

function fl_contact_fields() {
	return array(
		'name' => array( 'text', 120, 'name', true ),
		'company' => array( 'text', 150, 'organization', false ),
		'email' => array( 'email', 254, 'email', true ),
		'phone' => array( 'tel', 50, 'tel', false ),
		'subject' => array( 'text', 150, 'off', true ),
		'message' => array( 'textarea', 5000, 'off', true ),
	);
}

function fl_contact_labels( $lang ) {
	$labels = 'en' === $lang
		? array( 'Full name', 'Company (optional)', 'Email address', 'Phone (optional)', 'Subject', 'Message', 'I have read the information on how my data is processed.', 'Privacy policy', 'Send message', 'Thank you. Your message has been sent.', 'Please check the marked fields.', 'Sending is temporarily unavailable. Please try again or call us.', 'Too many attempts. Please try again in a few minutes.' )
		: array( 'Imię i nazwisko', 'Firma (opcjonalnie)', 'Adres e-mail', 'Telefon (opcjonalnie)', 'Temat', 'Wiadomość', 'Zapoznałem się z informacją o przetwarzaniu danych.', 'Polityka prywatności', 'Wyślij wiadomość', 'Dziękujemy. Twoja wiadomość została wysłana.', 'Sprawdź zaznaczone pola.', 'Wysyłka jest chwilowo niedostępna. Spróbuj ponownie lub zadzwoń.', 'Za dużo prób. Spróbuj ponownie za kilka minut.' );
	return array_combine( array( 'name', 'company', 'email', 'phone', 'subject', 'message', 'privacy', 'policy', 'submit', 'success', 'invalid', 'failure', 'limited' ), $labels );
}

function fl_post_string( $key ) {
	return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
}

function fl_contact_submit( $id ) {
	$result = array( 'status' => 'invalid', 'values' => array(), 'errors' => array() );
	if ( ! wp_verify_nonce( fl_post_string( 'fl_nonce' ), 'fl_contact_' . $id ) || '' !== fl_post_string( 'fl_website' ) ) {
		status_header( 400 );
		return $result;
	}
	$key = 'fl_contact_' . hash_hmac( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '', wp_salt( 'nonce' ) );
	$attempts = (int) get_transient( $key );
	if ( $attempts >= 5 ) {
		$result['status'] = 'limited';
		status_header( 429 );
		return $result;
	}
	set_transient( $key, $attempts + 1, 10 * MINUTE_IN_SECONDS );

	foreach ( fl_contact_fields() as $field => list( $type, $max, $autocomplete, $required ) ) {
		$raw = trim( fl_post_string( 'fl_' . $field ) );
		$value = 'textarea' === $type ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$result['values'][ $field ] = wp_html_excerpt( $value, $max );
		if ( ( $required && '' === $value ) || strlen( $raw ) > 4 * $max || mb_strlen( $value ) > $max || ( 'email' === $type && ( ! is_email( $raw ) || $value !== $raw ) ) ) {
			$result['errors'][ $field ] = true;
		}
	}
	if ( '1' !== fl_post_string( 'fl_privacy' ) ) {
		$result['errors']['privacy'] = true;
	}
	if ( $result['errors'] ) {
		status_header( 422 );
		return $result;
	}

	$v = $result['values'];
	$body = sprintf( "Name: %s\nCompany: %s\nEmail: %s\nPhone: %s\n\n%s", $v['name'], $v['company'], $v['email'], $v['phone'], $v['message'] );
	if ( ! wp_mail( 'info@fiberlan.pl', '[FIBERLAN] ' . $v['subject'], $body, array( 'Reply-To: ' . $v['email'] ) ) ) {
		$result['status'] = 'failure';
		status_header( 503 );
		return $result;
	}
	$receipt = wp_generate_password( 32, false );
	set_transient( 'fl_received_' . $receipt, $id, 5 * MINUTE_IN_SECONDS );
	$query = array( 'received' => $receipt ) + fl_campaign_args();
	wp_safe_redirect( add_query_arg( $query, get_permalink( $id ) ) . '#fl-form-status', 303 );
	exit;
}

add_action( 'template_redirect', static function () {
	$id = get_queried_object_id();
	if ( ! is_page() || ! has_block( 'fiberlan/contact', $id ) ) {
		return;
	}
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	nocache_headers();
	do_action( 'litespeed_control_set_nocache', 'FIBERLAN contact form' );
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && 'contact' === fl_post_string( 'fl_action' ) ) {
		$GLOBALS['fl_contact_result'] = fl_contact_submit( $id );
	}
} );

function fl_contact_render( $attrs ) {
	$id = get_queried_object_id();
	$lang = fl_page_lang( $id );
	$a = wp_parse_args( $attrs, fl_contact_labels( $lang ) );
	$result = $GLOBALS['fl_contact_result'] ?? array( 'values' => array(), 'errors' => array(), 'status' => '' );
	$receipt = $_GET['received'] ?? '';
	$success = is_string( $receipt ) && preg_match( '/^[a-z0-9]{32}$/iD', $receipt ) && $id && (int) get_transient( 'fl_received_' . $receipt ) === $id;
	if ( $success ) {
		$success = delete_transient( 'fl_received_' . $receipt );
		if ( $success ) {
			wp_enqueue_script( 'fiberlan-lead' );
		}
	}
	$status = $success ? 'success' : $result['status'];
	ob_start();
	?>
	<form class="fl-form" id="formularz" method="post">
		<?php if ( $status ) : ?>
			<p id="fl-form-status" tabindex="-1" class="fl-form-status <?php echo $success ? 'is-success' : 'is-error'; ?>" role="<?php echo $success ? 'status' : 'alert'; ?>"<?php if ( $success ) { printf( ' data-fiberlan-lead="%s"', esc_attr( $lang ) ); } ?>><?php echo esc_html( $a[ $status ] ); ?></p>
		<?php endif; ?>
		<input type="hidden" name="fl_action" value="contact">
		<?php wp_nonce_field( 'fl_contact_' . $id, 'fl_nonce', false ); ?>
		<div hidden><label>Website<input type="text" name="fl_website" tabindex="-1" autocomplete="off"></label></div>
		<div class="fl-form-grid">
			<?php foreach ( fl_contact_fields() as $field => list( $type, $max, $autocomplete, $required ) ) :
				$value = $result['values'][ $field ] ?? '';
				$invalid = isset( $result['errors'][ $field ] ) ? ' aria-invalid="true" aria-describedby="fl-form-status"' : '';
				?>
				<label class="fl-field fl-field-<?php echo esc_attr( $field ); ?>">
					<span><?php echo esc_html( $a[ $field ] . ( $required ? ' *' : '' ) ); ?></span>
					<?php if ( 'textarea' === $type ) : ?>
						<textarea name="fl_message" rows="6" maxlength="<?php echo (int) $max; ?>" required<?php echo $invalid; ?>><?php echo esc_textarea( $value ); ?></textarea>
					<?php else : ?>
						<input name="fl_<?php echo esc_attr( $field ); ?>" type="<?php echo esc_attr( $type ); ?>" maxlength="<?php echo (int) $max; ?>" autocomplete="<?php echo esc_attr( $autocomplete ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo $required ? ' required' : ''; echo $invalid; ?>>
					<?php endif; ?>
				</label>
			<?php endforeach; ?>
		</div>
		<label class="fl-form-privacy"><input type="checkbox" name="fl_privacy" value="1" required<?php echo isset( $result['errors']['privacy'] ) ? ' aria-invalid="true" aria-describedby="fl-form-status"' : ''; ?><?php checked( '1', fl_post_string( 'fl_privacy' ) ); ?>> <?php echo esc_html( $a['privacy'] ); ?></label>
		<p><a href="<?php echo esc_url( fl_url( 'privacy', $lang ) ); ?>"><?php echo esc_html( $a['policy'] ); ?></a></p>
		<button class="wp-element-button" type="submit"><?php echo esc_html( $a['submit'] ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}
