<?php
/**
 * Explicit, repeatable media import for the photographic FIBERLAN site.
 * Original uploads and custom page images are not overwritten.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', static function () {
	foreach ( array( '_fl_alt_pl', '_fl_alt_en' ) as $key ) {
		register_post_meta( 'attachment', $key, array(
			'type' => 'string', 'single' => true, 'show_in_rest' => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback' => static function ( $allowed, $key, $id ) {
				return current_user_can( 'edit_post', $id );
			},
		) );
	}
} );

function fl_media_slots() {
	return array(
		'about:team', 'services:spawanie', 'services:instalacje', 'services:pomiary',
		'services:lan', 'services:data-center', 'services:serwis',
		'home:spawanie', 'home:instalacje', 'home:pomiary', 'home:lan', 'equipment:hero',
		'projects:gallery:0', 'projects:gallery:1', 'projects:gallery:2',
		'projects:gallery:3', 'projects:gallery:4', 'projects:gallery:5',
	);
}

add_action( 'admin_menu', static function () {
	add_menu_page( 'FIBERLAN — zarządzanie', 'FIBERLAN', 'edit_pages', 'fiberlan-manager', 'fl_media_admin', 'dashicons-admin-site-alt3', 30 );
} );

function fl_media_admin() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>FIBERLAN — zarządzanie witryną</h1><p>Wybierz stronę i język. Treść nadal zapisuje się w natywnych blokach Gutenberga.</p>';
	echo '<table class="widefat striped" style="max-width:850px"><thead><tr><th>Strona</th><th>Polski</th><th>English</th></tr></thead><tbody>';
	foreach ( array( 'home' => 'Strona główna', 'about' => 'O nas', 'services' => 'Usługi', 'equipment' => 'Sprzęt', 'projects' => 'Realizacje', 'contact' => 'Kontakt', 'privacy' => 'Prywatność', 'cookies' => 'Cookies' ) as $key => $name ) {
		echo '<tr><th>' . esc_html( $name ) . '</th>';
		foreach ( array( 'pl', 'en' ) as $lang ) {
			$id = fl_page_id( $key, $lang );
			echo '<td>';
			if ( $id && current_user_can( 'edit_post', $id ) ) {
				printf( '<a href="%s">Edytuj</a> · <a href="%s" target="_blank" rel="noopener">Podgląd</a>', esc_url( get_edit_post_link( $id, 'raw' ) ), esc_url( get_permalink( $id ) ) );
			} else {
				echo '—';
			}
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table><h2>Dane i multimedia</h2><p>';
	$contact_id = absint( get_option( 'fiberlan_contact_block_id' ) );
	if ( $contact_id && current_user_can( 'edit_post', $contact_id ) ) {
		printf( '<a class="button" href="%s">Dane kontaktowe — wspólny wzorzec</a> ', esc_url( get_edit_post_link( $contact_id, 'raw' ) ) );
	}
	printf( '<a class="button" href="%s">Biblioteka mediów</a> <a class="button" href="%s">Realizacje i podstrony</a></p>', esc_url( admin_url( 'upload.php' ) ), esc_url( admin_url( 'edit.php?post_type=page' ) ) );
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) {
		echo '</div>';
		return;
	}
	$report = get_transient( 'fl_media_report_' . get_current_user_id() );
	if ( is_array( $report ) ) {
		delete_transient( 'fl_media_report_' . get_current_user_id() );
		echo '<div class="notice notice-info"><p><strong>Wynik operacji:</strong></p><ul>';
		foreach ( $report as $message ) {
			echo '<li>' . esc_html( $message ) . '</li>';
		}
		echo '</ul></div>';
	}
	echo '<h2>Import fotografii FIBERLAN</h2><p>Wgraj przygotowaną paczkę ZIP z manifest.json. Import tworzy zdjęcia w bibliotece mediów, zapisuje opisy PL/EN i opcjonalnie uzupełnia wyłącznie puste miejsca. Nie nadpisuje własnych zdjęć ani tekstów. Przed publikacją sprawdź prawa do publikacji, wizerunki oraz oznaczenia infrastruktury.</p>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'fl_media_import' );
	echo '<input type="hidden" name="action" value="fl_media_import"><p><input required type="file" name="bundle" accept=".zip,application/zip"></p><p><label><input type="checkbox" name="apply_slots" value="1"> Uzupełnij placeholdery PL i EN po imporcie</label></p>';
	echo '<p>Limit PHP: ' . esc_html( size_format( wp_max_upload_size() ) ) . '. W razie potrzeby zwiększ upload_max_filesize i post_max_size w hostingu.</p>';
	submit_button( 'Importuj paczkę' );
	echo '</form><h3>Ponowne dopasowanie</h3><p>Jeżeli strona była otwarta w edytorze podczas importu, zamknij ją i użyj poniższego przycisku. Nie trzeba ponownie wgrywać ZIP.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="fl_media_apply">';
	wp_nonce_field( 'fl_media_apply' );
	submit_button( 'Uzupełnij pozostałe puste miejsca', 'secondary' );
	echo '</form></div>';
}

function fl_media_redirect( $report ) {
	set_transient( 'fl_media_report_' . get_current_user_id(), $report, 300 );
	wp_safe_redirect( admin_url( 'admin.php?page=fiberlan-manager' ), 303 );
	exit;
}

function fl_media_guard( $action ) {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) {
		wp_die( 'Brak uprawnień.', '', array( 'response' => 403 ) );
	}
	check_admin_referer( $action );
}

add_action( 'admin_post_fl_media_import', static function () {
	fl_media_guard( 'fl_media_import' );
	if ( ! class_exists( 'ZipArchive' ) ) {
		fl_media_redirect( array( 'Brak rozszerzenia PHP ZipArchive.' ) );
	}
	$file = $_FILES['bundle'] ?? array();
	if ( UPLOAD_ERR_OK !== (int) ( $file['error'] ?? -1 ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) || (int) $file['size'] > 40 * MB_IN_BYTES ) {
		fl_media_redirect( array( 'Nieprawidłowy plik ZIP lub przekroczony limit 40 MB.' ) );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $file['tmp_name'] ) ) {
		fl_media_redirect( array( 'Nie można otworzyć archiwum.' ) );
	}
	$stat = $zip->statName( 'manifest.json' );
	$manifest = $stat && $stat['size'] <= 100000 ? json_decode( $zip->getFromName( 'manifest.json' ), true ) : null;
	if ( ! is_array( $manifest ) || 1 !== ( $manifest['version'] ?? 0 ) || ! is_array( $manifest['assets'] ?? null ) || count( $manifest['assets'] ) > 150 ) {
		$zip->close();
		fl_media_redirect( array( 'Nieprawidłowy manifest paczki.' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$report = array();
	$slots = get_option( 'fl_media_assignments', array() );
	$count = 0;
	$bytes = 0;
	foreach ( $manifest['assets'] as $asset ) {
		$name = $asset['file'] ?? '';
		if ( ! is_string( $name ) || ! preg_match( '/^[a-z0-9][a-z0-9-]{1,100}\.webp$/D', $name ) ) {
			$report[] = 'Pominięto nieprawidłową nazwę.';
			continue;
		}
		$stat = $zip->statName( $name );
		if ( ! $stat || $stat['size'] > 5 * MB_IN_BYTES || $stat['size'] < 12 || $bytes + $stat['size'] > 80 * MB_IN_BYTES ) {
			$report[] = 'Pominięto: ' . $name;
			continue;
		}
		$bytes += $stat['size'];
		$binary = $zip->getFromName( $name );
		$size = $binary ? @getimagesizefromstring( $binary ) : false;
		if ( ! $size || 'image/webp' !== ( $size['mime'] ?? '' ) || $size[0] > 2500 || $size[1] > 2500 ) {
			$report[] = 'Nieprawidłowy obraz: ' . $name;
			continue;
		}
		$hash = hash( 'sha256', $binary );
		$matches = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_fl_media_hash', 'meta_value' => $hash ) );
		$id = $matches ? (int) $matches[0] : 0;
		if ( ! $id ) {
			$tmp = wp_tempnam( $name );
			if ( ! $tmp || false === file_put_contents( $tmp, $binary ) ) {
				$report[] = 'Nie zapisano: ' . $name;
				continue;
			}
			$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0, null, array( 'post_title' => sanitize_text_field( pathinfo( $name, PATHINFO_FILENAME ) ) ) );
			if ( file_exists( $tmp ) ) {
				unlink( $tmp );
			}
			if ( is_wp_error( $id ) ) {
				$report[] = $name . ': ' . $id->get_error_message();
				continue;
			}
			update_post_meta( $id, '_fl_media_hash', $hash );
		}
		$pl = sanitize_text_field( $asset['alt_pl'] ?? '' );
		$en = sanitize_text_field( $asset['alt_en'] ?? '' );
		if ( ! get_post_meta( $id, '_fl_alt_pl', true ) ) {
			update_post_meta( $id, '_fl_alt_pl', $pl );
		}
		if ( ! get_post_meta( $id, '_fl_alt_en', true ) ) {
			update_post_meta( $id, '_fl_alt_en', $en );
		}
		if ( ! get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $pl );
		}
		foreach ( (array) ( $asset['slots'] ?? array() ) as $slot ) {
			if ( in_array( $slot, fl_media_slots(), true ) ) {
				$slots[ $slot ] = array( 'id' => (int) $id, 'transparent' => ! empty( $asset['transparent'] ) );
			}
		}
		$count++;
		unset( $binary );
	}
	$zip->close();
	update_option( 'fl_media_assignments', $slots, false );
	array_unshift( $report, 'Zdjęcia gotowe w bibliotece (nowe i już istniejące): ' . $count );
	if ( ! empty( $_POST['apply_slots'] ) ) {
		$report = array_merge( $report, fl_media_apply_all( $slots ) );
	}
	fl_media_redirect( $report );
} );

add_action( 'admin_post_fl_media_apply', static function () {
	fl_media_guard( 'fl_media_apply' );
	fl_media_redirect( fl_media_apply_all( get_option( 'fl_media_assignments', array() ) ) );
} );

function fl_media_class( $block, $name ) {
	return in_array( $name, preg_split( '/\s+/', $block['attrs']['className'] ?? '' ), true );
}

function fl_media_is_placeholder( $block ) {
	return 'core/image' === ( $block['blockName'] ?? '' ) &&
		false !== strpos( $block['innerHTML'] ?? '', '/assets/diagrams/photo-placeholder.svg' );
}

/** Standard Gutenberg image markup, including page-language alt and attachment ID. */
function fl_media_image( $asset, $lang, $class ) {
	$id = absint( $asset['id'] ?? 0 );
	$url = wp_get_attachment_url( $id );
	if ( ! $url || ! wp_attachment_is_image( $id ) ) {
		return null;
	}
	$alt = get_post_meta( $id, '_fl_alt_' . $lang, true );
	$classes = array_diff( preg_split( '/\s+/', trim( $class ) ), array( '', 'fl-photo-placeholder' ) );
	if ( ! empty( $asset['transparent'] ) ) {
		$classes[] = 'fl-client-cutout';
	}
	$class = implode( ' ', array_unique( $classes ) );
	$attrs = array( 'id' => $id, 'sizeSlug' => 'full', 'linkDestination' => 'none', 'className' => $class );
	$markup = '<!-- wp:image ' . serialize_block_attributes( $attrs ) . ' -->'
		. '<figure class="wp-block-image size-full ' . esc_attr( $class ) . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . $id . '"/></figure><!-- /wp:image -->';
	return parse_blocks( $markup )[0];
}

