<?php
/** Send messages to the local test inbox. */
defined( 'ABSPATH' ) || exit;

if ( 'local' === wp_get_environment_type() ) {
	add_action( 'phpmailer_init', static function ( $mailer ) {
		$mailer->isSMTP();
		$mailer->Host = 'mailpit';
		$mailer->Port = 1025;
		$mailer->SMTPAuth = false;
		$mailer->SMTPAutoTLS = false;
	} );
}