function fl_media_first_heading( $block ) {
	if ( 'core/heading' === ( $block['blockName'] ?? '' ) ) {
		return html_entity_decode( wp_strip_all_tags( $block['innerHTML'] ?? '' ), ENT_QUOTES, 'UTF-8' );
	}
	foreach ( $block['innerBlocks'] ?? array() as $child ) {
		$text = fl_media_first_heading( $child );
		if ( '' !== $text ) {
			return $text;
		}
	}
	return '';
}

function fl_media_home_service( $block ) {
	$heading = fl_media_first_heading( $block );
	foreach ( array(
		'spawanie' => '/spawanie|splicing|zastosowan|applications/iu',
		'pomiary' => '/pomiary|testing|certyfikacja|certification/iu',
		'instalacje' => '/instalacje|installations/iu',
		'lan' => '/komputerowe|miedziane|copper|computer networks/iu',
	) as $name => $pattern ) {
		if ( preg_match( $pattern, $heading ) ) {
			return 'home:' . $name;
		}
	}
	return '';
}

function fl_media_walk( &$block, $page, $lang, $slots, &$counts, $context = '' ) {
	if ( 'services' === $page && in_array( $block['attrs']['anchor'] ?? '', array( 'spawanie', 'instalacje', 'pomiary', 'lan', 'data-center', 'serwis' ), true ) ) {
		$context = 'services:' . $block['attrs']['anchor'];
	}
	if ( 'home' === $page && fl_media_class( $block, 'fl-service-card' ) ) {
		$context = fl_media_home_service( $block );
	}
	if ( 'about' === $page && fl_media_class( $block, 'fl-team-photo' ) ) {
		$context = 'about:team';
	}
	if ( 'equipment' === $page && fl_media_class( $block, 'fl-equipment-hero' ) ) {
		$context = 'equipment:hero';
	}
	if ( 'projects' === $page && fl_media_class( $block, 'fl-project-gallery' ) ) {
		$i = 0;
		foreach ( $block['innerBlocks'] as &$child ) {
			if ( 'core/image' === ( $child['blockName'] ?? '' ) ) {
				fl_media_walk( $child, $page, $lang, $slots, $counts, 'projects:gallery:' . $i++ );
			}
		}
		unset( $child );
		return;
	}
	if ( fl_media_is_placeholder( $block ) && isset( $slots[ $context ] ) ) {
		$replacement = fl_media_image( $slots[ $context ], $lang, $block['attrs']['className'] ?? '' );
		if ( $replacement ) {
			$block = $replacement;
			$counts['placed']++;
			return;
		}
	}
	if ( ! empty( $block['innerBlocks'] ) ) {
		foreach ( $block['innerBlocks'] as &$child ) {
			fl_media_walk( $child, $page, $lang, $slots, $counts, $context );
		}
		unset( $child );
	}
	// The current 24/7 service is text-only. Add the image once, to its inner shell.
	if ( 'services:serwis' === $context && fl_media_class( $block, 'fl-repair-row' ) && isset( $slots[ $context ] ) ) {
		foreach ( $block['innerBlocks'] ?? array() as $child ) {
			if ( 'core/image' === ( $child['blockName'] ?? '' ) ) {
				return;
			}
		}
		$image = fl_media_image( $slots[ $context ], $lang, 'fl-section-image fl-repair-photo' );
		if ( $image && ! empty( $block['innerContent'] ) && function_exists( 'fl_layout_insert_last' ) ) {
			fl_layout_insert_last( $block, $image );
			$counts['placed']++;
		}
	}
}

function fl_media_apply_all( $slots ) {
	$report = array();
	if ( ! is_array( $slots ) || ! $slots ) {
		return array( 'Najpierw zaimportuj paczkę fotografii.' );
	}
	foreach ( array( 'pl', 'en' ) as $lang ) {
		foreach ( array( 'home', 'about', 'services', 'equipment', 'projects' ) as $key ) {
			$id = fl_page_id( $key, $lang );
			$post = $id ? get_post( $id ) : null;
			if ( ! $post || ! current_user_can( 'edit_post', $id ) ) {
				$report[] = strtoupper( $lang ) . ' / ' . $key . ': brak dostępnej strony.';
				continue;
			}
			if ( wp_check_post_lock( $id ) ) {
				$report[] = strtoupper( $lang ) . ' / ' . $key . ': edytowana, pominięto.';
				continue;
			}
			$blocks = parse_blocks( $post->post_content );
			$counts = array( 'placed' => 0 );
			foreach ( $blocks as &$block ) {
				fl_media_walk( $block, $key, $lang, $slots, $counts );
			}
			unset( $block );
			if ( ! $counts['placed'] ) {
				$report[] = strtoupper( $lang ) . ' / ' . $key . ': brak pasujących pustych miejsc; własne zdjęcia pozostają.';
				continue;
			}
			if ( get_post_field( 'post_content', $id ) !== $post->post_content ) {
				$report[] = strtoupper( $lang ) . ' / ' . $key . ': treść zmieniła się podczas operacji, pominięto.';
				continue;
			}
			$saved = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => serialize_blocks( $blocks ) ) ), true );
			$report[] = strtoupper( $lang ) . ' / ' . $key . ': ' . ( is_wp_error( $saved ) ? $saved->get_error_message() : 'uzupełniono ' . $counts['placed'] . ' zdjęć.' );
		}
	}
	return $report;
}
